<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\Room;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendInvoiceCreatedNotification;
use Carbon\Carbon;

class MeterReadingService
{
    public function getAllMeterReadings(?string $search = null, int $perPage = 10)
    {
        return MeterReading::when($search, function ($query, $search) {
            return $query->where('room_id', 'like', "%{$search}%")
                ->orWhere('month', 'like', "%{$search}%")
                ->orWhere('year', 'like', "%{$search}%")
                ->orWhere('electricity_kwh', 'like', "%{$search}%")
                ->orWhere('water_m3', 'like', "%{$search}%")
                ->orWhereHas('room', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })->paginate($perPage);
    }

    /**
     * Lấy phòng cần nhập chỉ số trong thời gian quy định (28 -> 5)
     */
    public function getRoomsWithMotel()
    {
        try {
            $today = now();
            $currentDay = $today->day;

            // Xác định tháng cần nhập chỉ số một cách rõ ràng
            if ($currentDay >= 28) {
                // Từ ngày 28 trở đi của tháng hiện tại -> nhập chỉ số cho tháng hiện tại
                $targetMonth = $today->month;
                $targetYear = $today->year;
            } elseif ($currentDay <= 5) {
                // Từ ngày 1-5 của tháng hiện tại -> nhập chỉ số cho tháng trước
                $previousMonth = $today->copy()->subMonthNoOverflow();
                $targetMonth = $previousMonth->month;
                $targetYear = $previousMonth->year;
            } else {
                // Ngoài thời gian cho phép (11-27) -> không có phòng nào cần nhập
                Log::info('Outside meter reading period', [
                    'current_date' => $today->toDateString(),
                    'current_day' => $currentDay,
                    'message' => 'Not in allowed period (28th-5th next month)'
                ]);
                return collect();
            }

            Log::info('Getting rooms for meter reading period', [
                'current_date' => $today->toDateString(),
                'current_day' => $currentDay,
                'target_month' => $targetMonth,
                'target_year' => $targetYear
            ]);

            $rooms = Room::with([
                'motel',
                'contracts' => function ($query) {
                    $query->where('status', 'Hoạt động')
                        ->orderBy('end_date', 'desc');
                }
            ])
                ->where('status', 'Đã Thuê')
                ->whereHas('contracts', function ($query) {
                    $query->where('status', 'Hoạt động')
                        ->where('end_date', '>', now()); // Hợp đồng còn hiệu lực
                })
                ->whereDoesntHave('meterReadings', function ($query) use ($targetMonth, $targetYear) {
                    // Chưa có chỉ số cho tháng target
                    $query->where('month', $targetMonth)
                        ->where('year', $targetYear);
                })
                ->get()
                ->groupBy('motel_id');

            Log::info('Found rooms for meter reading', [
                'total_motels' => $rooms->count(),
                'total_rooms' => $rooms->flatten()->count()
            ]);

            return $rooms;

        } catch (\Exception $e) {
            Log::error('Error getting rooms for meter reading', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect();
        }
    }

    /**
     * Lấy phòng có hợp đồng gần hết hạn (trong 3 ngày) và chưa nhập chỉ số tháng hiện tại
     */
    public function getRoomsWithActiveContracts()
    {
        try {
            $today = now();
            $threeDaysFromNow = $today->copy()->addDays(3); // 3 ngày tới
            $currentMonth = $today->month;
            $currentYear = $today->year;

            Log::info('Getting rooms with contracts expiring soon', [
                'current_date' => $today->toDateString(),
                'expiry_check_date' => $threeDaysFromNow->toDateString(),
                'current_month' => $currentMonth,
                'current_year' => $currentYear
            ]);

            $rooms = Room::with([
                'motel',
                'contracts' => function ($query) {
                    $query->where('status', 'Hoạt động')
                        ->orderBy('end_date', 'desc');
                }
            ])
                ->where('status', 'Đã Thuê')
                ->whereHas('contracts', function ($query) use ($today, $threeDaysFromNow) {
                    $query->where('status', 'Hoạt động')
                        ->where('end_date', '>', $today) // Hợp đồng còn hiệu lực
                        ->where('end_date', '<=', $threeDaysFromNow); // Hết hạn trong 3 ngày
                })
                ->whereDoesntHave('meterReadings', function ($query) use ($currentMonth, $currentYear) {
                    // Chưa có chỉ số cho tháng hiện tại
                    $query->where('month', $currentMonth)
                        ->where('year', $currentYear);
                })
                ->get()
                ->groupBy('motel_id');

            Log::info('Found rooms with contracts expiring soon', [
                'total_motels' => $rooms->count(),
                'total_rooms' => $rooms->flatten()->count()
            ]);

            return $rooms;

        } catch (\Exception $e) {
            Log::error('Error getting rooms with expiring contracts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect();
        }
    }

    /**
     * Kiểm tra xem có thể tạo meter reading không (cho validation)
     */
    public function canCreateMeterReadingForRoom($roomId, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        // Kiểm tra đã có chỉ số cho tháng này chưa
        $existingReading = MeterReading::where('room_id', $roomId)
            ->where('month', $month)
            ->where('year', $year)
            ->exists();

        if ($existingReading) {
            return false;
        }

        // Kiểm tra phòng có hợp đồng hoạt động không
        $room = Room::with('contracts')
            ->where('id', $roomId)
            ->where('status', 'Đã Thuê')
            ->whereHas('contracts', function ($query) {
                $query->where('status', 'Hoạt động')
                    ->where('end_date', '>', now());
            })
            ->first();

        return $room !== null;
    }

    /**
     * Lấy thông tin thời gian hiển thị cho view
     */
    public function getDisplayPeriodInfo()
    {
        $today = now();
        $currentDay = $today->day;

        // Xác định khoảng thời gian được phép và tháng hiển thị
        $isInAllowedPeriod = ($currentDay >= 28) || ($currentDay <= 5);

        if ($currentDay >= 28) {
            // Từ ngày 28 trở đi -> nhập chỉ số cho tháng hiện tại
            $displayMonth = $today->month;
            $displayYear = $today->year;
            $periodDescription = "Nhập chỉ số tháng {$displayMonth}/{$displayYear}";

            $startDate = $today->copy()->day(28)->startOfDay();
            $endDate = $today->copy()->addMonthNoOverflow()->day(5)->endOfDay();

        } elseif ($currentDay <= 5) {
            // Từ ngày 1-5 -> nhập chỉ số cho tháng trước
            $previousMonth = $today->copy()->subMonthNoOverflow();
            $displayMonth = $previousMonth->month;
            $displayYear = $previousMonth->year;
            $periodDescription = "Nhập chỉ số tháng {$displayMonth}/{$displayYear}";

            $startDate = $previousMonth->copy()->day(28)->startOfDay();
            $endDate = $today->copy()->day(5)->endOfDay();

        } else {
            // Ngoài thời gian cho phép
            $displayMonth = $today->month;
            $displayYear = $today->year;
            $periodDescription = "Ngoài thời gian nhập chỉ số (chỉ từ 28 - 5 tháng sau)";

            // Tính toán kỳ tiếp theo
            $nextPeriodStart = $today->copy()->day(28);
            $startDate = $nextPeriodStart;
            $endDate = $nextPeriodStart->copy()->addMonthNoOverflow()->day(5)->endOfDay();
        }

        // ✅ Thêm validation cho edge cases
        try {
            // Kiểm tra xem ngày 28 có tồn tại trong tháng không
            $testDate = $today->copy();
            if ($testDate->day > 28) {
                $testDate->day(28); // Có thể throw exception nếu tháng không có ngày 28
            }
        } catch (\Exception $e) {
            Log::warning('Date calculation issue, using end of month instead', [
                'current_date' => $today->toDateString(),
                'error' => $e->getMessage()
            ]);

            $startDate = $today->copy()->endOfMonth()->startOfDay();
        }

        return [
            'display_month' => $displayMonth,
            'display_year' => $displayYear,
            'is_in_special_period' => $isInAllowedPeriod,
            'current_day' => $currentDay,
            'period_description' => $periodDescription,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'readable_period' => "Từ {$startDate->format('d/m/Y')} đến {$endDate->format('d/m/Y')}"
        ];
    }

    public function createMeterReading(array $data)
    {
        $meterReading = MeterReading::create($data);
        return $meterReading;
    }

    //Tính toán số điện/nước tiêu thụ và lấy chỉ số tháng gần nhất
    public function getConsumptionAndPreviousReadings($meterReading)
    {
        $electricityConsumption = $meterReading->electricity_kwh ?? 0;
        $waterConsumption = $meterReading->water_m3 ?? 0;
        $previousElectricityKwh = 0;
        $previousWaterM3 = 0;

        // Lấy chỉ số tháng gần nhất trước tháng hiện tại
        $previousMeterReading = MeterReading::where('room_id', $meterReading->room_id)
            ->where(function ($query) use ($meterReading) {
                $query->where('year', '<', $meterReading->year)
                    ->orWhere(function ($q) use ($meterReading) {
                        $q->where('year', $meterReading->year)
                            ->where('month', '<', $meterReading->month);
                    });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if ($previousMeterReading) {
            $electricityConsumption = ($meterReading->electricity_kwh ?? 0) - ($previousMeterReading->electricity_kwh ?? 0);
            $waterConsumption = ($meterReading->water_m3 ?? 0) - ($previousMeterReading->water_m3 ?? 0);
            $electricityConsumption = max(0, $electricityConsumption);
            $waterConsumption = max(0, $waterConsumption);
            $previousElectricityKwh = $previousMeterReading->electricity_kwh ?? 0;
            $previousWaterM3 = $previousMeterReading->water_m3 ?? 0;

            // Cảnh báo nếu chỉ số hiện tại nhỏ hơn chỉ số trước
            if ($meterReading->electricity_kwh < $previousMeterReading->electricity_kwh || $meterReading->water_m3 < $previousMeterReading->water_m3) {
                Log::warning('Current meter reading is less than previous reading', [
                    'room_id' => $meterReading->room_id,
                    'current_electricity' => $meterReading->electricity_kwh,
                    'previous_electricity' => $previousMeterReading->electricity_kwh,
                    'current_water' => $meterReading->water_m3,
                    'previous_water' => $previousMeterReading->water_m3
                ]);
            }
        } else {
            Log::warning('No previous meter reading found, assuming zero consumption for first month', [
                'room_id' => $meterReading->room_id,
                'month' => $meterReading->month,
                'year' => $meterReading->year
            ]);
        }

        return [
            'electricity_consumption' => $electricityConsumption,
            'water_consumption' => $waterConsumption,
            'previous_electricity_kwh' => $previousElectricityKwh,
            'previous_water_m3' => $previousWaterM3,
        ];
    }

    private function calculateProportionalFees($contract, $room, $meterReading, $motel)
    {
        try {
            $contractStartDate = Carbon::parse($contract->start_date);
            $contractEndDate = Carbon::parse($contract->end_date);
            $invoiceCreatedDate = now();
            $invoiceMonth = $meterReading->month;
            $invoiceYear = $meterReading->year;

            // Kiểm tra xem đây có phải là tháng đầu tiên hoặc tháng cuối cùng của hợp đồng không
            $hasExistingInvoices = Invoice::where('contract_id', $contract->id)
                ->where(function ($query) use ($invoiceMonth, $invoiceYear) {
                    $query->where('year', '<', $invoiceYear)
                        ->orWhere(function ($q) use ($invoiceMonth, $invoiceYear) {
                            $q->where('year', $invoiceYear)
                                ->where('month', '<', $invoiceMonth);
                        });
                })
                ->exists();

            // Lấy giá gốc các dịch vụ
            $roomPrice = $room->price ?? 0;
            $parkingFee = $motel->parking_fee ?? 0;
            $junkFee = $motel->junk_fee ?? 0;
            $internetFee = $motel->internet_fee ?? 0;
            $serviceFee = $motel->service_fee ?? 0;

            // Kiểm tra tháng cuối dựa trên ngày hết hạn và ngày hiện tại + 3 ngày
            $checkDate = now()->addDays(3); // Ngày kiểm tra là +3 ngày
            $isLastMonth = $checkDate->year == $contractEndDate->year &&
                          $checkDate->month == $contractEndDate->month &&
                          $checkDate->day <= $contractEndDate->day;

            // Nếu không phải tháng đầu tiên cũng không phải tháng cuối cùng, trả về phí đầy đủ
            if (!$hasExistingInvoices && !$isLastMonth) {
                Log::info('Not first or last month of contract, using full service fees', [
                    'contract_start_date' => $contractStartDate->toDateString(),
                    'contract_end_date' => $contractEndDate->toDateString(),
                    'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                    'invoice_month' => $invoiceMonth,
                    'invoice_year' => $invoiceYear
                ]);

                return [
                    'room_fee' => $roomPrice,
                    'parking_fee' => $parkingFee,
                    'junk_fee' => $junkFee,
                    'internet_fee' => $internetFee,
                    'service_fee' => $serviceFee
                ];
            }

            // Tính tỷ lệ phí dựa trên số ngày sử dụng
            $percentage = 1.0;
            if ($hasExistingInvoices) {
                // Tháng đầu tiên: tính từ start_date đến ngày tạo hóa đơn
                $daysDifference = $contractStartDate->diffInDays($invoiceCreatedDate) + 1;
                $percentage = min(1.0, $daysDifference / 30.0);
                Log::info('Calculating first month fees with proportional rates', [
                    'contract_start_date' => $contractStartDate->toDateString(),
                    'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                    'days_difference' => $daysDifference,
                    'percentage' => $percentage
                ]);
            } elseif ($isLastMonth) {
                // Tháng cuối cùng: tính từ đầu tháng đến end_date
                $monthStart = Carbon::create($invoiceYear, $invoiceMonth, 1);
                $daysDifference = $monthStart->diffInDays($contractEndDate) + 1;
                $percentage = min(1.0, $daysDifference / 30.0);
                Log::info('Calculating last month fees with proportional rates', [
                    'contract_end_date' => $contractEndDate->toDateString(),
                    'month_start' => $monthStart->toDateString(),
                    'days_difference' => $daysDifference,
                    'percentage' => $percentage
                ]);
            }

            $calculatedRoomFee = $roomPrice * $percentage;
            $calculatedParkingFee = $parkingFee * $percentage;
            $calculatedJunkFee = $junkFee * $percentage;
            $calculatedInternetFee = $internetFee * $percentage;
            $calculatedServiceFee = $serviceFee * $percentage;

            Log::info('Calculated proportional fees', [
                'contract_start_date' => $contractStartDate->toDateString(),
                'contract_end_date' => $contractEndDate->toDateString(),
                'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                'days_difference' => $daysDifference ?? null,
                'percentage' => $percentage,
                'original_fees' => [
                    'room_price' => $roomPrice,
                    'parking_fee' => $parkingFee,
                    'junk_fee' => $junkFee,
                    'internet_fee' => $internetFee,
                    'service_fee' => $serviceFee
                ],
                'calculated_fees' => [
                    'room_fee' => $calculatedRoomFee,
                    'parking_fee' => $calculatedParkingFee,
                    'junk_fee' => $calculatedJunkFee,
                    'internet_fee' => $calculatedInternetFee,
                    'service_fee' => $calculatedServiceFee
                ],
                'invoice_month' => $invoiceMonth,
                'invoice_year' => $invoiceYear
            ]);

            return [
                'room_fee' => round($calculatedRoomFee, 0),
                'parking_fee' => round($calculatedParkingFee, 0),
                'junk_fee' => round($calculatedJunkFee, 0),
                'internet_fee' => round($calculatedInternetFee, 0),
                'service_fee' => round($calculatedServiceFee, 0)
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating proportional fees: ' . $e->getMessage(), [
                'contract_id' => $contract->id ?? null,
                'room_id' => $room->id ?? null,
                'meter_reading_month' => $meterReading->month ?? null,
                'meter_reading_year' => $meterReading->year ?? null
            ]);

            return [
                'room_fee' => $room->price ?? 0,
                'parking_fee' => $motel->parking_fee ?? 0,
                'junk_fee' => $motel->junk_fee ?? 0,
                'internet_fee' => $motel->internet_fee ?? 0,
                'service_fee' => $motel->service_fee ?? 0
            ];
        }
    }

    public function createInvoice($meterReadingId)
    {
        try {
            $meterReading = MeterReading::with(['room.motel', 'room.activeContract.user'])->findOrFail($meterReadingId);

            $room = $meterReading->room;
            if (!$room) {
                throw new \Exception('Không tìm thấy thông tin phòng cho chỉ số điện nước này.');
            }

            $motel = $room->motel;
            if (!$motel) {
                throw new \Exception('Không tìm thấy thông tin nhà trọ cho phòng này.');
            }

            $contract = $room->activeContract;
            if (!$contract) {
                throw new \Exception('Không tìm thấy hợp đồng hoạt động cho phòng này.');
            }

            $contractId = $contract->id;

            // Kiểm tra xem đã có hóa đơn cho tháng này chưa
            $existingInvoice = Invoice::where('contract_id', $contractId)
                ->where('month', $meterReading->month)
                ->where('year', $meterReading->year)
                ->where('type', 'Hàng tháng')
                ->first();

            if ($existingInvoice) {
                throw new \Exception('Hóa đơn cho tháng ' . $meterReading->month . '/' . $meterReading->year . ' đã tồn tại.');
            }

            // Tính số điện và nước tiêu thụ
            $consumptionData = $this->getConsumptionAndPreviousReadings($meterReading);
            $electricityConsumption = $consumptionData['electricity_consumption'];
            $waterConsumption = $consumptionData['water_consumption'];

            // Lấy đơn giá điện nước từ nhà trọ
            $electricityRate = $motel->electricity_fee ?? 0;
            $waterRate = $motel->water_fee ?? 0;

            // Tính phí điện và nước dựa trên lượng tiêu thụ
            $electricityFee = $electricityConsumption * $electricityRate;
            $waterFee = $waterConsumption * $waterRate;

            // Tính các phí dịch vụ với logic tháng đầu tiên hoặc cuối cùng
            $calculatedFees = $this->calculateProportionalFees($contract, $room, $meterReading, $motel);

            $roomFee = $calculatedFees['room_fee'];
            $parkingFee = $calculatedFees['parking_fee'];
            $junkFee = $calculatedFees['junk_fee'];
            $internetFee = $calculatedFees['internet_fee'];
            $serviceFee = $calculatedFees['service_fee'];

            // Tính tổng số tiền
            $totalAmount = $electricityFee + $waterFee + $parkingFee + $junkFee + $internetFee + $serviceFee + $roomFee;

            // Tạo mã hóa đơn
            $currentTime = now()->format('His');
            $currentDate = now()->format('Ymd');
            $invoiceCode = 'INV' . $contractId . $currentTime . $currentDate;

            // Ghi log thông tin
            Log::info('Creating invoice with consumption-based fees', [
                'meter_reading_id' => $meterReadingId,
                'contract_id' => $contractId,
                'contract_start_date' => $contract->start_date,
                'contract_end_date' => $contract->end_date,
                'electricity_consumption' => $electricityConsumption,
                'water_consumption' => $waterConsumption,
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'room_fee' => $roomFee,
                'parking_fee' => $parkingFee,
                'junk_fee' => $junkFee,
                'internet_fee' => $internetFee,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount
            ]);

            // Tạo hóa đơn
            $invoice = Invoice::create([
                'contract_id' => $contractId,
                'meter_reading_id' => $meterReading->id,
                'code' => $invoiceCode,
                'type' => 'Hàng tháng',
                'month' => $meterReading->month,
                'year' => $meterReading->year,
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'parking_fee' => $parkingFee,
                'junk_fee' => $junkFee,
                'internet_fee' => $internetFee,
                'service_fee' => $serviceFee,
                'room_fee' => $roomFee,
                'total_amount' => $totalAmount,
                'status' => 'Chưa trả',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->code,
                'meter_reading_id' => $meterReadingId,
                'total_amount' => $totalAmount,
                'calculated_fees' => $calculatedFees
            ]);

            // Dispatch job để gửi email và thông báo
            SendInvoiceCreatedNotification::dispatch($invoice, $room, $meterReading, $contract);

            Log::info('Invoice notification job dispatched', [
                'invoice_id' => $invoice->id,
                'job' => 'SendInvoiceCreatedNotification'
            ]);

            return $invoice;

        } catch (\Exception $e) {
            Log::error('Error in createInvoice method', [
                'meter_reading_id' => $meterReadingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
