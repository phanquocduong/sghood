<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReadingRequest;
use Illuminate\Http\Request;
use App\Services\MeterReadingService;
use App\Http\Controllers\Controller;

class MeterReadingController extends Controller
{
    protected $meterReadingService;

    public function __construct(MeterReadingService $meterReadingService)
    {
        $this->meterReadingService = $meterReadingService;
    }

    public function index()
    {
        $rooms = $this->meterReadingService->getRooms();
        return view('meter_readings.index', compact('rooms'));
    }

    public function store(MeterReadingRequest $request)
    {
        try {
            // Xác thực đã được xử lý bởi MeterReadingRequest

            // Chuẩn bị dữ liệu để tạo chỉ số đồng hồ mới
            $data = $request->only(['room_id', 'month', 'year', 'electricity_kwh', 'water_m3']);

            // Gọi dịch vụ để tạo chỉ số đồng hồ mới
            $meterReading = $this->meterReadingService->createMeterReading($data);

            // Tự động tạo hóa đơn dựa trên chỉ số mới
            $this->meterReadingService->createInvoice($meterReading->id);

            // Chuyển hướng với thông báo thành công
            return redirect()->route('meter_readings.index')->with('success', 'Chỉ số điện nước đã được cập nhật và hóa đơn đã được tạo thành công.');
        } catch (\Exception $e) {
            // Xử lý các ngoại lệ xảy ra trong quá trình thực hiện
            return redirect()->route('meter_readings.index')->with('error', 'Đã xảy ra lỗi khi cập nhật chỉ số điện nước: ' . $e->getMessage());
        }
    }
}
