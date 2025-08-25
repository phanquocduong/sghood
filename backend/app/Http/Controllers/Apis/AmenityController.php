<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;

/**
 * Controller xử lý các yêu cầu API liên quan đến tiện ích (Amenity).
 */
class AmenityController extends Controller
{
    /**
     * Lấy danh sách tiện ích cho nhà trọ.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách tiện ích
     */
    public function index(): JsonResponse
    {
        // Lấy danh sách tiện ích có type là 'Nhà trọ' và trạng thái 'Hoạt động'
        $amenities = Amenity::where('type', 'Nhà trọ')
            ->where('status', 'Hoạt động')
            ->select('name') // Chỉ lấy trường name
            ->orderBy('order', 'asc') // Sắp xếp theo cột order tăng dần
            ->get()
            ->map(function ($amenity) {
                // Chuyển đổi mỗi tiện ích thành định dạng yêu cầu (value và label)
                return [
                    'value' => $amenity->name,
                    'label' => $amenity->name,
                ];
            });

        // Trả về phản hồi JSON với dữ liệu tiện ích
        return response()->json(['data' => $amenities]);
    }
}
