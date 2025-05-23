<?php
namespace App\Http\Controllers;

use App\Services\MotelService;
use App\Services\AmenityService;
use App\Services\DistrictService;
use App\Http\Requests\MotelRequest;
use Illuminate\Http\Request;

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
        $perPage = (int) $request->get('perPage', 25);

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
    // public function show()
    // {
    //     // $result = $this->motelService->getMotel($id);
    //     return view('motels.show');
    // }
    public function create()
    {
        $amenities = $this->amenityService->getAllAmenities();
        $districts = $this->districtService->getAllDistricts();
        return view('motels.create', ['amenities' => $amenities['data']], ['districts' => $districts['data']]);
    }

    public function store(MotelRequest $request)
    {
        $data = $request->validated();

        // Chuyển district thành district_id từ dữ liệu form
        if (isset($data['district'])) {
            $data['district_id'] = $data['district'];
            unset($data['district']);
        }

        // Đảm bảo amenities là mảng (rỗng nếu không chọn gì)
        $data['amenities'] = $request->input('amenities', []);

        // Xử lý hình ảnh
        // dd($request->file('images'));
        $imageFiles = $request->hasFile('images') ? $request->file('images') : [];

        if (!is_array($imageFiles)) {
            $imageFiles = [$imageFiles];
        }

        // Đặt trạng thái mặc định nếu không có (dù select là bắt buộc, đây là dự phòng)
        if (!isset($data['status'])) {
            $data['status'] = 'available';
        }

        $result = $this->motelService->createMotel($data, $imageFiles);

        if (isset($result['error'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['error']);
        }

        return redirect()->route('motels.index')->with('success', 'Nhà trọ đã được tạo thành công!');
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

    public function update(MotelRequest $request, int $id)
    {
        $imageFiles = $request->hasFile('images') ? $request->file('images') : [];
        $result = $this->motelService->updateMotel($id, $request->validated(), $imageFiles);
        return redirect()->route('motels.index')->with('message', 'Nhà trọ đã được cập nhật thành công!');
    }

    public function destroy(int $id)
    {
        $this->motelService->deleteMotel($id);
        return redirect()->route('motels.index')->with('message', 'Nhà trọ đã được xóa thành công!');
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

    public function restore(int $id)
    {
        $this->motelService->restoreMotel($id);
        return redirect()->route('motels.trash')->with('message', 'Nhà trọ đã được khôi phục thành công!');
    }

    public function forceDestroy(int $id)
    {
        $this->motelService->forceDeleteMotel($id);
        return redirect()->route('motels.trash')->with('message', 'Nhà trọ đã được xóa vĩnh viễn!');
    }
}