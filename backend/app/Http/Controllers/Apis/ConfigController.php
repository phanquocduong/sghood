<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;

class ConfigController extends Controller
{
    public function index()
    {
        // Lấy các trường cần thiết từ bảng configs
        $configs = Config::select('config_key', 'config_value', 'description', 'config_type')->get();

        return response()->json($configs);
    }

}
