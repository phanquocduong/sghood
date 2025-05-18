<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\FirebaseAuthService;

class FirebaseAuthController extends Controller
{
    protected $authService;

    public function __construct(FirebaseAuthService $authService) {
        $this->authService = $authService;
    }

    public function auth(AuthRequest $request) {
        $type = $request->type ?? 'user';
        $result = $this->authService->authenticate($request->id_token, $type);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'data' => $result['data']
        ])->cookie('firebase_token', $request->id_token, 60, null, null, true, true, false, 'Strict');
    }

    public function register(RegisterRequest $request) {
        $result = $this->authService->register($request->id_token, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'message' => 'Đăng ký thành công',
            'data' => $result['data']
        ])->cookie('firebase_token', $request->id_token, 60, null, null, true, true, false, 'Strict');
    }

    public function logout() {
        return response()->json(['message' => 'Đăng xuất thành công'])
            ->cookie('firebase_token', '', -1);
    }
}
