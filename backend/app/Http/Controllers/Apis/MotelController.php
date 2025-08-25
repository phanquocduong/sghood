<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Motel;
use App\Services\Apis\MotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller xử lý các yêu cầu API liên quan đến nhà trọ.
 */
class MotelController extends Controller
{
    /**
     * Khởi tạo controller với dịch vụ quản lý nhà trọ.
     *
     * @param MotelService $motelService Dịch vụ xử lý logic nhà trọ
     */
    public function __construct(protected MotelService $motelService)
    {
    }

    /**
     * Lấy danh sách nhà trọ nổi bật.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách nhà trọ nổi bật
     */
    public function featured(): JsonResponse
    {
        // Gọi dịch vụ để lấy danh sách nhà trọ nổi bật
        return response()->json([
            'data' => $this->motelService->getFeaturedMotels(),
        ]);
    }

    /**
     * Tìm kiếm nhà trọ theo các tiêu chí lọc.
     *
     * @param Request $request Yêu cầu chứa các tham số tìm kiếm
     * @return JsonResponse Phản hồi JSON chứa danh sách nhà trọ phù hợp
     */
    public function search(Request $request): JsonResponse
    {
        // Gọi dịch vụ để tìm kiếm nhà trọ theo tiêu chí
        return response()->json($this->motelService->searchMotels($request));
    }

    /**
     * Lấy chi tiết một nhà trọ theo slug.
     *
     * @param string $slug Slug của nhà trọ
     * @return JsonResponse Phản hồi JSON chứa chi tiết nhà trọ
     */
    public function show(string $slug): JsonResponse
    {
        // Gọi dịch vụ để lấy chi tiết nhà trọ
        return response()->json([
            'data' => $this->motelService->getMotelDetail($slug),
        ]);
    }

    /**
     * Lấy danh sách phòng trống của một nhà trọ.
     *
     * @param Motel $motel Mô hình nhà trọ
     * @return JsonResponse Phản hồi JSON chứa danh sách phòng trống
     */
    public function getRooms(Motel $motel)
    {
        // Lọc các phòng có trạng thái 'Trống' và định dạng dữ liệu
        return response()->json($motel->rooms->filter(function ($room) {
            return $room->status === 'Trống';
        })->map(function ($room) {
            return [
                'id' => $room->id, // ID phòng
                'name' => $room->name // Tên phòng
            ];
        })->values());
    }
}
