<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeterReadingRequest;
use App\Models\MeterReading;
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

            // Prepare data for creating a new meter reading
            $data = $request->only(['room_id', 'month', 'year', 'electricity_kwh', 'water_m3']);

            // Call the service to create a new meter reading
            $meterReading = $this->meterReadingService->createMeterReading($data);

            // Redirect back with success message
            return redirect()->route('meter_readings.index')->with('success', 'Chỉ số điện nước đã được cập nhật thành công.');
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the process
            return redirect()->route('meter_readings.index')->with('error', 'Đã xảy ra lỗi khi cập nhật chỉ số điện nước: ');
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
