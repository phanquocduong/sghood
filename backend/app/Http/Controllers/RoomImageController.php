<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomImageRequest;
use App\Http\Requests\UpdateRoomImageRequest;
use App\Services\RoomImageService;
use Illuminate\Http\Request;

class RoomImageController extends Controller
{
    protected $roomImageService;

    public function __construct(RoomImageService $roomImageService)
    {
        $this->roomImageService = $roomImageService;
    }

    public function index(Request $request, string $roomId)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $images = $this->roomImageService->getAllImages($roomId, $perPage);

            return response()->json([
                'data' => $images->items(),
                'current_page' => $images->currentPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total(),
                'last_page' => $images->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreRoomImageRequest $request, string $roomId)
    {
        try {
            $validatedRequest = $request->validated();

            $images = [];
            if ($request->hasFile('images')) {
                $imageFiles = $request->file('images');
                foreach ($imageFiles as $imageFile) {
                    $imageUrl = $this->roomImageService->handleImage($imageFile);
                    $images[] = $this->roomImageService->create($roomId, ['image_url' => $imageUrl]);
                }
            }

            return response()->json([
                'message' => 'Thêm hình ảnh thành công',
                'data' => $images,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateRoomImageRequest $request, string $roomId, string $id)
    {
        try {
            $validatedRequest = $request->validated();

            if ($request->hasFile('image')) {
                $validatedRequest['image_url'] = $this->roomImageService->handleImage($request->file('image'));
            }

            $image = $this->roomImageService->update($roomId, $id, $validatedRequest);

            return response()->json([
                'message' => 'Cập nhật hình ảnh thành công',
                'data' => $image,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $roomId, string $id)
    {
        try {
            $result = $this->roomImageService->destroy($roomId, $id);

            if ($result) {
                return response()->json([
                    'message' => 'Xóa hình ảnh thành công',
                    'data' => ['id' => $id],
                ], 200);
            }

            return response()->json([
                'message' => 'Hình ảnh không tìm thấy hoặc xóa thất bại',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
