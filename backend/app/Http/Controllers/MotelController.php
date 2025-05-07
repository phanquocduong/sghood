<?php
// app/Http/Controllers/MotelController.php
namespace App\Http\Controllers;

use App\Services\MotelService;
use Illuminate\Http\Request;
use App\Http\Requests\MotelRequest;

class MotelController extends Controller
{
    protected $motelService;

    public function __construct(MotelService $motelService)
    {
        $this->motelService = $motelService;
    }

    public function index()
    {
        try {
            return response()->json($this->motelService->getAll());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình lấy dữ liệu: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $motel = $this->motelService->getById($id);
            if (!$motel)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json($motel);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình lấy dữ liệu: ' . $e->getMessage()], 500);
        }
    }

    public function store(MotelRequest $request)
    {
        try {
            $motel = $this->motelService->create($request->validated());
            return response()->json([
                'message' => 'Tạo mới thành công',
                'data' => $motel
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình tạo dữ liệu: ' . $e->getMessage()], 500);
        }
    }

    public function update(MotelRequest $request, $id)
    {
        try {
            $motel = $this->motelService->update($id, $request->validated());
            if (!$motel)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json([
                'message' => 'Cập nhật thành công',
                'data' => $motel
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình cập nhật dữ liệu: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->motelService->delete($id);
            if (!$deleted)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json(['message' => 'Xoá thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình xóa dữ liệu: ' . $e->getMessage()], 500);
        }
    }

   public function restore($id)
    {
        try {
            $restored = $this->motelService->restore($id);
            if (!$restored)
                return response()->json(['message' => 'Not found'], 404);

            return response()->json(['message' => 'Khôi phục thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi trong quá trình khôi phục dữ liệu: ' . $e->getMessage()], 500);
        }
    }
}
