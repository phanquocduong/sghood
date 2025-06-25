<?php

namespace App\Http\Controllers;

use App\Services\MotelService;
use App\Services\AmenityService;
use App\Services\DistrictService;
use App\Http\Requests\MotelRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Motel;
use App\Models\MotelImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MotelController extends Controller
{
    protected $motelService;
    protected $amenityService;
    protected $districtService;

    public function __construct(MotelService $motelService, AmenityService $amenityService, DistrictService $districtService)
    {
        $this->motelService = $motelService;
        $this->amenityService = $amenityService;
        $this->districtService = $districtService;
    }

    public function index(Request $request)
    {
        $districts = $this->districtService->getAllDistricts();

        $querySearch = (string) $request->get('querySearch', '');
        $status = (string) $request->get('status', '');
        $sortOption = (string) $request->get('sortOption', '');
        $area = (string) $request->get('area', '');
        $perPage = (int) $request->get('perPage', 10);

        $result = $this->motelService->getAvailableMotels($querySearch, $status, $area, $sortOption, $perPage);

        return view('motels.index', [
            'motels' => $result['data'],
            'districts' => $districts['data']
        ]);
    }

    public function show(int $id)
    {
        $result = $this->motelService->getMotel($id, false);
        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }
        return view('motels.show', ['motel' => $result['data']]);
    }

    public function create()
    {
        $amenities = $this->amenityService->getAllAmenities();
        $districts = $this->districtService->getAllDistricts();
        return view('motels.create', ['amenities' => $amenities['data'], 'districts' => $districts['data']]);
    }

    public function store(MotelRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $imageFiles = $request->hasFile('images') ? $request->file('images') : [];
            $mainImageIndex = (int) $request->input('main_image_index', 0);

            $result = $this->motelService->createMotel($data, $imageFiles, $mainImageIndex);

            if (isset($result['error'])) {
                Log::error('Lỗi tạo nhà trọ: ' . $result['error']);
                return redirect()->back()->withInput()->with('error', $result['error']);
            }

            $message = 'Nhà trọ đã được tạo thành công!';
            if (isset($result['warnings'])) {
                $message .= ' Tuy nhiên, một số hình ảnh không thể tải lên: ' . implode(', ', $result['warnings']['failed_images']);
            }

            return redirect()->route('motels.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi không mong muốn trong MotelController::store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đã xảy ra lỗi không mong muốn: ' . $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $result = $this->motelService->getMotel($id, false);
        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }
        $districts = $this->districtService->getAllDistricts();
        $amenities = $this->amenityService->getAllAmenities();
        return view('motels.edit', ['motel' => $result['data'], 'districts' => $districts['data'], 'amenities' => $amenities['data']]);
    }

    public function update(MotelRequest $request, int $id): RedirectResponse
    {
        try {
            $data = $request->validated();
            $imageFiles = $request->hasFile('images') ? $request->file('images') : [];

            // Xử lý is_main từ radio buttons hoặc new_main_image_index từ JS
            $isMain = $request->input('is_main', null);
            $newMainImageIndex = $request->input('new_main_image_index', null);

            if ($isMain) {
                $data['is_main'] = $isMain;
            }
            if ($newMainImageIndex !== null) {
                $data['new_main_image_index'] = (int)$newMainImageIndex;
            }

            Log::info('Cập nhật nhà trọ ID: ' . $id, [
                'is_main' => $isMain,
                'new_main_image_index' => $newMainImageIndex,
                'image_files_count' => count($imageFiles),
                'all_request_data' => $request->all()
            ]);

            $result = $this->motelService->updateMotel($id, $data, $imageFiles);

            if (isset($result['error'])) {
                Log::error('Lỗi cập nhật nhà trọ: ' . $result['error']);
                return redirect()->back()->withInput()->with('error', $result['error']);
            }

            $message = 'Nhà trọ đã được cập nhật thành công!';
            if (isset($result['warnings'])) {
                $message .= ' Tuy nhiên, một số hình ảnh không thể tải lên: ' . implode(', ', $result['warnings']['failed_images']);
            }

            return redirect()->route('motels.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Lỗi không mong muốn trong MotelController::update: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đã xảy ra lỗi không mong muốn: ' . $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $result = $this->motelService->deleteMotel($id);
            if (isset($result['error'])) {
                return redirect()->route('motels.index')->with('error', $result['error']);
            }
            return redirect()->route('motels.index')->with('success', 'Nhà trọ đã được xóa thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi xóa nhà trọ: ' . $e->getMessage());
            return redirect()->route('motels.index')->with('error', 'Đã xảy ra lỗi khi xóa nhà trọ: ' . $e->getMessage());
        }
    }

    public function trash(Request $request)
    {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);
        $area = $request->get('area', '');

        $result = $this->motelService->getTrashedMotels($querySearch, $status, $area, $sortOption, $perPage);
        return view('motels.trash', ['motels' => $result['data']]);
    }

    public function showTrashed(int $id)
    {
        $result = $this->motelService->getMotel($id, true);
        return view('motels.show-trashed', ['motel' => $result['data']]);
    }

    public function restore(int $id): RedirectResponse
    {
        try {
            $result = $this->motelService->restoreMotel($id);
            if (isset($result['error'])) {
                return redirect()->route('motels.trash')->with('error', $result['error']);
            }
            return redirect()->route('motels.trash')->with('success', 'Nhà trọ đã được khôi phục thành công!');
        } catch (\Exception $e) {
            Log::error('Lỗi khôi phục nhà trọ: ' . $e->getMessage());
            return redirect()->route('motels.trash')->with('error', 'Đã xảy ra lỗi khi khôi phục nhà trọ: ' . $e->getMessage());
        }
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        try {
            $result = $this->motelService->forceDeleteMotel($id);
            if (isset($result['error'])) {
                return redirect()->route('motels.trash')->with('error', $result['error']);
            }
            return redirect()->route('motels.trash')->with('success', 'Nhà trọ đã được xóa vĩnh viễn!');
        } catch (\Exception $e) {
            Log::error('Lỗi xóa vĩnh viễn nhà trọ: ' . $e->getMessage());
            return redirect()->route('motels.trash')->with('error', 'Đã xảy ra lỗi khi xóa vĩnh viễn nhà trọ: ' . $e->getMessage());
        }
    }

    public function deleteMotelImage(int $motelId, int $imageId)
    {
        try {
            $motel = Motel::findOrFail($motelId);
            $image = MotelImage::where('motel_id', $motelId)->findOrFail($imageId);

            // Xóa file vật lý
            if (Storage::disk('public')->exists($image->image_url)) {
                Storage::disk('public')->delete($image->image_url);
            }

            // Xóa bản ghi trong database
            $wasMainImage = $image->is_main;
            $image->delete();

            // Nếu xóa ảnh chính, sử dụng MotelService để đảm bảo có ảnh chính mới
            if ($wasMainImage) {
                $this->motelService->ensureMainImage($motelId);
            }

            return response()->json(['success' => true, 'message' => 'Đã xóa ảnh thành công']);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa ảnh nhà trọ: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi khi xóa ảnh: ' . $e->getMessage()], 500);
        }
    }
}
?>
