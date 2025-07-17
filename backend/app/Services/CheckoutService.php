<?php

namespace App\Services;

use App\Models\Checkout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $checkout = Checkout::findOrFail($id);

        // Log dữ liệu đầu vào để debug
        Log::info('Incoming data in CheckoutService:', $data);

        // Xử lý inventory details
        $inventoryDetails = [];
        $deductionAmount = 0;

        // Kiểm tra nếu có dữ liệu item_name được gửi
        if (isset($data['item_name']) && is_array($data['item_name'])) {
            foreach ($data['item_name'] as $index => $name) {
                $name = trim($name);
                // Bỏ qua nếu tên mục rỗng
                if ($name === '') {
                    Log::warning('Skipped empty item name at index:', ['index' => $index]);
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

                Log::info('Processed item:', [
                    'index' => $index,
                    'name' => $name,
                    'cost' => $itemCost,
                    'quantity' => $itemQuantity,
                ]);
            }
        } else {
            Log::warning('No item_name array provided in data:', $data);
        }

        // Xử lý hình ảnh
        $existingImages = $checkout->images ?? [];
        $imagesToDelete = $data['images_to_delete'] ?? [];
        $existingImagesKept = $data['existing_images'] ?? [];

        // Xóa hình ảnh được yêu cầu
        foreach ($imagesToDelete as $imageToDelete) {
            if (($key = array_search($imageToDelete, $existingImages)) !== false) {
                unset($existingImages[$key]);
                if (Storage::disk('public')->exists($imageToDelete)) {
                    Storage::disk('public')->delete($imageToDelete);
                }
                Log::info('Deleted image:', ['image' => $imageToDelete]);
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
                    Log::info('Uploaded new image:', ['path' => $path]);
                }
            }
        }

        // Chuẩn bị dữ liệu để cập nhật
        $updateData = [
            'check_out_date' => $data['check_out_date'],
            'has_left' => $data['has_left'],
            'inventory_status' => $data['status'],
            'deduction_amount' => $deductionAmount,
            'images' => !empty($finalImages) ? $finalImages : null,
            'inventory_details' => !empty($inventoryDetails) ? $inventoryDetails : null,
        ];

        // Cập nhật checkout
        $checkout->update($updateData);

        Log::info('Updated checkout:', $updateData);

        return $checkout;
    }
}
