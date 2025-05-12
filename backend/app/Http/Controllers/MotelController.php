<?php
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

   public function index(Request $request) {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getAllMotels($querySearch, $status, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function show(int $id) {
        $result = $this->motelService->getMotelById($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function store(MotelRequest $request) {
        $result = $this->motelService->createMotel($request->validated(), $request->file('images'));
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        $response = [
            'message' => 'Nhà trọ đã được tạo thành công!',
            'data' => $result['data']
        ];

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, một số ảnh không được upload thành công.';
        }

        return response()->json($response, 201);
    }

    public function update(MotelRequest $request, int $id) {
        $result = $this->motelService->updateMotel($id, $request->validated(), $request->file('images'));

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $response = [
            'message' => 'Nhà trọ đã được cập nhật thành công!',
            'data' => $result['data']
        ];

        if (isset($result['warnings'])) {
            $response['warnings'] = $result['warnings'];
            $response['message'] .= ' Tuy nhiên, một số ảnh không được upload thành công.';
        }

        return response()->json($response, 200);
    }

    public function destroy(int $id) {
        $result = $this->motelService->deleteMotel($id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json(['message' => 'Nhà trọ được xoá thành công'], 200);
    }

    public function trash(Request $request) {
        $querySearch = $request->get('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->motelService->getTrashedMotels($querySearch, $status, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function restore(int $id) {
        $result = $this->motelService->restoreMotel($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Nhà trọ đã được khôi phục thành công!',
            'data' => $result['data']
        ], 200);
    }

    public function forceDestroy(int $id) {
        $result = $this->motelService->forceDeleteMotel($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['message' => 'Nhà trọ đã được xóa vĩnh viễn!'], 200);
    }
}
