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
    public function index(Request $request): View
    {
        $motelId = $request->query('motel_id', ''); // Lấy motel_id từ query string, mặc định rỗng
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        if (empty($motelId)) {
            return redirect()->route('motels.index')->with('error', 'Vui lòng chọn một nhà trọ để xem phòng.');
        }

        $result = $this->roomService->getRoomsByMotelId($motelId, $querySearch, $status, $sortOption, $perPage);

        return view('rooms.index', [
            'rooms' => $result['data'],
            'querySearch' => $querySearch,
            'status' => $status,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
            'motelId' => $motelId,
        ]);
    }

    // Hiển thị chi tiết phòng.
    public function show(string $id): View
    {
        $result = $this->roomService->getRoom($id);
        return view('rooms.show', ['room' => $result['data']]);
    }

    // Hiển thị form tạo phòng mới.
    public function create(Request $request): View
    {
        // Lấy danh sách nhà trọ đang hoạt động
        $motels = Motel::where('status', 'Hoạt động')->get();
        $selectedMotelId = $request->query('motel_id', '');

        $amenities = Amenity::where('status', 'Hoạt động')
                            ->where('type', 'Phòng trọ')
                            ->get();

        return view('rooms.create', [
            'motels' => $motels,
            'selectedMotelId' => $selectedMotelId,
            'amenities' => $amenities
        ]);
    }

    // Tạo phòng mới.
    public function store(RoomRequest $request): RedirectResponse
    {
        $motelId = $request->input('motel_id', ''); // Lấy motel_id từ form
        $result = $this->roomService->createRoom($request->validated(), $request->file('images'));

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput();
        }

        return redirect()->route('rooms.index', ['motel_id' => $motelId])->with('success', 'Phòng đã được tạo thành công!');
    }

    // Hiển thị form chỉnh sửa phòng.
    public function edit(Request $request, string $id): View
    {
        $motels = Motel::where('status', 'Hoạt động')->get();
                $selectedMotelId = $request->query('motel_id', '');

        $amenities = Amenity::where('status', 'Hoạt động')
                            ->where('type', 'Phòng trọ')
                            ->get();

        $result = $this->roomService->getRoom($id);
        return view('rooms.edit', [
            'room' => $result['data'],
            'motels' => $motels,
            'selectedMotelId' => $selectedMotelId,
            'amenities' => $amenities
        ]);
    }

    // Cập nhật thông tin phòng.
    public function update(RoomRequest $request, string $id): RedirectResponse
    {
        $motelId = $request->input('motel_id', '');
        $imageFiles = $request->hasFile('images') ? $request->file('images') : [];
        $mainImageId = $request->input('is_main'); // Lấy ID của ảnh được chọn làm ảnh chính

        // Truyền mainImageId vào phương thức updateRoom
        $result = $this->roomService->updateRoom($id, $request->validated(), $imageFiles, $mainImageId);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']])->withInput();
        }

        return redirect()->route('rooms.index', ['motel_id' => $motelId])->with('success', 'Phòng đã được cập nhật thành công!');
    }

    // Xóa phòng.
    public function destroy(string $id): RedirectResponse
    {
        $room = $this->roomService->getRoom($id)['data'];
        $motelId = $room->motel_id ?? '';
        $result = $this->roomService->deleteRoom($id);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('rooms.index', ['motel_id' => $motelId])->with('success', 'Phòng đã được xoá thành công!');
    }

    // Hiển thị danh sách phòng đã xóa.
    public function trash(Request $request): View
    {
        $motelId = $request->query('motel_id', '');
        $querySearch = $request->query('query', '');
        $status = $request->query('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        if (empty($motelId)) {
            return redirect()->route('motels.index')->with('error', 'Vui lòng chọn một nhà trọ để xem phòng đã xóa.');
        }

        $result = $this->roomService->getTrashedRoomsByMotelId($motelId, $querySearch, $status, $sortOption, $perPage);

        return view('rooms.trash', [
            'rooms' => $result['data'],
            'querySearch' => $querySearch,
            'status' => $status,
            'sortOption' => $sortOption,
            'perPage' => $perPage,
            'motelId' => $motelId,
        ]);
    }

    // Hiển thị chi tiết phòng đã xóa.
    public function showTrashed(string $id): View
    {
        $result = $this->roomService->getRoom($id, true);
        return view('rooms.show-trashed', ['room' => $result['data']]);
    }

    // Khôi phục phòng đã xóa.
    public function restore(string $id): RedirectResponse
    {
        $room = $this->roomService->getRoom($id, true)['data'];
        $motelId = $room->motel_id ?? '';
        $result = $this->roomService->restoreRoom($id);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('rooms.trash', ['motel_id' => $motelId])->with('success', 'Phòng đã được khôi phục thành công!');
    }

    // Xóa vĩnh viễn phòng đã xóa.
    public function forceDelete(string $id): RedirectResponse
    {
        $room = $this->roomService->getRoom($id, true)['data'];
        $motelId = $room->motel_id ?? '';
        $result = $this->roomService->permanentlyDeleteRoom($id);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('rooms.trash', ['motel_id' => $motelId])->with('success', 'Phòng đã được xóa vĩnh viễn!');
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
