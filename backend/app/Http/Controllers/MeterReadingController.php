<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReadingRequest;
use App\Models\MeterReading;
use App\Models\Room;
use DB;
use Illuminate\Http\Request;
use App\Services\MeterReadingService;
use App\Http\Controllers\Controller;
use Log;

class MeterReadingController extends Controller
{
    protected $meterReadingService;

    public function __construct(MeterReadingService $meterReadingService)
    {
        $this->meterReadingService = $meterReadingService;
    }

    public function index(Request $request)
    {
        $rooms = $this->meterReadingService->getRoomsWithMotel();
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $meterReadings = $this->meterReadingService->getAllMeterReadings($search, $perPage);
        $isFiltering = $request->hasAny(['room_id', 'month', 'year', 'sortOption']);

        $data = [
            'rooms' => $rooms,
            'meterReadings' => $meterReadings,
            'isFiltering' => $isFiltering,
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

            return redirect()->route('meter_readings.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating meter readings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Lưu dữ liệu vào session để sử dụng trong modal
            session()->flash('motel_data', [
                'motel_name' => $request->input('motel_name', 'Unknown'),
                'month' => $month,
                'year' => $year,
                'rooms' => collect($readings)->map(function ($reading) {
                    return [
                        'id' => $reading['room_id'],
                        'name' => Room::find($reading['room_id'])->name ?? 'Unknown',
                    ];
                })->toArray(),
                'readings' => $readings,
            ]);

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

}