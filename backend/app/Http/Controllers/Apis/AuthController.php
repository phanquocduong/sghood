<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Apis\AuthService;
use App\Services\Apis\FirebaseService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;
    protected $firebaseService;

    public function __construct(AuthService $authService, FirebaseService $firebaseService)
    {
        $this->authService = $authService;
        $this->firebaseService = $firebaseService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('username', 'password');
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$field] = $credentials['username'];
        unset($credentials['username']);

        $result = $this->authService->login($credentials);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'data' => $result['data'],
        ])->withCookie(cookie(
            name: 'sanctum_token',
            value: $result['token'],
            minutes: 60,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            sameSite: 'Strict'
        ));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $verifiedPhone = $this->firebaseService->verifyToken($request->id_token);

        if ($verifiedPhone !== $request->phone) {
            return response()->json(['error' => 'Số điện thoại không khớp với OTP'], 400);
        }

        $payload = $request->validated();
        unset($payload['password_confirmation']);

        $result = $this->authService->register($payload);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác minh tài khoản.',
            'data' => $result['data'],
        ])->withCookie(cookie(
            name: 'sanctum_token',
            value: $result['token'],
            minutes: 60,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            sameSite: 'Strict'
        ));
    }

    public function getUser(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return response()->json(['message' => 'Đăng xuất thành công'])
            ->withCookie(cookie()->forget('sanctum_token'));
    }

    public function verifyEmail(Request $request, $id, $hash): \Illuminate\Http\RedirectResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->to(config('app.frontend_url') . '/xac-minh-email?error=' . urlencode('Liên kết xác minh không hợp lệ'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->to(config('app.frontend_url') . '/xac-minh-email?message=' . urlencode('Email đã được xác minh'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->to(config('app.frontend_url') . '/xac-minh-email?message=' . urlencode('Xác minh email thành công'));
    }
}
