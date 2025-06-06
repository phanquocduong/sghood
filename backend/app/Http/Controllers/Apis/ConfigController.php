<?php

namespace App\Http\Controllers\apis;
use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ConfigController extends Controller
{
    public function index()
    {
        // Lấy toàn bộ config
        $configs = Config::all();
        return response()->json($configs);
    }
}
