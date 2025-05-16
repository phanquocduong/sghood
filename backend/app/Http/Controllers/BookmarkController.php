<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarkRequest;
use App\Services\BookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    protected $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->get('userId', '');
        $motelId = $request->get('motelId', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->bookmarkService->getBookmarks($userId, $motelId, $sortOption, $perPage);
        return $this->handleResponse($result);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->bookmarkService->getBookmark($id);
        return $this->handleResponse($result);
    }

    public function store(StoreBookmarkRequest $request): JsonResponse
    {
        $result = $this->bookmarkService->createBookmark($request->validated());
        return $this->handleResponse(
            $result,
            'Bookmark đã được tạo thành công!',
            201
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->bookmarkService->deleteBookmark($id);
        return $this->handleResponse(
            $result,
            'Bookmark đã được xóa thành công!'
        );
    }

    private function handleResponse(array $result, string $successMessage = '', int $successStatus = 200): JsonResponse
    {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        if (isset($result['data'])) {
            $response = ['data' => $result['data']];
        } else {
            $response = ['success' => $result['success']];
        }
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        return response()->json($response, $successStatus);
    }
}
