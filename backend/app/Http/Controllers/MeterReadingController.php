<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReadingRequest;
use App\Models\MeterReading;
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
        $rooms = $this->meterReadingService->getRooms();
        // Get all meter readings with optional search functionality
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $meterReadings = $this->meterReadingService->getAllMeterReadings($search, $perPage);

        $isFiltering = request()->hasAny(['room_id', 'month', 'year', 'sortOption']);
        // Logic to handle meter reading index
        return view('meter_readings.index', compact('rooms', 'meterReadings', 'isFiltering'));
    }

    public function store(MeterReadingRequest $request)
    {
        try {
            // Validation is already handled by MeterReadingRequest
            Log::info('Creating meter reading', $request->all());

            // Prepare data for creating a new meter reading
            $data = $request->only(['room_id', 'month', 'year', 'electricity_kwh', 'water_m3']);

            // Call the service to create a new meter reading
            $meterReading = $this->meterReadingService->createMeterReading($data);
            Log::info('Meter reading created successfully', ['id' => $meterReading->id]);

            // Tự động tạo hóa đơn sau khi tạo chỉ số điện nước thành công
            try {
                Log::info('Attempting to create invoice for meter reading', ['meter_reading_id' => $meterReading->id]);
                $invoice = $this->meterReadingService->createInvoice($meterReading->id);
                Log::info('Invoice created successfully', ['invoice_id' => $invoice->id, 'invoice_code' => $invoice->code]);

                // Redirect back with success message bao gồm cả hóa đơn
                return redirect()->route('meter_readings.index')
                    ->with('success', 'Chỉ số điện nước đã được cập nhật thành công và hóa đơn ' . $invoice->code . ' đã được tạo tự động.');
            } catch (\Exception $invoiceError) {
                // Nếu tạo hóa đơn thất bại, vẫn thông báo thành công cho chỉ số điện nước
                Log::error('Error creating invoice after meter reading', [
                    'meter_reading_id' => $meterReading->id,
                    'error' => $invoiceError->getMessage(),
                    'trace' => $invoiceError->getTraceAsString()
                ]);

                return redirect()->route('meter_readings.index')
                    ->with('success', 'Chỉ số điện nước đã được cập nhật thành công.')
                    ->with('warning', 'Tuy nhiên, có lỗi khi tạo hóa đơn tự động: ' . $invoiceError->getMessage());
            }

        } catch (\Exception $e) {
            // Handle any exceptions that occur during the process
            Log::error('Error creating meter reading', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return redirect()->route('meter_readings.index')->with('error', 'Đã xảy ra lỗi khi cập nhật chỉ số điện nước: ' . $e->getMessage());
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
