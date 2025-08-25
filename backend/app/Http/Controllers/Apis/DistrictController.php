<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;

/**
 * Controller xử lý các yêu cầu API liên quan đến quận/huyện.
 */
class DistrictController extends Controller
{
    /**
     * Lấy danh sách các quận nổi bật dựa trên số lượng nhà trọ.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách quận nổi bật
     */
    public function index(): JsonResponse
    {
        // Lấy 5 quận có số lượng nhà trọ lớn nhất
        $districts = District::select('id', 'name', 'image')
            ->withCount('motels') // Đếm số lượng nhà trọ liên quan đến quận
            ->orderBy('motels_count', 'desc') // Sắp xếp theo số lượng nhà trọ giảm dần
            ->take(5) // Giới hạn kết quả trả về 5 quận
            ->get()
            ->map(function ($district) {
                // Định dạng dữ liệu quận để trả về
                return [
                    'id' => $district->id, // ID của quận
                    'name' => $district->name, // Tên quận
                    'image' => $district->image, // Đường dẫn hình ảnh quận
                    'motel_count' => $district->motels_count // Số lượng nhà trọ
                ];
            });

        // Trả về phản hồi JSON với danh sách quận
        return response()->json(['data' => $districts]);
    }
}
