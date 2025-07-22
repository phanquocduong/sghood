<?php

namespace App\Services;

use App\Models\Checkout;
use App\Mail\CheckoutStatusUpdated;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class CheckoutService
{
    public function getCheckouts($filters = [])
    {
        $query = Checkout::with(['contract.room']);

        if (isset($filters['inventory_status']) && !empty($filters['inventory_status'])) {
            $query->where('inventory_status', $filters['inventory_status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $querySearch = trim($filters['querySearch'] ?? '');

        if (!empty($querySearch)) {
            $query->whereHas('contract.room', function ($q) use ($querySearch) {
                $q->where('name', 'like', '%' . $querySearch . '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        return $query->paginate($perPage);
    }

    public function getCheckoutById($id)
    {
        return Checkout::with(['contract.room', 'contract.user'])->findOrFail($id);
    }

    public function updateCheckout($id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $checkout = Checkout::with(['contract.room', 'contract.user'])->findOrFail($id);
                $depositAmount = $checkout->contract->deposit_amount ?? 0;

                // Xử lý inventory details
                $inventoryDetails = [];
                $deductionAmount = 0;

                if (isset($data['item_name']) && is_array($data['item_name'])) {
                    foreach ($data['item_name'] as $index => $name) {
                        $name = trim($name);
                        if ($name === '') {
                            continue;
                        }

                        $itemCost = isset($data['item_cost'][$index]) ? (float) $data['item_cost'][$index] : 0;
                        $itemQuantity = isset($data['item_quantity'][$index]) ? (int) $data['item_quantity'][$index] : 1;

                        $inventoryDetails[] = [
                            'item_name' => $name,
                            'item_condition' => isset($data['item_condition'][$index]) ? trim($data['item_condition'][$index]) : '',
                            'item_quantity' => $itemQuantity,
                            'item_cost' => $itemCost,
                        ];

                        $deductionAmount += $itemCost * $itemQuantity;

                        Log::info('Processed item ' . ($index + 1) . ':', [
                            'name' => $name,
                            'condition' => $data['item_condition'][$index] ?? '',
                            'quantity' => $itemQuantity,
                            'cost' => $itemCost,
                        ]);
                    }
                }

                // Tính số tiền hoàn trả cuối cùng
                $finalRefundedAmount = max(0, $depositAmount - $deductionAmount);

                // Xử lý hình ảnh
                $finalImages = $this->processImages($checkout, $data);

                // Chuẩn bị dữ liệu để cập nhật
                $updateData = [
                    'check_out_date' => $data['check_out_date'],
                    'has_left' => (int) $data['has_left'],
                    'inventory_status' => $data['status'],
                    'deduction_amount' => $deductionAmount,
                    'final_refunded_amount' => $finalRefundedAmount,
                    'images' => !empty($finalImages) ? $finalImages : $checkout->images,
                    'inventory_details' => !empty($inventoryDetails) ? $inventoryDetails : $checkout->inventory_details,
                    'updated_at' => now(),
                ];

                // Xử lý chuyển đổi user_confirmation_status
                $this->handleUserConfirmationStatusTransition($checkout, $data['status'], $updateData);

                // Kiểm tra và gửi thông báo nếu trạng thái thay đổi thành "Đã kiểm kê"
                $sendNotifications = ($data['status'] === 'Đã kiểm kê' && $checkout->inventory_status !== 'Đã kiểm kê');

                // Cập nhật checkout
                $updated = $checkout->update($updateData);

                if ($updated) {
                    Log::info('Checkout updated successfully', [
                        'checkout_id' => $checkout->id,
                        'inventory_status' => $data['status'],
                        'deposit_amount' => $depositAmount,
                        'deduction_amount' => $deductionAmount,
                        'final_refunded_amount' => $finalRefundedAmount,
                    ]);

                    if ($sendNotifications) {
                        $user = $checkout->contract->user;
                        $room = $checkout->contract->room;

                        if (!$user || !$room) {
                            Log::warning('User or room not found for checkout', [
                                'checkout_id' => $checkout->id,
                                'user_id' => $user ? $user->id : null,
                                'room_id' => $room ? $room->id : null,
                            ]);
                        } else {
                            // Gửi email
                            try {
                                $email = $user->email;
                                if ($email) {
                                    Mail::to($email)->send(new CheckoutStatusUpdated(
                                        $checkout,
                                        $user->name,
                                        $room->name,
                                        $data['check_out_date']
                                    ));
                                    Log::info('Checkout status email sent successfully', [
                                        'email' => $email,
                                        'checkout_id' => $checkout->id,
                                        'final_refunded_amount' => $finalRefundedAmount,
                                    ]);
                                } else {
                                    Log::warning('No email found for user', [
                                        'user_id' => $user->id,
                                        'checkout_id' => $checkout->id,
                                    ]);
                                }
                            } catch (\Exception $emailError) {
                                Log::error('Error sending checkout status email', [
                                    'error' => $emailError->getMessage(),
                                    'checkout_id' => $checkout->id,
                                ]);
                            }

                            // Tạo thông báo
                            try {
                                $notificationData = [
                                    'user_id' => $user->id,
                                    'title' => 'Trạng thái kiểm kê đã được cập nhật',
                                    'content' => 'Quá trình kiểm kê cho phòng ' . $room->name . ' đã hoàn tất. Số tiền hoàn trả: ' . number_format($finalRefundedAmount, 0, ',', '.') . ' VNĐ. Vui lòng xem chi tiết.',
                                    'status' => 'Chưa đọc',
                                ];
                                $notification = Notification::create($notificationData);
                                Log::info('Notification created for checkout', [
                                    'notification_id' => $notification->id,
                                    'checkout_id' => $checkout->id,
                                    'user_id' => $user->id,
                                ]);

                                // Gửi thông báo đẩy FCM
                                if ($user->fcm_token) {
                                    $messaging = app('firebase.messaging');
                                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                                        ->withNotification(FirebaseNotification::create(
                                            $notificationData['title'],
                                            $notificationData['content']
                                        ))
                                        ->withData(['url' => 'http://127.0.0.1:3000/quan-ly/kiem-ke']);

                                    $messaging->send($fcmMessage);
                                    Log::info('FCM sent to user', [
                                        'user_id' => $user->id,
                                        'checkout_id' => $checkout->id,
                                    ]);
                                } else {
                                    Log::info('No FCM token found for user', [
                                        'user_id' => $user->id,
                                        'checkout_id' => $checkout->id,
                                    ]);
                                }
                            } catch (\Exception $notificationError) {
                                Log::error('Error creating notification', [
                                    'error' => $notificationError->getMessage(),
                                    'checkout_id' => $checkout->id,
                                ]);
                            }
                        }
                    }
                } else {
                    Log::error('Failed to update checkout', ['checkout_id' => $checkout->id]);
                }

                return $checkout->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Error in updateCheckout method', [
                'checkout_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function handleUserConfirmationStatusTransition($checkout, $newStatus, &$updateData)
    {
        $currentStatus = $checkout->inventory_status;
        $currentUserConfirmationStatus = $checkout->user_confirmation_status;

        if (
            $currentStatus === 'Kiểm kê lại' &&
            $newStatus === 'Đã kiểm kê' &&
            $currentUserConfirmationStatus === 'Từ chối'
        ) {
            $updateData['user_confirmation_status'] = 'Chưa xác nhận';
            $updateData['user_rejection_reason'] = null;

            Log::info('User confirmation status changed:', [
                'from' => $currentUserConfirmationStatus,
                'to' => 'Chưa xác nhận',
                'user_rejection_reason' => 'cleared',
                'reason' => 'Status transition from "Kiểm kê lại" to "Đã kiểm kê"',
                'checkout_id' => $checkout->id,
            ]);
        }
    }

    private function processImages($checkout, $data)
    {
        $existingImages = $checkout->images ?? [];
        $imagesToDelete = $data['deleted_images'] ?? [];
        $existingImagesKept = $data['existing_images'] ?? [];

        Log::info('Processing images:', [
            'existing' => $existingImages,
            'to_delete' => $imagesToDelete,
            'kept' => $existingImagesKept,
            'checkout_id' => $checkout->id,
        ]);

        foreach ($imagesToDelete as $imageToDelete) {
            if (($key = array_search($imageToDelete, $existingImages)) !== false) {
                unset($existingImages[$key]);
                if (Storage::disk('public')->exists($imageToDelete)) {
                    Storage::disk('public')->delete($imageToDelete);
                }
                Log::info('Deleted image: ' . $imageToDelete, ['checkout_id' => $checkout->id]);
            }
        }

        $finalImages = array_values(array_intersect($existingImages, $existingImagesKept));

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                    $extension = $image->getClientOriginalExtension();
                    $fileName = 'checkout-' . time() . '-' . uniqid() . '.' . $extension;
                    $path = $image->storeAs('checkout_images', $fileName, 'public');
                    $finalImages[] = $path;
                    Log::info('Uploaded new image: ' . $path, ['checkout_id' => $checkout->id]);
                }
            }
        }

        return $finalImages;
    }

    public function reInventoryCheckout($id)
    {
        try {
            $checkout = Checkout::findOrFail($id);

            if ($checkout->inventory_status !== 'Đã kiểm kê') {
                throw new \Exception('Chỉ có thể kiểm kê lại các checkout đã hoàn thành.');
            }

            $updated = $checkout->update([
                'inventory_status' => 'Kiểm kê lại',
                'updated_at' => now(),
            ]);

            if ($updated) {
                Log::info('Successfully changed status to "Kiểm kê lại"', ['checkout_id' => $id]);
            } else {
                Log::error('Failed to update status to "Kiểm kê lại"', ['checkout_id' => $id]);
            }

            return $checkout->fresh();
        } catch (\Exception $e) {
            Log::error('Re-inventory error', [
                'checkout_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function confirmCheckout($id, $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $checkout = Checkout::with(['contract.room', 'contract.user', 'contract.invoices'])->findOrFail($id);

                // Kiểm tra điều kiện hợp lệ
                if ($checkout->inventory_status !== 'Đã kiểm kê' || $checkout->user_confirmation_status !== 'Đồng ý') {
                    throw new \Exception('Yêu cầu không hợp lệ để xác nhận hoàn tiền.');
                }

                // Cập nhật trạng thái thành Đã xử lý
                $checkout->refund_status = 'Đã xử lý';
                $checkout->save();

                // Lấy invoice_id của hóa đơn có type là "đặt cọc"
                $depositInvoice = $checkout->contract->invoices()
                    ->where('type', 'đặt cọc')
                    ->where('status', 'Đã trả') // Giả sử hóa đơn đặt cọc đã hoàn tất
                    ->first();

                if (!$depositInvoice) {
                    throw new \Exception('Không tìm thấy hóa đơn đặt cọc cho hợp đồng này.');
                }

                $invoiceId = $depositInvoice->id;

                Invoice::where('id', $invoiceId)->update([
                    'refunded_at' => now()
                ]);

                // Tạo giao dịch
                Transaction::create([
                    'invoice_id' => $invoiceId,
                    'reference_code' => $request->input('reference_code'),
                    'transfer_amount' => $checkout->final_refunded_amount,
                    'content' => 'Hoàn tiền cho phòng ' . ($checkout->contract->room->name ?? 'N/A'),
                    'transfer_type' => 'out',
                    'transaction_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Gửi thông báo
                $user = $checkout->contract->user;
                $room = $checkout->contract->room;

                if ($user && $room) {
                    // Gửi email
                    try {
                        $email = $user->email;
                        if ($email) {
                            Mail::to($email)->send(new CheckoutStatusUpdated(
                                $checkout,
                                $user->name,
                                $room->name,
                                $checkout->check_out_date
                            ));
                            Log::info('Checkout confirmation email sent successfully', [
                                'email' => $email,
                                'checkout_id' => $checkout->id,
                                'final_refunded_amount' => $checkout->final_refunded_amount,
                            ]);
                        }
                    } catch (\Exception $emailError) {
                        Log::error('Error sending checkout confirmation email', [
                            'error' => $emailError->getMessage(),
                            'checkout_id' => $checkout->id,
                        ]);
                    }

                    // Tạo thông báo trong cơ sở dữ liệu
                    try {
                        $notificationData = [
                            'user_id' => $user->id,
                            'title' => 'Xác nhận hoàn tiền thành công',
                            'content' => 'Hoàn tiền cho phòng ' . $room->name . ' đã được xử lý. Số tiền: ' . number_format($checkout->final_refunded_amount, 0, ',', '.') . ' VNĐ. Mã tham chiếu: ' . $request->input('reference_code'),
                            'status' => 'Chưa đọc',
                        ];
                        $notification = Notification::create($notificationData);
                        Log::info('Notification created for checkout confirmation', [
                            'notification_id' => $notification->id,
                            'checkout_id' => $checkout->id,
                            'user_id' => $user->id,
                        ]);

                        // Gửi thông báo đẩy FCM
                        if ($user->fcm_token) {
                            $messaging = app('firebase.messaging');
                            $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                                ->withNotification(FirebaseNotification::create(
                                    $notificationData['title'],
                                    $notificationData['content']
                                ))
                                ->withData(['url' => 'http://127.0.0.1:3000/quan-ly/kiem-ke']);

                            $messaging->send($fcmMessage);
                            Log::info('FCM sent to user for confirmation', [
                                'user_id' => $user->id,
                                'checkout_id' => $checkout->id,
                            ]);
                        }
                    } catch (\Exception $notificationError) {
                        Log::error('Error creating notification for confirmation', [
                            'error' => $notificationError->getMessage(),
                            'checkout_id' => $checkout->id,
                        ]);
                    }
                }

                Log::info('Checkout confirmed successfully', [
                    'checkout_id' => $checkout->id,
                    'reference_code' => $request->input('reference_code'),
                    'transfer_amount' => $request->input('transfer_amount'),
                    'invoice_id' => $invoiceId,
                ]);

                return $checkout->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Error in confirmCheckout method', [
                'checkout_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
