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
        // $meterReadings = \App\Models\MeterReading::all();
        $rooms = $this->meterReadingService->getRooms();
        // Logic to handle meter reading index
        return view('meter_readings.index', compact('rooms'));
    }
    public function store(MeterReadingRequest $request)
    {
      try{
          // Validation is already handled by MeterReadingRequest
        
        // Prepare data for creating a new meter reading
        $data = $request->only(['room_id', 'month', 'year', 'electricity_kwh', 'water_m3']);
        
        // Call the service to create a new meter reading
        $meterReading = $this->meterReadingService->createMeterReading($data);

        // Redirect back with success message
        return redirect()->route('meter_readings.index')->with('success', 'Chỉ số điện nước đã được cập nhật thành công.');
      }catch (\Exception $e) {
          // Handle any exceptions that occur during the process
          return redirect()->route('meter_readings.index')->with('error', 'Đã xảy ra lỗi khi cập nhật chỉ số điện nước: ' . $e->getMessage());
      }
    }
}
