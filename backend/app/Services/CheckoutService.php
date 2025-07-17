<?php

namespace App\Services;

use App\Models\Checkout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        return Checkout::with(['contract.room'])->findOrFail($id);
    }

    public function updateCheckout($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $checkout = Checkout::findOrFail($id);

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

            // Xử lý hình ảnh
            $finalImages = $this->processImages($checkout, $data);

            // Chuẩn bị dữ liệu để cập nhật
            $updateData = [
                'check_out_date' => $data['check_out_date'],
                'has_left' => (int) $data['has_left'],
                'inventory_status' => $data['status'],
                'deduction_amount' => $deductionAmount,
                'images' => !empty($finalImages) ? $finalImages : null,
                'inventory_details' => !empty($inventoryDetails) ? $inventoryDetails : null,
                'updated_at' => now(),
            ];

            // Xử lý logic chuyển đổi user_confirmation_status
            $this->handleUserConfirmationStatusTransition($checkout, $data['status'], $updateData);


            // Cập nhật checkout
            $updated = $checkout->update($updateData);

            if ($updated) {
                Log::info('Checkout updated successfully');
                Log::info('Final checkout data:', $checkout->fresh()->toArray());
            } else {
                Log::error('Failed to update checkout');
            }

            Log::info('=== CHECKOUT UPDATE END ===');

            return $checkout->fresh();
        });
    }

    /**
     * Xử lý logic chuyển đổi user_confirmation_status
     */
    private function handleUserConfirmationStatusTransition($checkout, $newStatus, &$updateData)
    {
        $currentStatus = $checkout->inventory_status;
        $currentUserConfirmationStatus = $checkout->user_confirmation_status;


        if ($currentStatus === 'Kiểm kê lại' &&
            $newStatus === 'Đã kiểm kê' &&
            $currentUserConfirmationStatus === 'Từ chối') {

            $updateData['user_confirmation_status'] = 'Chưa xác nhận';

            Log::info('User confirmation status changed:', [
                'from' => $currentUserConfirmationStatus,
                'to' => 'Chờ xác nhận',
                'reason' => 'Status transition from "Kiểm kê lại" to "Đã kiểm kê"'
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
        ]);

        // Xóa hình ảnh được yêu cầu
        foreach ($imagesToDelete as $imageToDelete) {
            if (($key = array_search($imageToDelete, $existingImages)) !== false) {
                unset($existingImages[$key]);
                if (Storage::disk('public')->exists($imageToDelete)) {
                    Storage::disk('public')->delete($imageToDelete);
                }
                Log::info('Deleted image: ' . $imageToDelete);
            }
        }

        $finalImages = array_values(array_intersect($existingImages, $existingImagesKept));

        // Thêm hình ảnh mới
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                if ($image instanceof \Illuminate\Http\UploadedFile && $image->isValid()) {
                    $extension = $image->getClientOriginalExtension();
                    $fileName = 'checkout-' . time() . '-' . uniqid() . '.' . $extension;
                    $path = $image->storeAs('checkout_images', $fileName, 'public');
                    $finalImages[] = $path;
                    Log::info('Uploaded new image: ' . $path);
                }
            }
        }

        return $finalImages;
    }

    public function reInventoryCheckout($id)
    {
        try {
            $checkout = Checkout::findOrFail($id);

            Log::info('Re-inventory request for checkout: ' . $id);
            Log::info('Current status: ' . $checkout->inventory_status);

            if ($checkout->inventory_status !== 'Đã kiểm kê') {
                throw new \Exception('Chỉ có thể kiểm kê lại các checkout đã hoàn thành.');
            }

            $updated = $checkout->update([
                'inventory_status' => 'Kiểm kê lại',
                'updated_at' => now(),
            ]);

            if ($updated) {
                Log::info('Successfully changed status to "Kiểm kê lại"');
            } else {
                Log::error('Failed to update status to "Kiểm kê lại"');
            }

            return $checkout->fresh();
        } catch (\Exception $e) {
            Log::error('Re-inventory error: ' . $e->getMessage());
            throw $e;
        }
    }
}
