<?php

namespace App\Services;

use App\Models\Checkout;
use Illuminate\Support\Facades\Storage;


class CheckoutService
{
    public function getCheckouts($filters = [])
    {
        $query = Checkout::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('id', 'desc');

        return $query->paginate(10);
    }

    public function getCheckoutById($id)
    {
        return Checkout::findOrFail($id);
    }

    public function updateCheckout($id, array $data)
    {
        $checkout = Checkout::findOrFail($id);
        $inventoryDetails = json_decode($data['inventory_details'], true) ?: [];

        // Delete old images that will be replaced
        if ($checkout->inventory_details) {
            foreach ($checkout->inventory_details as $oldIndex => $item) {
                if (isset($item['type']) && $item['type'] === 'IMAGE' && isset($item['value']) && $item['value']) {
                    // Check if this item is being replaced with a new image
                    $imageFiles = $data['inventory_value_image'] ?? [];
                    if (isset($imageFiles[$oldIndex]) && $imageFiles[$oldIndex] instanceof \Illuminate\Http\UploadedFile) {
                        // Delete old image as it's being replaced
                        $oldPath = str_replace('/storage/', '', $item['value']);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
            }
        }

        // Process new inventory details with file uploads
        $updatedDetails = [];
        $imageFiles = $data['inventory_value_image'] ?? [];
        $textValues = $data['inventory_value_text'] ?? [];
        $existingImageValues = $data['existing_image_value'] ?? [];

        foreach ($inventoryDetails as $index => $item) {
            if (!isset($item['type'])) continue;

            $newItem = ['type' => $item['type']];

            if ($item['type'] === 'TEXT') {
                $textValue = isset($textValues[$index]) ? trim($textValues[$index]) : (isset($item['value']) ? trim($item['value']) : '');
                $newItem['value'] = $textValue;
            } elseif ($item['type'] === 'IMAGE') {
                // Check if new file is uploaded for this index
                if (isset($imageFiles[$index]) && $imageFiles[$index] instanceof \Illuminate\Http\UploadedFile) {
                    // Generate unique filename
                    $file = $imageFiles[$index];
                    $extension = $file->getClientOriginalExtension();
                    $fileName = 'checkout-' . time() . '-' . uniqid() . '.' . $extension;

                    // Store file in inventory_images directory
                    $path = $file->storeAs('inventory_images', $fileName, 'public');
                    $newItem['value'] = '/storage/' . $path;
                } elseif (isset($existingImageValues[$index]) && $existingImageValues[$index] !== '') {
                    // Keep existing image path from hidden input
                    $newItem['value'] = $existingImageValues[$index];
                } elseif (isset($item['value']) && $item['value'] !== '' && $item['value'] !== 'pending_upload') {
                    // Fallback to keep existing image path
                    $newItem['value'] = $item['value'];
                } else {
                    // No file provided - keep empty for now
                    $newItem['value'] = '';
                }
            }

            $updatedDetails[] = $newItem;
        }

        $checkout->update([
            'check_out_date' => $data['check_out_date'],
            'has_left' => $data['has_left'],
            'status' => $data['status'],
            'deduction_amount' => $data['deduction_amount'] ?? $checkout->deduction_amount,
            'inventory_details' => $updatedDetails,
        ]);

        return $checkout;
    }
}
