<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\RegisterRequest;
use App\Http\Requests\Apis\ResetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\Apis\AuthService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * API Controller for handling authentication-related endpoints.
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authenticate user with email/phone and password.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$field => $request->username, 'password' => $request->password];

        $result = $this->authService->login($credentials);

        return $this->respondWithResult(
            $result,
            'Đăng nhập thành công',
            fn($result) => $this->createAuthCookie($result['token'])
        );
    }

    /**
     * Register a new user and initiate email verification.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->except('password_confirmation');
        $result = $this->authService->register($payload);

        return $this->respondWithResult(
            $result,
            'Đăng ký thành công. Vui lòng kiểm tra email để xác minh tài khoản.',
            fn($result) => $this->createAuthCookie($result['token'])
        );
    }

    /**
     * Retrieve authenticated user details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user() ?? []]);
    }

    /**
     * Log out the authenticated user and clear session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        if ($user = $request->user()) {
            $this->authService->logout($user);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Đăng xuất thành công'])
            ->withCookie(cookie()->forget('sanctum_token'));
    }

    /**
     * Verify user email with ID and hash.
     *
     * @param Request $request
     * @param int $id
     * @param string $hash
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        $frontendUrl = config('app.frontend_url') . '/xac-minh-email';

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->to("{$frontendUrl}?error=" . urlencode('Liên kết xác minh không hợp lệ'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->to("{$frontendUrl}?message=" . urlencode('Email đã được xác minh'));
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->to("{$frontendUrl}?message=" . urlencode('Xác minh email thành công'));
    }

    /**
     * Reset user password using phone number.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request->validated());

        return $this->respondWithResult($result, 'Đặt lại mật khẩu thành công');
    }

    /**
     * Handle API response for success or error cases.
     *
     * @param array $result
     * @param string $successMessage
     * @param callable|null $cookieCallback
     * @return JsonResponse
     */
    protected function respondWithResult(array $result, string $successMessage, ?callable $cookieCallback = null): JsonResponse
    {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $response = response()->json([
            'message' => $successMessage,
            'data' => $result['data'] ?? [],
        ]);

        return $cookieCallback ? $response->withCookie($cookieCallback($result)) : $response;
    }

    /**
     * Create authentication cookie with configurable settings.
     *
     * @param string $token
     * @return Cookie
     */
    protected function createAuthCookie(string $token): Cookie
    {
        return cookie(
            name: 'sanctum_token',
            value: $token,
            minutes: config('auth.token_expiration', 60),
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            sameSite: 'Strict'
        );
    }
}
