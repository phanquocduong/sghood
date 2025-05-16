<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse {
        $querySearch = $request->query('query', '');
        $status = $request->get('status', '');
        $sortOption = $request->query('sortOption', '');
        $perPage = $request->query('perPage', 25);

        $result = $this->userService->getAllUsers($querySearch, $status, $sortOption, $perPage);

        return $this->handleResponse($result);
    }

    public function show(int $id): JsonResponse {
        $result = $this->userService->getUser($id);
        return $this->handleResponse($result);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse {
        $imageFiles = [
            'avatar' => $request->file('avatar'),
            'front_id_card_image' => $request->file('front_id_card_image'),
            'back_id_card_image' => $request->file('back_id_card_image'),
        ];
        $result = $this->userService->update($id, $request->validated(), $imageFiles);

        return $this->handleResponse($result, 'Cập nhật người dùng thành công!');
    }

    private function handleResponse(array $result, string $successMessage = ''): JsonResponse {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $response = ['data' => $result['data']];
        if ($successMessage) {
            $response['message'] = $successMessage;
        }

        return response()->json($response, 200);
    }
}
