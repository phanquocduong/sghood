<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ConfigService
{
    /**
    * Lấy danh sách cấu hình có phân trang với tùy chọn tìm kiếm.
     */
    public function getConfigs(?string $search = null, int $perPage = 10)
    {
        return Config::when($search, function ($query, $search) {
            return $query->where('config_key', 'like', "%{$search}%")
                ->orWhere('config_value', 'like', "%{$search}%");
        })->paginate($perPage);
    }

    /**
    * Lấy danh sách cấu hình đã xóa có phân trang với tùy chọn tìm kiếm.
     */
    public function getTrashedConfigs(?string $search = null, int $perPage = 10)
    {
        return Config::onlyTrashed()
            ->when($search, function ($query, $search) {
                return $query->where('config_key', 'like', "%{$search}%")
                    ->orWhere('config_value', 'like', "%{$search}%");
            })->paginate($perPage);
    }

    /**
    * Lấy cấu hình theo ID.
     */
    public function getConfigById(int $id): Config
    {
        return Config::findOrFail($id);
    }

    /**
    * Tạo một cấu hình mới.
     */
    public function createConfig(array $data, ?UploadedFile $imageFile = null): array
    {
        return $this->storeOrUpdateConfig($data, $imageFile);
    }

    /**
    * Cập nhật một cấu hình hiện có.
     */
    public function updateConfig(int $id, array $data, ?UploadedFile $imageFile = null): array
    {
        return $this->storeOrUpdateConfig($data, $imageFile, $id);
    }

    /**
    * Lưu hoặc cập nhật cấu hình với xử lý hình ảnh.
     */
    protected function storeOrUpdateConfig(array $data, ?UploadedFile $imageFile = null, ?int $id = null): array
    {
        try {
            DB::beginTransaction();

            $config = $id ? Config::findOrFail($id) : new Config();

            if ($data['config_type'] === 'IMAGE' && $imageFile && $imageFile->isValid()) {
                // Delete old image if updating
                if ($id && $config->config_type === 'IMAGE' && $config->config_value) {
                    $this->deleteImage($config->config_value);
                }

                // Convert and store image as WebP
                $data['config_value'] = $this->storeImageAsWebp($imageFile);
            } elseif ($data['config_type'] !== 'IMAGE') {
                $data['config_value'] = $data['config_value'] ?? ($config->config_value ?? null);
            } else {
                $data['config_value'] = $config->config_value ?? null;
            }

            $id ? $config->update($data) : $config = Config::create($data);

            DB::commit();
            return ['data' => $config];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi lưu/cập nhật cấu hình: ' . $e->getMessage(), ['id' => $id]);
            return ['error' => 'Đã xảy ra lỗi khi lưu/cập nhật cấu hình.', 'status' => 500];
        }
    }

    /**
    * Chuyển đổi và lưu trữ hình ảnh tải lên dưới định dạng WebP.
     */
    protected function storeImageAsWebp(UploadedFile $imageFile): string
    {
        $fileName = time() . '_' . Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.webp';
        $tempPath = $imageFile->getPathname();

        // Convert image to WebP using GD
        $image = $this->createImageResource($imageFile);
        $webpPath = storage_path('app/public/images/configs/' . $fileName);
        imagewebp($image, $webpPath, 80); // Quality set to 80
        imagedestroy($image);

        return '/storage/images/configs/' . $fileName;
    }

    /**
    * Tạo tài nguyên hình ảnh dựa trên loại tệp.
     */
    protected function createImageResource(UploadedFile $imageFile)
    {
        $extension = strtolower($imageFile->getClientOriginalExtension());
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                return imagecreatefromjpeg($imageFile->getPathname());
            case 'png':
                return imagecreatefrompng($imageFile->getPathname());
            case 'gif':
                return imagecreatefromgif($imageFile->getPathname());
            default:
                throw new \Exception('Định dạng ảnh không được hỗ trợ.');
        }
    }

    /**
    * Xóa hình ảnh khỏi storage.
     */
    protected function deleteImage(string $imagePath): void
    {
        $path = str_replace('/storage/', '', $imagePath);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
    * Xóa mềm cấu hình.
     */
    public function deleteConfig(int $id): array
    {
        try {
            $config = Config::findOrFail($id);
            $config->delete();
            return ['success' => true];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi xóa cấu hình: ' . $e->getMessage(), ['id' => $id]);
            return ['error' => 'Đã xảy ra lỗi khi xóa cấu hình.', 'status' => 500];
        }
    }

    /**
     * Khôi phục cấu hình đã xóa mềm.
     */
    public function restoreConfig(int $id): array
    {
        try {
            DB::beginTransaction();
            $config = Config::onlyTrashed()->findOrFail($id);
            $config->restore();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi khôi phục cấu hình: ' . $e->getMessage(), ['id' => $id]);
            return ['error' => 'Đã xảy ra lỗi khi khôi phục cấu hình.', 'status' => 500];
        }
    }

    /**
     * Xóa vĩnh viễn cấu hình đã xóa mềm.
     */
    public function forceDeleteConfig(int $id): array
    {
        try {
            DB::beginTransaction();
            $config = Config::onlyTrashed()->findOrFail($id);
            if ($config->config_type === 'IMAGE' && $config->config_value) {
                $this->deleteImage($config->config_value);
            }
            $config->forceDelete();
            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa vĩnh viễn cấu hình: ' . $e->getMessage(), ['id' => $id]);
            return ['error' => 'Đã xảy ra lỗi khi xóa vĩnh viễn cấu hình.', 'status' => 500];
        }
    }
}