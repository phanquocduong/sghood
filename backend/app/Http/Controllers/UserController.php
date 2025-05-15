<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $querySearch = $request->query('query', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->userService->getAllUsers($querySearch, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->userService->getUser($id);

        return $this->handleResponse($result);
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse {
        $result = $this->userService->update($id, $request->validated());

        return $this->handleResponse($result, 'Cập nhật người dùng thành công!');
    }

    private function handleResponse(array $result, string $successMessage = ''): JsonResponse
    {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        if (isset($result['data'])) {
            $response = ['data' => $result['data']];
        } else {
            $response = ['success' => $result['success']];
        };
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        return response()->json($response, 200);
    }
}
