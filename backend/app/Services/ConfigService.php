<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConfigService
{
    public function getConfigs($search)
    {
        return Config::when($search, function ($query, $search) {
            return $query->where('config_key', 'like', "%{$search}%")
                ->orWhere('config_value', 'like', "%{$search}%");
        })->paginate(10);
    }

    public function createConfig(array $data)
    {
        try {
            $config = Config::create($data);
            return ['data' => $config];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tạo cấu hình: ' . $e->getMessage());
            return ['error' => `Đã xảy ra lỗi khi tạo cấu hình! `, 'status' => 500];
        }
    }

    public function storeConfig(array $data, $imageFile = null): array
    {
        try {
            // Nếu là kiểu IMAGE và có file ảnh
            if ($data['config_type'] === 'IMAGE' && $imageFile && $imageFile->isValid()) {
                $fileName = time() . '_' . $imageFile->getClientOriginalName();
                $path = $imageFile->storeAs('config_images', $fileName, 'public');
                $data['config_value'] = 'storage/' . $path;
            }

            // Tạo bản ghi trong database
            $config = Config::create($data);

            return ['data' => $config];
        } catch (\Exception $e) {
            Log::error('Lỗi lưu cấu hình: ' . $e->getMessage());
           return ['error' => 'Đã xảy ra lỗi khi lưu cấu hình.', 'status' => 500];
        }
    }

    public function getConfigById($id)
    {
        return Config::findOrFail($id);
    }

    public function updateConfig($id, array $data, $imageFile = null): array
    {
        try {
            $config = Config::findOrFail($id);

            if ($data['config_type'] === 'IMAGE' && $imageFile && $imageFile->isValid()) {
                // Delete old image if exists
                if ($config->config_type === 'IMAGE' && $config->config_value && Storage::disk('public')->exists(str_replace('storage/', '', $config->config_value))) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $config->config_value));
                }

                $fileName = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $imageFile->getClientOriginalExtension();
                $path = $imageFile->storeAs('config_images', $fileName, 'public');
                $data['config_value'] = 'storage/' . $path;
            } elseif ($data['config_type'] !== 'IMAGE') {
                // Use provided config_value for non-IMAGE types
                $data['config_value'] = $data['config_value'] ?? $config->config_value;
            } else {
                // Keep existing config_value if no new image is uploaded
                $data['config_value'] = $config->config_value;
            }

            $config->update($data);
            return ['data' => $config];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cập nhật cấu hình: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật cấu hình: ' . $e->getMessage(), 'status' => 500];
        }
    }

    public function deleteConfig($id)
    {
        try {
            $config = Config::findOrFail($id);
            $config->delete();
            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi xóa cấu hình: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa cấu hình!', 'status' => 500];
        }
    }

    public function getTrashedConfigs($search)
    {
        return Config::onlyTrashed()
            ->when($search, function ($query, $search) {
                return $query->where('config_key', 'like', "%{$search}%")
                    ->orWhere('config_value', 'like', "%{$search}%");
            })->paginate(10);
    }

    public function restoreConfig($id)
    {
        DB::beginTransaction();
        try {
            $config = Config::onlyTrashed()->findOrFail($id);
            $config->restore();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi khôi phục cấu hình: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi khôi phục cấu hình!', 'status' => 500];
        }
    }

    public function forceDeleteConfig($id)
    {
        DB::beginTransaction();
        try {
            $config = Config::onlyTrashed()->findOrFail($id);
            $config->forceDelete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa vĩnh viễn cấu hình: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa vĩnh viễn cấu hình!', 'status' => 500];
        }
    }
}
