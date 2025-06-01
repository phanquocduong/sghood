<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            return ['error' => 'Đã xảy ra lỗi khi tạo cấu hình!', 'status' => 500];
        }
    }

    public function getConfigById($id)
    {
        return Config::findOrFail($id);
    }

    public function updateConfig($id, array $data)
    {
        try {
            $config = Config::findOrFail($id);
            $config->update($data);
            return ['data' => $config];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cập nhật cấu hình: ' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi cập nhật cấu hình!', 'status' => 500];
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