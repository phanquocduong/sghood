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
            // Trong thời gian nhập chỉ số (28 -> 5): hiển thị phòng cần nhập chỉ số
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
        $query = MeterReading::query()->with('room');

        if ($request->room_id) {
            $query->whereHas('room', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->room_id . '%');
            });
        }

        if ($request->month) {
            $query->where('month', $request->month);
        }

        if ($request->year) {
            $query->where('year', $request->year);
        }

        if ($request->sortOption) {
            switch ($request->sortOption) {
                case 'room_asc':
                    $query->join('rooms', 'rooms.id', '=', 'meter_readings.room_id')->orderBy('rooms.name', 'asc');
                    break;
                case 'room_desc':
                    $query->join('rooms', 'rooms.id', '=', 'meter_readings.room_id')->orderBy('rooms.name', 'desc');
                    break;
                case 'month_desc':
                    $query->orderBy('month', 'desc');
                    break;
                case 'month_asc':
                    $query->orderBy('month', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
            }
        }

        $meterReadings = $query->paginate(10);

        if ($request->ajax()) {
            return view('meter_readings._meter_readings_table', compact('meterReadings'));
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