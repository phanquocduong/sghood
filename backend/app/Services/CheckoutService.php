<?php

namespace App\Services;

use App\Models\Checkout;
use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use App\Models\ContractTenant;
use App\Jobs\SendCheckoutStatusUpdatedNotification;
use App\Jobs\SendCheckoutRefundNotification;
use App\Jobs\SendCheckoutAutoConfirmedNotification;
use App\Jobs\SendAutoEndContractNotification;
use App\Jobs\SendContractTenantStatusNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;

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
            $query->whereHas('contract', function ($q) use ($querySearch) {
                if (strtolower($querySearch) === 'hd') {
                    return;
                }

                $numericQuery = $querySearch;
                if (preg_match('/^hd(\d+)$/i', $querySearch, $matches)) {
                    $numericQuery = $matches[1];
                }

                $q->where('id', 'like', '%' . $querySearch . '%')
                    ->orWhere('id', 'like', '%' . $numericQuery . '%')
                    ->orWhereHas('user', function ($userQuery) use ($querySearch) {
                        $userQuery->where('name', 'like', '%' . $querySearch . '%');
                    });
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 10;
        return $query->paginate($perPage);
    }

    public function getCheckoutsByStatus()
    {
        return Checkout::with(['contract.room', 'contract.user'])
            ->where('inventory_status', 'Chờ kiểm kê')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
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

                $finalRefundedAmount = max(0, $depositAmount - $deductionAmount);

                $finalImages = $this->processImages($checkout, $data);

                $updateData = [
                    'inventory_status' => $data['status'],
                    'deduction_amount' => $deductionAmount,
                    'final_refunded_amount' => $finalRefundedAmount,
                    'images' => !empty($finalImages) ? $finalImages : $checkout->images,
                    'inventory_details' => !empty($inventoryDetails) ? $inventoryDetails : $checkout->inventory_details,
                    'updated_at' => now(),
                ];

                $this->handleUserConfirmationStatusTransition($checkout, $data['status'], $updateData);

                $sendNotifications = ($data['status'] === 'Đã kiểm kê' && $checkout->inventory_status !== 'Đã kiểm kê');

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
                            SendCheckoutStatusUpdatedNotification::dispatch(
                                $checkout,
                                $user,
                                $room,
                                $data = $checkout->check_out_date,
                            );

                            Log::info('Checkout notification job dispatched', [
                                'checkout_id' => $checkout->id,
                                'user_id' => $user->id,
                            ]);
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
                    $uploadedPath = $this->uploadCheckoutImage($image);
                    if ($uploadedPath !== false) {
                        $finalImages[] = $uploadedPath;
                        Log::info('Uploaded new image: ' . $uploadedPath, ['checkout_id' => $checkout->id]);
                    }
                }
            }
        }

        return $finalImages;
    }

    private function uploadCheckoutImage(\Illuminate\Http\UploadedFile $imageFile): string|false
    {
        try {
            $manager = new \Intervention\Image\ImageManager(new Driver());
            $filename = 'images/checkouts/checkout-' . time() . '-' . uniqid() . '.webp';

            $image = $manager->read($imageFile)->toWebp(quality: 85)->toString();

            Storage::disk('public')->put($filename, $image);

            return '/storage/' . $filename;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
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

                if ($checkout->inventory_status !== 'Đã kiểm kê' || $checkout->user_confirmation_status !== 'Đồng ý') {
                    throw new \Exception('Yêu cầu không hợp lệ để xác nhận hoàn tiền.');
                }

                $refundedAt = now();
                $endDate = $checkout->contract->end_date ? \Carbon\Carbon::parse($checkout->contract->end_date) : null;

                $checkout->refund_status = 'Đã xử lý';
                $checkout->save();

                $depositInvoice = $checkout->contract->invoices()
                    ->where('type', 'đặt cọc')
                    ->where('status', 'Đã trả')
                    ->first();

                if (!$depositInvoice) {
                    throw new \Exception('Không tìm thấy hóa đơn đặt cọc cho hợp đồng này.');
                }

                $invoiceId = $depositInvoice->id;

                Invoice::where('id', $invoiceId)->update([
                    'refunded_at' => $refundedAt
                ]);

                Transaction::create([
                    'invoice_id' => $invoiceId,
                    'reference_code' => $request->input('reference_code'),
                    'transfer_amount' => $checkout->final_refunded_amount,
                    'content' => 'Hoàn tiền cho phòng ' . ($checkout->contract->room->name ?? 'N/A'),
                    'transfer_type' => 'out',
                    'transaction_date' => $refundedAt,
                    'created_at' => $refundedAt,
                    'updated_at' => $refundedAt,
                ]);

                if ($endDate && $refundedAt->greaterThan($endDate)) {
                    Contract::where('id', $checkout->contract->id)->update([
                        'status' => 'Kết thúc',
                        'updated_at' => $refundedAt,
                    ]);

                    Room::where('id', $checkout->contract->room_id)->update([
                        'status' => 'Sửa chữa',
                        'updated_at' => $refundedAt,
                    ]);

                    $user = $checkout->contract->user;
                    if ($user) {
                        if ($user->identity_document && Storage::disk('private')->exists($user->identity_document)) {
                            Storage::disk('private')->delete($user->identity_document);
                            Log::info('Identity document deleted', [
                                'user_id' => $user->id,
                                'document_path' => $user->identity_document,
                            ]);
                        }

                        User::where('id', $user->id)->update([
                            'identity_document' => null,
                            'updated_at' => $refundedAt,
                        ]);

                        Log::info('User role updated to Người đăng ký and identity_document cleared', [
                            'user_id' => $user->id,
                            'checkout_id' => $checkout->id,
                            'contract_id' => $checkout->contract->id,
                        ]);
                    } else {
                        Log::warning('User not found for role update', [
                            'checkout_id' => $checkout->id,
                            'contract_id' => $checkout->contract->id,
                        ]);
                    }

                    $room = $checkout->contract->room;
                    if ($user && $room) {
                    SendAutoEndContractNotification::dispatch(
                        $checkout->contract,
                        'Hợp đồng kết thúc sớm do hoàn tất quy trình checkout.'
                    );

                    Log::info('Contract early termination notification job dispatched', [
                        'checkout_id' => $checkout->id,
                        'user_id' => $user->id,
                        'contract_id' => $checkout->contract->id,
                    ]);
                    }

                    Log::info('Contract status updated to Kết thúc', [
                        'contract_id' => $checkout->contract->id,
                        'checkout_id' => $checkout->id,
                        'refunded_at' => $refundedAt->toDateTimeString(),
                        'end_date' => $endDate->toDateTimeString(),
                    ]);
                }

                $user = $checkout->contract->user;
                $room = $checkout->contract->room;

                if ($user && $room) {
                    SendCheckoutRefundNotification::dispatch(
                        $checkout,
                        $user,
                        $room,
                        $checkout->check_out_date,
                        $request->input('reference_code')
                    );

                    Log::info('Checkout refund notification job dispatched', [
                        'checkout_id' => $checkout->id,
                        'user_id' => $user->id,
                        'reference_code' => $request->input('reference_code'),
                    ]);
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

    public function forceConfirmUserStatus($id)
    {
        try {
            $checkout = Checkout::with(['contract.room', 'contract.user'])->findOrFail($id);

            if ($checkout->inventory_status !== 'Đã kiểm kê' || $checkout->user_confirmation_status !== 'Chưa xác nhận') {
                throw new \Exception('Yêu cầu không hợp lệ để xác nhận thay người dùng.');
            }

            $updated = $checkout->update([
                'user_confirmation_status' => 'Đồng ý',
                'updated_at' => now(),
            ]);

            if ($updated) {
                Log::info('User confirmation status forced to "Đồng ý"', ['checkout_id' => $id]);

                $user = $checkout->contract->user;
                $room = $checkout->contract->room;

                if ($user && $room) {
                    SendCheckoutAutoConfirmedNotification::dispatch($checkout, $user, $room);

                    Log::info('Checkout auto-confirmation notification job dispatched', [
                        'checkout_id' => $checkout->id,
                        'user_id' => $user->id,
                    ]);
                } else {
                    Log::warning('User or room not found for auto-confirmation notification', [
                        'checkout_id' => $checkout->id,
                        'user_id' => $user ? $user->id : null,
                        'room_id' => $room ? $room->id : null,
                    ]);
                }
            } else {
                Log::error('Failed to force confirm user status', ['checkout_id' => $id]);
            }

            return $checkout->fresh();
        } catch (\Exception $e) {
            Log::error('Error in forceConfirmUserStatus method', [
                'checkout_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function confirmLeftCheckout($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $checkout = Checkout::with(['contract.room', 'contract.user'])->findOrFail($id);

                if ($checkout->inventory_status !== 'Đã kiểm kê') {
                    throw new \Exception('Chỉ có thể xác nhận người dùng đã rời khỏi phòng khi checkout đã được kiểm kê.');
                }

                $updated = $checkout->update([
                    'has_left' => 1,
                    'updated_at' => now(),
                ]);

                if ($updated) {
                    Log::info('Checkout confirmed as left', ['checkout_id' => $id]);

                    // Cập nhật trạng thái của tất cả ContractTenant liên quan đến contract
                    $contractTenants = ContractTenant::where('contract_id', $checkout->contract_id)->get();

                    foreach ($contractTenants as $tenant) {
                        if ($tenant->status !== 'Đã rời đi') {
                            $oldStatus = $tenant->status;
                            $tenant->update([
                                'status' => 'Đã rời đi',
                                'rejection_reason' => null,
                                'updated_at' => now(),
                            ]);

                            Log::info('Contract tenant status updated to Đã rời đi', [
                                'contract_tenant_id' => $tenant->id,
                                'contract_id' => $checkout->contract_id,
                                'old_status' => $oldStatus,
                            ]);

                            // // Xóa identity_document của tenant
                            if ($tenant->identity_document && Storage::disk('private')->exists($tenant->identity_document)) {
                                $imagePaths = explode('|', $tenant->identity_document);
                                foreach ($imagePaths as $path) {
                                    Storage::disk('private')->delete($path);
                                }
                                Log::info('Deleted tenant identity document', [
                                    'contract_tenant_id' => $tenant->id,
                                    'deleted_files' => $imagePaths,
                                ]);
                            }

                            $tenant->update(['identity_document' => null]);

                            // Gửi thông báo trạng thái cho tenant
                            SendContractTenantStatusNotification::dispatch($tenant, 'Đã rời đi', null);

                            Log::info('Contract tenant status notification job dispatched', [
                                'contract_tenant_id' => $tenant->id,
                                'contract_id' => $checkout->contract_id,
                                'new_status' => 'Đã rời đi',
                            ]);
                        }
                    }
                } else {
                    Log::error('Failed to confirm left checkout', ['checkout_id' => $id]);
                }

                return $checkout->fresh();
            });
        } catch (\Exception $e) {
            Log::error('Error in confirmLeftCheckout method', [
                'checkout_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
