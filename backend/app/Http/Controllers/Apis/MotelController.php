<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\MotelService;
use Illuminate\Http\Request;

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

    public function search(Request $request)
    {
        $result = $this->motelService->searchMotels($request);
        return response()->json($result);
    }

    public function show($slug)
    {
        $motel = $this->motelService->getMotelDetail($slug);
        return response()->json(['data' => $motel]);
    }
}
