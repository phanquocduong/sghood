<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\MotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MotelController extends Controller
{
    public function __construct(protected MotelService $motelService)
    {
    }
    /**
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        return response()->json([
            'data' => $this->motelService->getFeaturedMotels(),
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        return response()->json($this->motelService->searchMotels($request));
    }
    /**
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        return response()->json([
            'data' => $this->motelService->getMotelDetail($slug),
        ]);
    }
}
