<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\ContractTenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class InvoiceMonthlySeeder extends Seeder
{
    public function run()
    {
        // Lặp qua tất cả bản ghi MeterReading
        MeterReading::all()->each(function ($meterReading) {
            // Lấy thông tin contract và motel liên quan
            $contract = $meterReading->room->contract;
            $motel = $meterReading->room->motel;

            if (!$contract || !$motel) {
                return; // Bỏ qua nếu không có contract hoặc motel
            }

            // Tính số lượng người ở (contract_tenants + 1)
            $totalOccupants = ContractTenant::where('contract_id', $contract->id)->count() + 1;

            // Tính tỷ lệ phí và các phí liên quan
            $fees = $this->calculateProportionalFees($contract, $meterReading->room, $meterReading, $motel, $totalOccupants);

            // Tính phí điện
            $previousMonth = $meterReading->month - 1;
            $previousYear = $meterReading->year;
            if ($previousMonth == 0) {
                $previousMonth = 12;
                $previousYear -= 1;
            }
            $previousMeterReading = MeterReading::where('room_id', $meterReading->room_id)
                ->where('month', $previousMonth)
                ->where('year', $previousYear)
                ->first();
            $electricityKwh = $previousMeterReading
                ? ($meterReading->electricity_kwh - $previousMeterReading->electricity_kwh) * $fees['percentage']
                : $meterReading->electricity_kwh * $fees['percentage'];
            $electricityFee = $electricityKwh * $motel->electricity_fee;

            // Tính phí nước
            $waterM3 = $previousMeterReading
                ? ($meterReading->water_m3 - $previousMeterReading->water_m3) * $fees['percentage']
                : $meterReading->water_m3 * $fees['percentage'];
            $waterFee = $waterM3 * $motel->water_fee;

            // Tính tổng tiền
            $totalAmount = $fees['room_fee'] + $electricityFee + $waterFee + $fees['parking_fee'] + $fees['junk_fee'] + $fees['internet_fee'] + $fees['service_fee'];

            // Tạo mã invoice duy nhất
            $code = 'INV' . $contract->id . $meterReading->created_at->format('YmdHis');

            // Gán trạng thái ngẫu nhiên cho tháng 7/2025
            $status = 'Đã trả';
            if ($meterReading->month == 7 && $meterReading->year == 2025) {
                $status = (mt_rand(1, 100) <= 90) ? 'Đã trả' : 'Chưa trả';
            }

            // Tạo bản ghi Invoice
            Invoice::create([
                'code' => $code,
                'contract_id' => $contract->id,
                'meter_reading_id' => $meterReading->id,
                'type' => 'Hàng tháng',
                'month' => $meterReading->month,
                'year' => $meterReading->year,
                'room_fee' => $fees['room_fee'],
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'parking_fee' => $fees['parking_fee'],
                'junk_fee' => $fees['junk_fee'],
                'internet_fee' => $fees['internet_fee'],
                'service_fee' => $fees['service_fee'],
                'total_amount' => $totalAmount,
                'status' => $status,
                'refunded_at' => null,
                'created_at' => $meterReading->created_at,
                'updated_at' => $meterReading->updated_at,
            ]);
        });
    }

    private function calculateProportionalFees($contract, $room, $meterReading, $motel, $totalOccupants = 1)
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

            // Xác định tháng cuối dựa trên tháng/năm của hóa đơn
            $isLastMonth = $invoiceYear == $contractEndDate->year && $invoiceMonth == $contractEndDate->month;

            // Kiểm tra điều kiện nhập chỉ số: chỉ cho phép từ 3 ngày trước end_date
            $allowEntryDate = $contractEndDate->copy()->subDays(3);
            if ($isLastMonth && $invoiceCreatedDate->lt($allowEntryDate)) {
                Log::info('Invoice creation not allowed before 3 days prior to contract end date', [
                    'contract_end_date' => $contractEndDate->toDateString(),
                    'allow_entry_date' => $allowEntryDate->toDateString(),
                    'invoice_created_date' => $invoiceCreatedDate->toDateString()
                ]);
                throw new \Exception('Không được tạo hóa đơn trước ngày ' . $allowEntryDate->toDateString());
            }

            // Lấy giá gốc các dịch vụ
            $roomPrice = $contract->rental_price ?? 0;
            $baseParkingFee = $motel->parking_fee ?? 0;
            $baseJunkFee = $motel->junk_fee ?? 0;
            $baseInternetFee = $motel->internet_fee ?? 0;
            $baseServiceFee = $motel->service_fee ?? 0;

            // Tính phí dịch vụ dựa trên số người ở
            $parkingFee = $baseParkingFee * $totalOccupants; // Phí giữ xe theo đầu người
            $junkFee = $baseJunkFee; // Phí rác cố định
            $internetFee = $baseInternetFee; // Phí internet cố định
            $serviceFee = $baseServiceFee; // Phí dịch vụ cố định

            // Tính tỷ lệ phí dựa trên số ngày sử dụng
            $percentage = 1.0;
            $daysDifference = null;

            if ($isLastMonth) {
                // Tháng cuối cùng: tính từ đầu tháng đến end_date
                $monthStart = Carbon::create($invoiceYear, $invoiceMonth, 1);
                $daysDifference = (int) $monthStart->diffInDays($contractEndDate) + 1;
                $percentage = min(1.0, $daysDifference / 30.0);
                Log::info('Calculating last month fees with proportional rates', [
                    'contract_end_date' => $contractEndDate->toDateString(),
                    'month_start' => $monthStart->toDateString(),
                    'days_difference' => $daysDifference,
                    'percentage' => $percentage,
                    'total_occupants' => $totalOccupants
                ]);
            } elseif (!$hasExistingInvoices) {
                // Tháng đầu tiên: tính từ start_date đến cuối tháng
                $monthEnd = Carbon::create($invoiceYear, $invoiceMonth, 1)->endOfMonth();
                $daysDifference = (int) $contractStartDate->diffInDays($monthEnd) + 1;
                $percentage = min(1.0, $daysDifference / 30.0);
                Log::info('Calculating first month fees with proportional rates', [
                    'contract_start_date' => $contractStartDate->toDateString(),
                    'month_end' => $monthEnd->toDateString(),
                    'days_difference' => $daysDifference,
                    'percentage' => $percentage,
                    'total_occupants' => $totalOccupants
                ]);
            } else {
                // Không phải tháng đầu hoặc cuối, trả về phí đầy đủ
                Log::info('Not first or last month of contract, using full service fees', [
                    'contract_start_date' => $contractStartDate->toDateString(),
                    'contract_end_date' => $contractEndDate->toDateString(),
                    'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                    'invoice_month' => $invoiceMonth,
                    'invoice_year' => $invoiceYear,
                    'total_occupants' => $totalOccupants
                ]);
                return [
                    'room_fee' => $roomPrice,
                    'parking_fee' => $parkingFee,
                    'junk_fee' => $junkFee,
                    'internet_fee' => $internetFee,
                    'service_fee' => $serviceFee,
                    'percentage' => $percentage
                ];
            }

            $calculatedRoomFee = $roomPrice * $percentage;
            $calculatedParkingFee = $parkingFee * $percentage;
            $calculatedJunkFee = $junkFee * $percentage;
            $calculatedInternetFee = $internetFee * $percentage;
            $calculatedServiceFee = $serviceFee * $percentage;

            Log::info('Calculated proportional fees with occupant count', [
                'contract_start_date' => $contractStartDate->toDateString(),
                'contract_end_date' => $contractEndDate->toDateString(),
                'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                'days_difference' => $daysDifference,
                'percentage' => $percentage,
                'total_occupants' => $totalOccupants,
                'base_fees' => [
                    'room_price' => $roomPrice,
                    'base_parking_fee' => $baseParkingFee,
                    'base_junk_fee' => $baseJunkFee,
                    'base_internet_fee' => $baseInternetFee,
                    'base_service_fee' => $baseServiceFee
                ],
                'calculated_fees_before_percentage' => [
                    'parking_fee' => $parkingFee,
                    'junk_fee' => $junkFee,
                    'internet_fee' => $internetFee,
                    'service_fee' => $serviceFee
                ],
                'final_calculated_fees' => [
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
                'service_fee' => round($calculatedServiceFee, 0),
                'percentage' => $percentage
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating proportional fees: ' . $e->getMessage(), [
                'contract_id' => $contract->id ?? null,
                'room_id' => $room->id ?? null,
                'meter_reading_month' => $meterReading->month ?? null,
                'meter_reading_year' => $meterReading->year ?? null,
                'total_occupants' => $totalOccupants
            ]);

            return [
                'room_fee' => $contract->rental_price ?? 0,
                'parking_fee' => ($motel->parking_fee ?? 0) * $totalOccupants,
                'junk_fee' => $motel->junk_fee ?? 0,
                'internet_fee' => $motel->internet_fee ?? 0,
                'service_fee' => $motel->service_fee ?? 0,
                'percentage' => 1.0
            ];
        }
    }
}
