<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;

/**
 * Controller xử lý các yêu cầu API liên quan đến cấu hình hệ thống.
 */
class ConfigController extends Controller
{
    /**
     * Lấy danh sách các cấu hình từ cơ sở dữ liệu.
     *
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON chứa danh sách cấu hình
     */
    public function index()
    {
        // Lấy các trường cần thiết từ bảng configs
        $configs = Config::select('config_key', 'config_value', 'description', 'config_type')->get();

        // Trả về phản hồi JSON với danh sách cấu hình
        return response()->json($configs);
    }
}
