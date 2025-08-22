<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReadingRequest;
use App\Models\MeterReading;
use App\Models\Room;
use DB;
use Illuminate\Http\Request;
use App\Services\MeterReadingService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MeterReadingsExport;

class MeterReadingController extends Controller
{
    protected $meterReadingService;

    public function __construct(MeterReadingService $meterReadingService)
    {
        $this->meterReadingService = $meterReadingService;
    }

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $meterReadings = $this->meterReadingService->getAllMeterReadings($search, $perPage);
        $isFiltering = $request->hasAny(['search', 'room_id', 'month', 'year', 'sortOption']);

        // ✅ Sử dụng service để lấy thông tin thời gian thay vì tính toán thủ công
        $periodInfo = $this->meterReadingService->getDisplayPeriodInfo();
        $isInMeterReadingPeriod = $periodInfo['is_in_special_period'];

        // ✅ Logic đồng bộ với service
        if ($isInMeterReadingPeriod) {
            // Trong thời gian nhập chỉ số (28 -> 10): hiển thị phòng cần nhập chỉ số
            $rooms = $this->meterReadingService->getRoomsWithMotel();
            $shouldDisplayTable = true;
            $displayMode = 'time_based';

            Log::info('In meter reading period', [
                'current_day' => $periodInfo['current_day'],
                'display_month' => $periodInfo['display_month'],
                'display_year' => $periodInfo['display_year'],
                'period_description' => $periodInfo['period_description']
            ]);
        } else {
            // Ngoài thời gian: chỉ hiển thị phòng gần hết hạn hợp đồng (3 ngày)
            $roomsWithActiveContracts = $this->meterReadingService->getRoomsWithActiveContracts();

            if ($roomsWithActiveContracts && $roomsWithActiveContracts->isNotEmpty()) {
                $rooms = $roomsWithActiveContracts;
                $shouldDisplayTable = true;
                $displayMode = 'active_contracts';

                Log::info('Outside meter reading period, showing expiring contracts', [
                    'current_day' => $periodInfo['current_day'],
                    'period_description' => $periodInfo['period_description'],
                    'rooms_count' => $roomsWithActiveContracts->flatten()->count()
                ]);
            } else {
                $rooms = collect();
                $shouldDisplayTable = false;
                $displayMode = 'none';

                Log::info('Outside meter reading period, no expiring contracts', [
                    'current_day' => $periodInfo['current_day'],
                    'period_description' => $periodInfo['period_description']
                ]);
            }
        }

        // ✅ Lấy chỉ số điện nước tháng trước cho tất cả phòng
        if ($rooms && $rooms->isNotEmpty()) {
            $displayMonth = $periodInfo['display_month'];
            $displayYear = $periodInfo['display_year'];

            // Transform rooms collection để thêm thông tin chỉ số tháng trước
            $rooms = $rooms->map(function ($groupedRooms, $motelId) use ($displayMonth, $displayYear) {
                return $groupedRooms->map(function ($room) use ($displayMonth, $displayYear) {
                    // Lấy chỉ số điện nước tháng trước cho từng phòng
                    $previousReading = MeterReading::where('room_id', $room->id)
                        ->where(function ($query) use ($displayMonth, $displayYear) {
                            $query->where('year', '<', $displayYear)
                                ->orWhere(function ($q) use ($displayMonth, $displayYear) {
                                    $q->where('year', $displayYear)
                                        ->where('month', '<', $displayMonth);
                                });
                        })
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->first();

                    // Thêm thông tin chỉ số tháng trước vào room object
                    $room->previous_electricity = $previousReading ? $previousReading->electricity_kwh : 0;
                    $room->previous_water = $previousReading ? $previousReading->water_m3 : 0;
                    $room->previous_month = $previousReading ? $previousReading->month : null;
                    $room->previous_year = $previousReading ? $previousReading->year : null;
                    $room->has_previous_reading = $previousReading !== null;
                    // dd($room); // Debugging line to check room data
                    return $room;
                });
            });

            Log::info('Added previous readings to rooms', [
                'total_rooms_processed' => $rooms->flatten()->count(),
                'display_month' => $displayMonth,
                'display_year' => $displayYear
            ]);
        }

        $data = [
            'rooms' => $rooms,
            'meterReadings' => $meterReadings,
            'isFiltering' => $isFiltering,
            'shouldDisplayTable' => $shouldDisplayTable,
            'displayMode' => $displayMode,
            'search' => $search,
            'perPage' => $perPage,
            // ✅ Thêm thông tin period để view sử dụng
            'periodInfo' => $periodInfo,
            'isInMeterReadingPeriod' => $isInMeterReadingPeriod,
            'currentDay' => $periodInfo['current_day'],
            'periodDescription' => $periodInfo['period_description'],
            'readablePeriod' => $periodInfo['readable_period']
        ];

        if ($request->ajax()) {
            return view('meter_readings.index', $data);
        }

        return view('meter_readings.index', $data);
    }

    public function store(MeterReadingRequest $request)
    {
        try {
            DB::beginTransaction();
            Log::info('Creating multiple meter readings', $request->all());

            $readings = $request->input('readings');
            $month = $request->input('month');
            $year = $request->input('year');
            $createdInvoices = [];

            // ✅ Kiểm tra xem có thể tạo meter reading không
            foreach ($readings as $reading) {
                $canCreate = $this->meterReadingService->canCreateMeterReadingForRoom(
                    $reading['room_id'],
                    $month,
                    $year
                );

                if (!$canCreate) {
                    throw new \Exception("Không thể tạo chỉ số cho phòng {$reading['room_id']} trong tháng {$month}/{$year}. Có thể đã tồn tại hoặc phòng không có hợp đồng hoạt động.");
                }
            }

            foreach ($readings as $reading) {
                $meterReading = $this->meterReadingService->createMeterReading([
                    'room_id' => $reading['room_id'],
                    'month' => $month,
                    'year' => $year,
                    'electricity_kwh' => $reading['electricity_kwh'],
                    'water_m3' => $reading['water_m3'],
                ]);

                Log::info('Meter reading created', ['id' => $meterReading->id]);

                $invoice = $this->meterReadingService->createInvoice($meterReading->id);
                $createdInvoices[] = $invoice->code;
                Log::info('Invoice created', [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->code,
                ]);
            }

            DB::commit();
            session()->forget('motel_data'); // Xóa session sau khi thành công

            $message = 'Đã cập nhật chỉ số điện nước cho ' . count($readings) . ' phòng.';
            if (count($createdInvoices)) {
                $message .= ' Đã tạo ' . count($createdInvoices) . ' hóa đơn: ' . implode(', ', $createdInvoices) . '.';
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('meter_readings.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating meter readings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Gửi dữ liệu về để dùng lại trong modal
            $motelData = [
                'motel_name' => $request->input('motel_name', 'Unknown'),
                'month' => $month,
                'year' => $year,
                'rooms' => collect($readings)->map(function ($reading) {
                    return [
                        'id' => $reading['room_id'],
                        'name' => Room::find($reading['room_id'])->name ?? 'Unknown',
                    ];
                })->toArray(),
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'motel_data' => $motelData,
                ], 500);
            }

            session()->flash('motel_data', $motelData);

            return redirect()->route('meter_readings.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())
                ->withInput()
                ->with('open_update_modal', true);
        }
    }



    public function filter(Request $request)
    {
        try {
            $query = MeterReading::query()->with(['room.motel']);

            // ✅ Apply improved search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('room', function ($roomQuery) use ($searchTerm) {
                        $roomQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                        ->orWhereHas('room.motel', function ($motelQuery) use ($searchTerm) {
                            $motelQuery->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhere('id', 'like', '%' . $searchTerm . '%');
                });
            }

            // Apply other filters
            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }

            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Apply sorting
            if ($request->filled('sortOption')) {
                switch ($request->sortOption) {
                    case 'room_asc':
                        $query->join('rooms', 'rooms.id', '=', 'meter_readings.room_id')
                            ->orderBy('rooms.name', 'asc')
                            ->select('meter_readings.*');
                        break;
                    case 'room_desc':
                        $query->join('rooms', 'rooms.id', '=', 'meter_readings.room_id')
                            ->orderBy('rooms.name', 'desc')
                            ->select('meter_readings.*');
                        break;
                    case 'month_desc':
                        $query->orderBy('year', 'desc')->orderBy('month', 'desc');
                        break;
                    case 'month_asc':
                        $query->orderBy('year', 'asc')->orderBy('month', 'asc');
                        break;
                    case 'created_at_desc':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'created_at_asc':
                        $query->orderBy('created_at', 'asc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $meterReadings = $query->paginate(15);
            $meterReadings->appends($request->all());

            // Calculate statistics for the filtered data
            $statistics = $this->calculateStatistics($request);

            if ($request->ajax()) {
                // ✅ Create search summary for AJAX response
                $searchSummary = null;
                if ($request->filled('search')) {
                    $searchSummary = [
                        'term' => $request->search,
                        'total_found' => $meterReadings->total(),
                        'page_showing' => $meterReadings->count()
                    ];
                }

                // ✅ Return only the table content for AJAX requests
                $html = view('meter_readings.partials.history-table', compact('meterReadings', 'searchSummary'))->render();

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'statistics' => $statistics,
                    'total_count' => $meterReadings->total(),
                    'search_summary' => $searchSummary
                ]);
            }

            // For non-AJAX requests, redirect to history page with filters
            return redirect()->route('meter_readings.history', $request->all());

        } catch (\Exception $e) {
            Log::error('Error filtering meter readings', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tìm kiếm.');
        }
    }

    public function history(Request $request)
    {
        try {
            // Get all rooms for filter dropdown
            $rooms = Room::with('motel')->orderBy('name')->get();

            $query = MeterReading::query()->with(['room.motel']);

            // ✅ Improved search functionality - search by room name and motel name
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('room', function ($roomQuery) use ($searchTerm) {
                        $roomQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                        ->orWhereHas('room.motel', function ($motelQuery) use ($searchTerm) {
                            $motelQuery->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhere('id', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }

            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Default sorting
            $query->orderBy('created_at', 'desc');

            $meterReadings = $query->paginate(15);
            $meterReadings->appends($request->all());

            // Calculate statistics
            $statistics = $this->calculateStatistics($request);

            // ✅ Add search summary
            $searchSummary = null;
            if ($request->filled('search')) {
                $searchSummary = [
                    'term' => $request->search,
                    'total_found' => $meterReadings->total(),
                    'page_showing' => $meterReadings->count()
                ];
            }

            return view('meter_readings.history', array_merge(
                compact('meterReadings', 'rooms', 'searchSummary'),
                $statistics
            ));

        } catch (\Exception $e) {
            Log::error('Error loading meter readings history', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * ✅ Fixed export method
     */
    public function export(Request $request)
    {
        try {
            $query = MeterReading::with(['room.motel'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('room_id')) {
                $query->where('room_id', $request->room_id);
            }

            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $meterReadings = $query->get();

            if ($meterReadings->isEmpty()) {
                return back()->with('warning', 'Không có dữ liệu để xuất Excel.');
            }

            $filename = 'meter_readings_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

            // Use string format instead of class constants
            return Excel::download(new MeterReadingsExport($meterReadings), $filename, 'Xlsx');

        } catch (\Exception $e) {
            \Log::error('Error exporting meter readings: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file Excel.');
        }
    }

    public function getRoomReading(Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|min:2020'
            ]);

            $reading = MeterReading::where('room_id', $request->room_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            if ($reading) {
                return response()->json([
                    'exists' => true,
                    'data' => [
                        'electricity_kwh' => $reading->electricity_kwh,
                        'water_m3' => $reading->water_m3,
                        'id' => $reading->id,
                        'created_at' => $reading->created_at->format('d/m/Y H:i')
                    ]
                ]);
            }

            return response()->json(['exists' => false]);

        } catch (\Exception $e) {
            Log::error('Error getting room reading', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'exists' => false,
                'error' => 'Có lỗi xảy ra khi kiểm tra dữ liệu.'
            ], 500);
        }
    }

    /**
     * ✅ Fixed statistics calculation
     */
    private function calculateStatistics(Request $request)
    {
        try {
            $baseQuery = MeterReading::query();

            // ✅ Apply search filter to statistics
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $baseQuery->where(function ($q) use ($searchTerm) {
                    $q->whereHas('room', function ($roomQuery) use ($searchTerm) {
                        $roomQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                        ->orWhereHas('room.motel', function ($motelQuery) use ($searchTerm) {
                            $motelQuery->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhere('id', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->filled('room_id')) {
                $baseQuery->where('room_id', $request->room_id);
            }

            if ($request->filled('month')) {
                $baseQuery->where('month', $request->month);
            }

            if ($request->filled('year')) {
                $baseQuery->where('year', $request->year);
            }

            if ($request->filled('date_from')) {
                $baseQuery->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $baseQuery->whereDate('created_at', '<=', $request->date_to);
            }

            // Calculate statistics
            $totalReadings = (clone $baseQuery)->count();
            $roomsWithReadings = (clone $baseQuery)->distinct('room_id')->count('room_id');
            $totalElectricity = (clone $baseQuery)->sum('electricity_kwh') ?: 0;
            $totalWater = (clone $baseQuery)->sum('water_m3') ?: 0;

            return [
                'totalReadings' => $totalReadings,
                'roomsWithReadings' => $roomsWithReadings,
                'totalElectricity' => $totalElectricity,
                'totalWater' => $totalWater
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating statistics', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return [
                'totalReadings' => 0,
                'roomsWithReadings' => 0,
                'totalElectricity' => 0,
                'totalWater' => 0
            ];
        }
    }

    /**
     * ✅ Thêm method mới để check thời gian hiện tại (cho API hoặc debugging)
     */
    public function checkCurrentPeriod()
    {
        $periodInfo = $this->meterReadingService->getDisplayPeriodInfo();

        return response()->json([
            'current_period' => $periodInfo,
            'can_input_meter_reading' => $periodInfo['is_in_special_period'],
            'rooms_available' => $this->meterReadingService->getRoomsWithMotel()->flatten()->count(),
            'expiring_contracts' => $this->meterReadingService->getRoomsWithActiveContracts()->flatten()->count()
        ]);
    }

    /**
     * ✅ Thêm method để lấy rooms theo thời gian (cho AJAX)
     */
    public function getRoomsByPeriod(Request $request)
    {
        $periodInfo = $this->meterReadingService->getDisplayPeriodInfo();

        if ($periodInfo['is_in_special_period']) {
            $rooms = $this->meterReadingService->getRoomsWithMotel();
            $displayMode = 'time_based';
        } else {
            $rooms = $this->meterReadingService->getRoomsWithActiveContracts();
            $displayMode = 'active_contracts';
        }

        return response()->json([
            'rooms' => $rooms,
            'display_mode' => $displayMode,
            'period_info' => $periodInfo,
            'total_rooms' => $rooms->flatten()->count(),
            'total_motels' => $rooms->count()
        ]);
    }
}
