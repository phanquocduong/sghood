<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\Room;
use App\Models\Invoice;
use App\Mail\InvoiceCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

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
            $roomFee = $room->price ?? 0; // Tiền phòng từ room

            // Tính tổng số tiền (bao gồm tiền phòng)
            $totalAmount = $electricityFee + $waterFee + $parkingFee + $junkFee + $internetFee + $serviceFee + $roomFee;

            Log::info('Creating invoice with details', [
                'meter_reading_id' => $meterReadingId,
                'contract_id' => $contractId,
                'room_id' => $room->id,
                'motel_id' => $motel->id,
                'electricity_kwh' => $meterReading->electricity_kwh,
                'water_m3' => $meterReading->water_m3,
                'electricity_rate' => $electricityRate,
                'water_rate' => $waterRate,
                'electricity_fee' => $electricityFee,
                'water_fee' => $waterFee,
                'room_fee' => $roomFee,
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
                'total_amount' => $totalAmount
            ]);

            // Gửi email thông báo tạo hóa đơn
            try {
                $user = $contract->user;
                $email = $user->email ?? null;
                if ($email) {
                    Mail::to($email)->send(new InvoiceCreated($invoice, $room, $meterReading, $contract));
                    Log::info('Invoice email sent successfully', ['email' => $email, 'invoice_code' => $invoice->code]);
                } else {
                    Log::warning('No email found for contract user', ['contract_id' => $contractId]);
                }
            } catch (\Exception $emailError) {
                Log::error('Error sending invoice email', [
                    'error' => $emailError->getMessage(),
                    'invoice_id' => $invoice->id
                ]);
            }

            //Gửi thông báo đến người dùng
            try {
                $user = $contract->user;
                if ($user) {
                    $notificationdata = [
                        'user_id' => $user->id,
                        'title' => 'Hóa đơn của bạn đã được tạo',
                        'content' => 'Hóa đơn của bạn đã được tạo! Vui lòng xem chi tiết và thanh toán.',
                        'status' => 'Chưa đọc'
                    ];
                    $notification = Notification::create($notificationdata);
                    Log::info('Notification created for invoice', [
                        'contract_id' => $contract->id,
                        'notification_id' => $notification->id,
                        'invoice_id' => $invoice->id
                    ]);

                    // gửi FCM token
                    if ($user->fcm_token) {
                        $messaging = app('firebase.messaging');

                        $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                            ->withNotification(FirebaseNotification::create(
                                $notificationdata['title'],
                                $notificationdata['content']
                            ));

                        try {
                            $messaging->send($fcmMessage);
                            Log::info('FCM sent to user', ['user_id' => $user->id, 'invoice_id' => $invoice->id]);
                        } catch (\Exception $e) {
                            Log::error('FCM send error', ['error' => $e->getMessage(), 'user_id' => $user->id]);
                        }
                    } else {
                        Log::info('No FCM token found for user', ['user_id' => $user->id]);
                    }
                } else {
                    Log::warning('No user found for contract', ['contract_id' => $contractId]);
                }
            } catch (\Exception $notificationError) {
                Log::error('Error creating notification', [
                    'error' => $notificationError->getMessage(),
                    'invoice_id' => $invoice->id
                ]);
            }

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