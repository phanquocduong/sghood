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
    public function getAllMeterReadings()
    {
        // Logic để lấy tất cả chỉ số đồng hồ
        return MeterReading::all();
    }

    public function getRooms()
    {
        // Lấy tháng và năm hiện tại
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Sử dụng leftJoin để tìm các phòng chưa có chỉ số đồng hồ tháng này
        $rooms = Room::where('rooms.status', 'Đã Thuê')
            ->leftJoin('meter_readings', function ($join) use ($currentMonth, $currentYear) {
                $join->on('rooms.id', '=', 'meter_readings.room_id')
                    ->where('meter_readings.month', '=', $currentMonth)
                    ->where('meter_readings.year', '=', $currentYear);
            })
            ->whereNull('meter_readings.id') // Lọc phòng chưa có chỉ số
            ->select('rooms.*') // Chỉ lấy thông tin từ bảng rooms
            ->get();

        return $rooms;
    }

    public function createMeterReading(array $data)
    {
        $meterReading = MeterReading::create($data);
        return $meterReading;
    }

    public function createInvoice($meterReadingId)
    {
        $meterReading = MeterReading::findOrFail($meterReadingId);

        $room = $meterReading->room;

        $motel = $room->motel;

        $contract = $room->activeContract;
        $contractId = $contract ? $contract->id : null;

        if (!$contractId) {
            throw new \Exception('Không tìm thấy hợp đồng hoạt động cho phòng này.');
        }

        $currentTime = now()->format('His');
        $currentDate = now()->format('Ymd');

        $invoiceCode = 'INV' . $contractId . $currentTime . $currentDate;

        $electricityRate = $motel->electricity_fee;
        $waterRate = $motel->water_fee;

        $electricityFee = $meterReading->electricity_kwh * $electricityRate;
        $waterFee = $meterReading->water_m3 * $waterRate;
        $parkingFee = $motel->parking_fee; // Phí giữ xe từ motel
        $junkFee = $motel->junk_fee; // Phí rác từ motel
        $internetFee = $motel->internet_fee; // Phí internet từ motel
        $serviceFee = $motel->service_fee; // Phí dịch vụ từ motel
        $roomFee = $room->price; // Tiền phòng từ room


        // Tính tổng số tiền (bao gồm tiền phòng)
        $totalAmount = $electricityFee + $waterFee + $parkingFee + $junkFee + $internetFee + $serviceFee + $roomFee;

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
            'total_amount' => $totalAmount,
            'status' => 'Chưa trả',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Gửi email thông báo tạo hóa đơn
        $email= $room->activeContract->user->email ?? null;
        Mail::to($email)->send(new InvoiceCreated($invoice, $room, $meterReading, $contract));

        //Gửi thông báo đến người dùng
        $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hóa đơn của bạn đã được tạo',
                    'content' => 'Hóa đơn của bạn đã được tạo! Vui lòng xem chi tiết và thanh toán.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract revision', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
        return $invoice;
    }
}
