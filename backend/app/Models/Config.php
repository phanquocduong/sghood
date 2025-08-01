<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Config extends Model
{
    protected $table = 'configs';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
        'config_type',
    ];

    /**
     * Lấy giá trị config từ database với cache
     * @param string $key - Tên key config
     * @param mixed $default - Giá trị mặc định nếu không tìm thấy
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        return Cache::remember("config_{$key}", 3600, function () use ($key, $default) {
            $config = self::where('config_key', $key)->first();
            
            if (!$config) {
                return $default;
            }

            // Chuyển đổi giá trị theo type
            return self::convertValue($config->config_value, $config->config_type);
        });
    }

    /**
     * Lấy nhiều config cùng lúc
     * @param array $keys - Mảng các key cần lấy
     * @return array
     */
    public static function getMultiple(array $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::getValue($key);
        }
        return $result;
    }

    /**
     * Lấy tất cả config theo pattern key
     * @param string $pattern - Pattern để tìm kiếm (VD: 'contact_%')
     * @return array
     */
    public static function getByPattern($pattern)
    {
        $cacheKey = "config_pattern_" . md5($pattern);
        
        return Cache::remember($cacheKey, 3600, function () use ($pattern) {
            $configs = self::where('config_key', 'LIKE', $pattern)->get();
            
            $result = [];
            foreach ($configs as $config) {
                $result[$config->config_key] = self::convertValue(
                    $config->config_value, 
                    $config->config_type
                );
            }
            
            return $result;
        });
    }

    /**
     * Cập nhật hoặc tạo mới config
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string $description
     * @return Config
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        $config = self::updateOrCreate(
            ['config_key' => $key],
            [
                'config_value' => self::formatValue($value, $type),
                'config_type' => $type,
                'description' => $description
            ]
        );

        // Xóa cache liên quan
        Cache::forget("config_{$key}");
        self::clearPatternCache();

        return $config;
    }

    /**
     * Xóa config
     * @param string $key
     * @return bool
     */
    public static function removeConfig($key)
    {
        $result = self::where('config_key', $key)->delete();
        Cache::forget("config_{$key}");
        self::clearPatternCache();
        
        return $result > 0;
    }

    /**
     * Lấy config cho email
     * @return array
     */
    public static function getEmailConfigs()
    {
        return self::getByPattern('email_%');
    }

    /**
     * Lấy config liên hệ
     * @return array
     */
    public static function getContactConfigs()
    {
        return self::getByPattern('contact_%');
    }

    /**
     * Lấy config công ty
     * @return array
     */
    public static function getCompanyConfigs()
    {
        return self::getByPattern('company_%');
    }

    /**
     * Chuyển đổi giá trị theo type
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function convertValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float', 'number' => (float) $value,
            'json' => json_decode($value, true),
            'array' => is_string($value) ? explode(',', $value) : $value,
            default => (string) $value,
        };
    }

    /**
     * Format giá trị để lưu vào database
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function formatValue($value, $type)
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            'array' => is_array($value) ? implode(',', $value) : $value,
            default => (string) $value,
        };
    }

    /**
     * Xóa cache pattern
     */
    private static function clearPatternCache()
    {
        // Xóa các cache pattern phổ biến
        $patterns = ['email_%', 'contact_%', 'company_%', 'app_%'];
        foreach ($patterns as $pattern) {
            $cacheKey = "config_pattern_" . md5($pattern);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Refresh tất cả cache config
     */
    public static function clearAllCache()
    {
        $configs = self::all();
        foreach ($configs as $config) {
            Cache::forget("config_{$config->config_key}");
        }
        self::clearPatternCache();
    }
}