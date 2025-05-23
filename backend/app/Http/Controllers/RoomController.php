<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Models\Motel;
use App\Models\Amenity;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    // Hiển thị danh sách phòng theo id của motel.
    public function index(Request $request): View|RedirectResponse
    {
        $motelId = $request->query('motel_id', '');
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 10);

        // Kiểm tra và lấy thông tin nhà trọ
        $motelResult = $this->getAndValidateMotel($motelId);
        if (isset($motelResult['error'])) {
            return redirect()->route('motels.index')->with('error', $motelResult['error']);
        }

        $result = $this->roomService->getRoomsByMotelId(
            $motelId, $querySearch, $status, $sortOption, $perPage
        );

        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }

        return view('rooms.index', [
            'rooms' => $result['data'],
            'querySearch' => $querySearch,
            'status' => $status,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
            'motelId' => $motelId,
            'motel' => $motelResult['data']
        ]);
    }

    // Xử lý lỗi từ service và chuyển hướng phù hợp
    private function handleServiceError($result, $inputData = [])
    {
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput($inputData);
        }
        return null;
    }

    // Kiểm tra và lấy thông tin nhà trọ theo ID
    private function getAndValidateMotel($motelId)
    {
        if (empty($motelId)) {
            return ['error' => 'Vui lòng chọn một nhà trọ.'];
        }

        $motel = Motel::where('status', 'Hoạt động')->find($motelId);
        if (!$motel) {
            return ['error' => 'Nhà trọ không tồn tại hoặc không hoạt động.'];
        }

        return ['data' => $motel];
    }

    // Lấy danh sách tiện ích phòng đang hoạt động
    private function getActiveRoomAmenities()
    {
        return Amenity::where('status', 'Hoạt động')
                      ->where('type', 'Phòng trọ')
                      ->get();
    }

    // Hiển thị chi tiết phòng.
    public function show(string $id): View|RedirectResponse
    {
        $result = $this->roomService->getRoom($id);

        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }

        return view('rooms.show', ['room' => $result['data']]);
    }

    // Hiển thị form tạo phòng mới.
    public function create(Request $request): View|RedirectResponse
    {
        $motelId = $request->query('motel_id');

        // Kiểm tra và lấy thông tin nhà trọ
        $motelResult = $this->getAndValidateMotel($motelId);
        if (isset($motelResult['error'])) {
            return redirect()->route('motels.index')->with('error', $motelResult['error']);
        }

        return view('rooms.create', [
            'motel' => $motelResult['data'],
            'amenities' => $this->getActiveRoomAmenities()
        ]);
    }

    // Tạo phòng mới.
    public function store(RoomRequest $request): RedirectResponse
    {
        $motelId = $request->input('motel_id', '');
        $result = $this->roomService->createRoom(
            $request->validated(),
            $request->file('images')
        );

        $errorResponse = $this->handleServiceError($result, $request->all());
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('rooms.index', ['motel_id' => $motelId])
            ->with('success', 'Phòng đã được tạo thành công!');
    }

    // Hiển thị form chỉnh sửa phòng.
    public function edit(string $id): View|RedirectResponse
    {
        $result = $this->roomService->getRoom($id);

        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }
        // $room = $result['data'];
        $motels = Motel::where('status', 'Hoạt động')->get();
        return view('rooms.edit', [
            'room' => $result['data'],
            'motels' => $motels,
            'amenities' => $this->getActiveRoomAmenities()
        ]);
    }

    // Cập nhật thông tin phòng.
    public function update(RoomRequest $request, string $id): RedirectResponse
    {
        $motelId = $request->input('motel_id', '');
        $imageFiles = $request->hasFile('images') ? $request->file('images') : [];
        $mainImageId = $request->input('is_main');

        $result = $this->roomService->updateRoom(
            $id,
            $request->validated(),
            $imageFiles,
            $mainImageId
        );

        $errorResponse = $this->handleServiceError($result, $request->all());
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('rooms.index', ['motel_id' => $motelId])
            ->with('success', 'Phòng đã được cập nhật thành công!');
    }

    // Xóa phòng.
    public function destroy(string $id): RedirectResponse
    {
        $roomResult = $this->roomService->getRoom($id);
        if (isset($roomResult['error'])) {
            return redirect()->back()->with('error', $roomResult['error']);
        }

        $motelId = $roomResult['data']->motel_id;
        $result = $this->roomService->deleteRoom($id);

        $errorResponse = $this->handleServiceError($result);
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('rooms.index', ['motel_id' => $motelId])
            ->with('success', 'Phòng đã được xóa thành công!');
    }

    // Hiển thị danh sách phòng đã xóa.
    public function trash(Request $request): View|RedirectResponse
    {
        $motelId = $request->query('motel_id', '');
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        // Kiểm tra và lấy thông tin nhà trọ
        $motelResult = $this->getAndValidateMotel($motelId);
        if (isset($motelResult['error'])) {
            return redirect()->route('motels.index')->with('error', $motelResult['error']);
        }

        $result = $this->roomService->getTrashedRoomsByMotelId(
            $motelId, $querySearch, $status, $sortOption, $perPage
        );

        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }

        return view('rooms.trash', [
            'rooms' => $result['data'],
            'querySearch' => $querySearch,
            'status' => $status,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
            'motelId' => $motelId,
            'motel' => $motelResult['data']
        ]);
    }

    // Hiển thị chi tiết phòng đã xóa.
    public function showTrashed(string $id): View|RedirectResponse
    {
        $result = $this->roomService->getRoom($id, true);

        if (isset($result['error'])) {
            return redirect()->route('motels.index')->with('error', $result['error']);
        }

        return view('rooms.show-trashed', ['room' => $result['data']]);
    }

    // Khôi phục phòng đã xóa.
    public function restore(string $id): RedirectResponse
    {
        $roomResult = $this->roomService->getRoom($id, true);
        if (isset($roomResult['error'])) {
            return redirect()->back()->with('error', $roomResult['error']);
        }

        $motelId = $roomResult['data']->motel_id;
        $result = $this->roomService->restoreRoom($id);

        $errorResponse = $this->handleServiceError($result);
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('rooms.trash', ['motel_id' => $motelId])
            ->with('success', 'Phòng đã được khôi phục thành công!');
    }

    // Xóa vĩnh viễn phòng đã xóa.
    public function forceDelete(string $id): RedirectResponse
    {
        $roomResult = $this->roomService->getRoom($id, true);
        if (isset($roomResult['error'])) {
            return redirect()->back()->with('error', $roomResult['error']);
        }

        $motelId = $roomResult['data']->motel_id;
        $result = $this->roomService->permanentlyDeleteRoom($id);

        $errorResponse = $this->handleServiceError($result);
        if ($errorResponse) {
            return $errorResponse;
        }

        return redirect()
            ->route('rooms.trash', ['motel_id' => $motelId])
            ->with('success', 'Phòng đã được xóa vĩnh viễn!');
    }

    // Xóa hình ảnh phòng.
    public function deleteImage(Request $request, $roomId, $imageId): JsonResponse
    {
        $result = $this->roomService->deleteSingleRoomImage((int)$imageId, (int)$roomId);

        if (isset($result['error'])) {
            return response()->json($result, $result['status'] ?? 400);
        }

        return response()->json($result);
    }
}
