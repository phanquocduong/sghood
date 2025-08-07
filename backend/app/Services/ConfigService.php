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
                ->orWhere('config_value', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
    public function createConfig(array $data, ?UploadedFile $imageFile = null, $jsonData = null): array
    {
        return $this->storeOrUpdateConfig($data, $imageFile, null, $jsonData);
    }

    /**
     * Cập nhật một cấu hình hiện có.
     */
    public function updateConfig(int $id, array $data, ?UploadedFile $imageFile = null, $jsonData = null): array
    {
        return $this->storeOrUpdateConfig($data, $imageFile, $id, $jsonData);
    }

    /**
     * Lưu hoặc cập nhật cấu hình với xử lý hình ảnh.
     */
    protected function storeOrUpdateConfig(array $data, ?UploadedFile $imageFile = null, ?int $id = null, ?array $jsonData = null): array
    {
        try {
            DB::beginTransaction();

            $config = $id ? Config::findOrFail($id) : new Config();

            // Xử lý IMAGE
            if ($data['config_type'] === 'IMAGE' && $imageFile && $imageFile->isValid()) {
                // Xóa ảnh cũ nếu đang cập nhật
                if ($id && $config->config_type === 'IMAGE' && $config->config_value) {
                    $this->deleteImage($config->config_value);
                }
                $data['config_value'] = $this->storeImageAsWebp($imageFile);
            }
            // Xử lý JSON (OPTION)
            elseif ($data['config_type'] === 'JSON' && $jsonData) {
                $data['config_value'] = $this->processJsonData($jsonData);
            }
            // Xử lý BANK
            elseif ($data['config_type'] === 'BANK' && $jsonData) {
                $data['config_value'] = $this->processBankData($jsonData);
            }
            // Xử lý OBJECT - THÊM PHẦN NÀY
            elseif ($data['config_type'] === 'OBJECT' && $jsonData) {
                $data['config_value'] = $this->processObjectData($jsonData);
            }
            // Xử lý TEXT, URL, HTML
            elseif (in_array($data['config_type'], ['TEXT', 'URL', 'HTML'])) {
                if (empty($data['config_value'])) {
                    throw new \Exception('Nội dung không được để trống cho loại ' . $data['config_type']);
                }
                // Giữ nguyên config_value đã được validate
            }
            // Trường hợp đặc biệt: cập nhật IMAGE mà không có file mới
            elseif ($data['config_type'] === 'IMAGE' && $id && $config->config_type === 'IMAGE') {
                // Giữ nguyên ảnh cũ
                $data['config_value'] = $config->config_value;
            } else {
                // Fallback cho các trường hợp khác
                $data['config_value'] = $data['config_value'] ?? ($config->config_value ?? '');
            }

            // Tạo mới hoặc cập nhật
            if ($id) {
                $config->update($data);
            } else {
                $config = Config::create($data);
            }

            DB::commit();
            return ['data' => $config];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi lưu/cập nhật cấu hình: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'jsonData' => $jsonData,
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lưu/cập nhật cấu hình: ' . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Xử lý dữ liệu JSON cho type OBJECT
     */
    protected function processObjectData(array $jsonData): string
    {
        if (empty($jsonData)) {
            throw new \Exception('Vui lòng thêm ít nhất một nhóm đối tượng hợp lệ');
        }

        $validObjects = [];

        foreach ($jsonData as $objectGroup) {
            if (!is_array($objectGroup) || empty($objectGroup)) {
                continue; // Bỏ qua nhóm rỗng
            }

            $cleanGroup = [];
            foreach ($objectGroup as $key => $value) {
                // Kiểm tra key không rỗng
                if (empty(trim($key))) {
                    continue;
                }

                $cleanKey = trim($key);

                // Xử lý value
                if (is_array($value)) {
                    // Lọc bỏ các value rỗng
                    $cleanValues = array_filter($value, function ($v) {
                        return !empty(trim($v));
                    });

                    if (!empty($cleanValues)) {
                        $cleanValues = array_map('trim', $cleanValues);
                        // Nếu chỉ có 1 giá trị, lưu dưới dạng string
                        if (count($cleanValues) === 1) {
                            $cleanGroup[$cleanKey] = reset($cleanValues);
                        } else {
                            $cleanGroup[$cleanKey] = array_values($cleanValues);
                        }
                    }
                } else {
                    // Value là string
                    $cleanValue = trim($value);
                    if (!empty($cleanValue)) {
                        $cleanGroup[$cleanKey] = $cleanValue;
                    }
                }
            }

            if (!empty($cleanGroup)) {
                $validObjects[] = $cleanGroup;
            }
        }

        if (empty($validObjects)) {
            throw new \Exception('Vui lòng thêm ít nhất một nhóm đối tượng với dữ liệu hợp lệ');
        }

        return json_encode($validObjects, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Xử lý dữ liệu JSON cho type OPTION
     */
    protected function processJsonData(array $jsonData): string
    {
        // Lọc bỏ các giá trị rỗng
        $filteredData = array_filter($jsonData, function ($item) {
            return !empty(trim($item));
        });

        if (empty($filteredData)) {
            throw new \Exception('Vui lòng thêm ít nhất một lựa chọn hợp lệ');
        }

        return json_encode(array_values($filteredData), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Xử lý dữ liệu BANK
     */
    protected function processBankData(array $jsonData): string
    {
        $validBanks = [];

        foreach ($jsonData as $bank) {
            // Kiểm tra dữ liệu bắt buộc
            if (empty($bank['value']) || empty($bank['label'])) {
                continue; // Bỏ qua bank không đầy đủ thông tin
            }

            $bankData = [
                'value' => trim($bank['value']),
                'label' => trim($bank['label']),
            ];

            // Thêm logo nếu có
            if (!empty($bank['logo']) && filter_var($bank['logo'], FILTER_VALIDATE_URL)) {
                $bankData['logo'] = trim($bank['logo']);
            }

            $validBanks[] = $bankData;
        }

        if (empty($validBanks)) {
            throw new \Exception('Vui lòng thêm ít nhất một ngân hàng hợp lệ');
        }

        return json_encode($validBanks, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Xóa cấu hình
     */
    public function deleteConfig(int $id): array
    {
        try {
            DB::beginTransaction();

            $config = Config::findOrFail($id);

            // Xóa ảnh nếu là type IMAGE
            if ($config->config_type === 'IMAGE' && $config->config_value) {
                $this->deleteImage($config->config_value);
            }

            $config->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa cấu hình: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Đã xảy ra lỗi khi xóa cấu hình: ' . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Lấy giá trị cấu hình theo key
     */
    public function getConfigByKey(string $key, $default = null)
    {
        $config = Config::where('config_key', $key)->first();

        if (!$config) {
            return $default;
        }

        // Trả về dữ liệu đã parse cho JSON, BANK và OBJECT
        if (in_array($config->config_type, ['JSON', 'BANK', 'OBJECT'])) {
            return json_decode($config->config_value, true) ?? $default;
        }

        return $config->config_value ?? $default;
    }

    /**
     * Chuyển đổi và lưu trữ hình ảnh tải lên dưới định dạng WebP.
     */
    protected function storeImageAsWebp(UploadedFile $imageFile): string
    {
        // Tạo thư mục nếu chưa tồn tại
        $directory = storage_path('app/public/images/configs');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileName = time() . '_' . Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '.webp';
        $webpPath = $directory . '/' . $fileName;

        // Convert image to WebP using GD
        $image = $this->createImageResource($imageFile);

        if (!$image) {
            throw new \Exception('Không thể tạo resource từ file ảnh');
        }

        $result = imagewebp($image, $webpPath, 80); // Quality set to 80
        imagedestroy($image);

        if (!$result) {
            throw new \Exception('Không thể lưu ảnh WebP');
        }

        return '/storage/images/configs/' . $fileName;
    }

    /**
     * Tạo tài nguyên hình ảnh dựa trên loại tệp.
     */
    protected function createImageResource(UploadedFile $imageFile)
    {
        $extension = strtolower($imageFile->getClientOriginalExtension());
        $tempPath = $imageFile->getPathname();

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                return imagecreatefromjpeg($tempPath);
            case 'png':
                $image = imagecreatefrompng($tempPath);
                // Preserve transparency for PNG
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return $image;
            case 'gif':
                return imagecreatefromgif($tempPath);
            default:
                throw new \Exception('Định dạng ảnh không được hỗ trợ: ' . $extension);
        }
    }

    /**
     * Xóa hình ảnh khỏi storage.
     */
    protected function deleteImage(string $imagePath): void
    {
        try {
            $path = str_replace('/storage/', '', $imagePath);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Đã xóa ảnh: ' . $path);
            }
        } catch (\Exception $e) {
            Log::warning('Không thể xóa ảnh: ' . $imagePath . ' - ' . $e->getMessage());
        }
    }
}