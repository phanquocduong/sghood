<?php

namespace App\Http\Controllers;

use App\Http\Requests\MotelImageRequest;
use App\Services\MotelImageService;
use Illuminate\Http\JsonResponse;

class MotelImageController extends Controller
{
    protected $motelImageService;

    public function __construct(MotelImageService $service)
    {
        $this->motelImageService = $service;
    }

    public function index()
    {
        try {
            return response()->json($this->motelImageService->getAll());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $image = $this->motelImageService->getById($id);
            if (!$image)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json($image, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function store(MotelImageRequest $request)
    {
        try {
            $image = $this->motelImageService->create($request->validated());
            return response()->json([
                'message' => 'Thêm mới thành công!',
                'data' => $image
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function update(MotelImageRequest $request, $id)
    {
        try {
            $image = $this->motelImageService->update($id, $request->validated());
            if (!$image)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json([
                'message' => 'Cập nhật thành công!',
                'data' => $image
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->motelImageService->delete($id);
            if (!$deleted)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json(['message' => 'Xóa thành công!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}

