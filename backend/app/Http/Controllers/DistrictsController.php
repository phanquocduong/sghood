<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DistrictService;
use App\Http\Requests\DistrictRequest;
use Illuminate\Http\JsonResponse;

class DistrictsController extends Controller
{
    protected $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(): JsonResponse
    {
        try {
            $districts = $this->districtService->getAllDistricts();
            return response()->json(['data' => $districts], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình lấy danh sách quận/huyện!'], 500);
        }
    }

    public function store(DistrictRequest $request): JsonResponse
    {
        try {
            // Validate dữ liệu ban đầu
            $data = $request->validated();

            // Xử lý ảnh nếu có
            $imagePath = $this->districtService->handleDistrictImage($request->file('image'));
            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            // Tạo quận/huyện mới
            $district = $this->districtService->createDistrict($data);

            return response()->json([
                'message' => 'Quận/Huyện đã được tạo thành công!',
                'data' => $district
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình tạo quận/huyện!'], 500);
        }
    }


    public function show($id): JsonResponse
    {
        try {

            $district = $this->districtService->getDistrictById($id);
            return response()->json(['data' => $district], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình lấy thông tin quận/huyện!'], 500);
        }
    }

    public function update(DistrictRequest $request, $id): JsonResponse
    {
        try {

            // Validate dữ liệu ban đầu
            $data = $request->validated();

            // Xử lý ảnh nếu có
            $imagePath = $this->districtService->handleDistrictImage($request->file('image'));
            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            // Cập nhật quận/huyện
            $district = $this->districtService->updateDistrict($id, $data);

            return response()->json([
                'message' => 'Quận/Huyện đã được cập nhật thành công!',
                'data' => $district
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình cập nhật quận/huyện!'], 500);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            // Lấy thông tin quận/huyện
            $district = $this->districtService->getDistrictById($id);

            if (!$district) {
                return response()->json(['message' => 'Không tìm thấy Quận/Huyện!'], 404);
            }

            // Xoá ảnh nếu có
            if (!empty($district->image)) {
                $this->districtService->deleteDistrictImage($district->image);
            }

            // Xoá quận/huyện
            $this->districtService->deleteDistrict($id);

            return response()->json(['message' => 'Quận/Huyện đã được xoá thành công!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra trong quá trình xoá quận/huyện!'], 500);
        }
    }

}
