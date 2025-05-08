<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            $querySearch = $request->get('query');
            $sortOption = $request->get('sortOption');
            $perPage = $request->get('per_page', 10);

            $users = $this->userService->getAllUsers($querySearch, $sortOption, $perPage);

            return response()->json([
                'data' => $users->items(),
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $user = $this->userService->getUser($id);

            return response()->json([
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã có lỗi xảy ra',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $validatedRequest = $request->validated();
            $user = $this->userService->update($id, $validatedRequest);

            return response()->json([
                'message' => 'Cập nhật người dùng thành công',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
