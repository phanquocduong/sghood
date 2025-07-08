<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Motel;
use App\Services\Apis\MotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MotelController extends Controller
{
    public function __construct(protected MotelService $motelService)
    {
    }

    /**
     * Lấy danh sách nhà trọ nổi bật.
     *
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        return response()->json([
            'data' => $this->motelService->getFeaturedMotels(),
        ]);
    }

    /**
     * Tìm kiếm nhà trọ theo tiêu chí.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        return response()->json($this->motelService->searchMotels($request));
    }

    /**
     * Lấy chi tiết nhà trọ theo slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        return response()->json([
            'data' => $this->motelService->getMotelDetail($slug),
        ]);
    }

    public function getRooms(Motel $motel)
    {
        return response()->json($motel->rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name
            ];
        }));
    }
}
