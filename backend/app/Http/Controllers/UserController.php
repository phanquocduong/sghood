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
        $querySearch = $request->get('query', '');
        $sortOption = $request->get('sortOption', '');
        $perPage = $request->get('perPage', 25);

        $result = $this->userService->getAllUsers($querySearch, $sortOption, $perPage);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function show(string $id)
    {
        $result = $this->userService->getUser($id);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json(['data' => $result['data']], 200);
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $result = $this->userService->update($id, $request->validated());
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }
        return response()->json([
            'message' => 'Cập nhật người dùng thành công!',
            'data' => $result['data']
        ], 200);
    }
}
