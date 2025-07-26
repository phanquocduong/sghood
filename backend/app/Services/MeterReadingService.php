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
            // $search = request('search');
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

    public function getRoomsWithMotel()
    {
        $today = now();

        $startDate = $today->copy()->subMonthNoOverflow()->day(28)->startOfDay();
        $endDate = $today->copy()->addMonthNoOverflow()->day(5)->endOfDay();

        // Lấy phòng đã thuê và chưa có meter_readings trong khoảng thời gian
        $rooms = Room::with('motel') // eager load nhà trọ
            ->where('status', 'Đã Thuê')
            ->whereDoesntHave('meterReadings', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get()
            ->groupBy('motel_id'); // group theo nhà trọ

        return $rooms;
    }

    public function createMeterReading(array $data)
    {
        $meterReading = MeterReading::create($data);
        return $meterReading;
    }

    // Tính tiền phòng cho tháng đầu tiên của hợp đồng
    private function calculateFirstMonthRoomFee($contract, $room, $meterReading)
    {
        try {
            $contractStartDate = Carbon::parse($contract->start_date);
            $invoiceCreatedDate = now(); // Ngày tạo hóa đơn (hiện tại)
            $invoiceMonth = $meterReading->month;
            $invoiceYear = $meterReading->year;

            // Kiểm tra xem đã có hóa đơn nào cho hợp đồng này chưa
            $hasExistingInvoices = Invoice::where('contract_id', $contract->id)
                ->where(function($query) use ($invoiceMonth, $invoiceYear) {
                    // Kiểm tra các hóa đơn trước hóa đơn hiện tại
                    $query->where('year', '<', $invoiceYear)
                        ->orWhere(function($q) use ($invoiceMonth, $invoiceYear) {
                            $q->where('year', $invoiceYear)
                            ->where('month', '<', $invoiceMonth);
                        });
                })
                ->exists();

            $roomPrice = $room->price ?? 0;

            if ($hasExistingInvoices) {
                // Đã có hóa đơn trước đó, không phải tháng đầu tiên
                Log::info('Not first month of contract, using full room price', [
                    'contract_start_date' => $contractStartDate->toDateString(),
                    'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                    'invoice_month' => $invoiceMonth,
                    'invoice_year' => $invoiceYear,
                    'room_price' => $roomPrice,
                    'has_existing_invoices' => $hasExistingInvoices
                ]);
                return $roomPrice;
            }

            // Đây là hóa đơn đầu tiên - tính theo số ngày từ start_date đến ngày tạo hóa đơn
            $daysDifference = $contractStartDate->diffInDays($invoiceCreatedDate) + 1; // +1 để bao gồm ngày bắt đầu

            // Tính tỷ lệ dựa trên 30 ngày (1 tháng chuẩn)
            $percentage = min(1.0, $daysDifference / 30.0); // Đảm bảo không vượt quá 100%
            $firstMonthFee = $roomPrice * $percentage;

            Log::info('Calculating first month room fee based on actual days', [
                'contract_start_date' => $contractStartDate->toDateString(),
                'invoice_created_date' => $invoiceCreatedDate->toDateString(),
                'days_difference' => $daysDifference,
                'percentage' => $percentage,
                'full_room_price' => $roomPrice,
                'first_month_fee' => $firstMonthFee,
                'invoice_month' => $invoiceMonth,
                'invoice_year' => $invoiceYear,
                'has_existing_invoices' => $hasExistingInvoices
            ]);

            return round($firstMonthFee, 0);

        } catch (\Exception $e) {
            Log::error('Error calculating first month room fee: ' . $e->getMessage(), [
                'contract_id' => $contract->id ?? null,
                'room_id' => $room->id ?? null,
                'meter_reading_month' => $meterReading->month ?? null,
                'meter_reading_year' => $meterReading->year ?? null
            ]);

            return $room->price ?? 0;
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

            $currentTime = now()->format('His');
            $currentDate = now()->format('Ymd');

            $invoiceCode = 'INV' . $contractId . $currentTime . $currentDate;

            $electricityRate = $motel->electricity_fee ?? 0;
            $waterRate = $motel->water_fee ?? 0;

            $electricityFee = ($meterReading->electricity_kwh ?? 0) * $electricityRate;
            $waterFee = ($meterReading->water_m3 ?? 0) * $waterRate;
            $parkingFee = $motel->parking_fee ?? 0; // Phí giữ xe từ motel
            $junkFee = $motel->junk_fee ?? 0; // Phí rác từ motel
            $internetFee = $motel->internet_fee ?? 0; // Phí internet từ motel
            $serviceFee = $motel->service_fee ?? 0; // Phí dịch vụ từ motel

            // Tính tiền phòng với logic tháng đầu tiên
            $roomFee = $this->calculateFirstMonthRoomFee($contract, $room, $meterReading);

            // Tính tổng số tiền (bao gồm tiền phòng)
            $totalAmount = $electricityFee + $waterFee + $parkingFee + $junkFee + $internetFee + $serviceFee + $roomFee;

            Log::info('Creating invoice with details', [
                'meter_reading_id' => $meterReadingId,
                'contract_id' => $contractId,
                'contract_start_date' => $contract->start_date,
                'room_id' => $room->id,
                'motel_id' => $motel->id,
                'electricity_kwh' => $meterReading->electricity_kwh,
                'water_m3' => $meterReading->water_m3,
                'electricity_rate' => $electricityRate,
                'water_rate' => $waterRate,
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'room_fee' => $roomFee,
                'room_original_price' => $room->price,
                'is_first_month' => Carbon::parse($contract->start_date)->month == $meterReading->month && Carbon::parse($contract->start_date)->year == $meterReading->year,
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
                'room_fee_calculated' => $roomFee
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
