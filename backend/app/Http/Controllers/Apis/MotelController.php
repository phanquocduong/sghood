<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\MotelService;

class MotelController extends Controller
{
    protected $motelService;

    public function __construct(MotelService $motelService)
    {
        $this->motelService = $motelService;
    }

    public function featured()
    {
        $motels = $this->motelService->getFeaturedMotels();
        return response()->json(['data' => $motels]);
    }
}
