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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        try {
            // Force logout any existing session first
            if (Auth::check()) {
                $this->authService->logout(Auth::user());
                Auth::logout();
            }

            // Clear any existing sessions
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $credentials = [$field => $request->username, 'password' => $request->password];

            $result = $this->authService->login($credentials);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], $result['status']);
            }

            return response()->json([
                'message' => 'Đăng nhập thành công',
                'data' => $result['data'],
            ])->withCookie($this->createAuthCookie($result['token']));

        } catch (\Exception $e) {
            Log::error('Login controller error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đăng nhập'], 500);
        }
    }

    /**
     * Register a new user and initiate email verification.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Force logout any existing session first
            if (Auth::check()) {
                $this->authService->logout(Auth::user());
                Auth::logout();
            }

            $payload = $request->except('password_confirmation');
            $result = $this->authService->register($payload);

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], $result['status']);
            }

            return response()->json([
                'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác minh tài khoản.',
                'data' => $result['data'] ?? [],
            ])->withCookie($this->createAuthCookie($result['token']));

        } catch (\Exception $e) {
            Log::error('Register controller error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đăng ký'], 500);
        }
    }

    /**
     * Retrieve authenticated user details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        try {
            // Kiểm tra xem người dùng có được xác thực không
            if (!Auth::check()) {
                return response()->json(['error' => 'Chưa đăng nhập'], 401);
            }

            $user = $request->user();
            // Chỉ trả về các trường cần thiết
            $userData = $user->only([
                'id',
                'name',
                'email',
                'phone',
                'avatar',
                'birthdate',
                'address',
                'role',
                'email_verified_at',
            ]);

            return response()->json(['data' => $userData ?? []]);
        } catch (\Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra', 'data' => []], 500);
        }
    }

    /**
     * Log out the authenticated user and clear session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Get user before logout
            $user = $request->user();

            // Revoke all tokens for this user
            if ($user) {
                $this->authService->logout($user);
            }

            // Force logout from auth guard
            Auth::logout();

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['message' => 'Đăng xuất thành công'])
                ->withCookie($this->clearAuthCookie());

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            // Even if there's an error, clear the cookie
            return response()->json(['message' => 'Đăng xuất thành công'])
                ->withCookie($this->clearAuthCookie());
        }
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
        $frontendUrl = 'http://127.0.0.1:3000/xac-minh-email';
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
            minutes: config('auth.token_expiration', 120),
            path: '/',
            domain: null,
            secure: env('APP_ENV') !== 'local',
            httpOnly: true,
            sameSite: 'Strict'
        );
    }

    /**
     * Clear authentication cookie.
     *
     * @return Cookie
     */
    protected function clearAuthCookie(): Cookie
    {
        return cookie(
            name: 'sanctum_token',
            value: '',
            minutes: -1,
            path: '/',
            domain: null,
            secure: env('APP_ENV') !== 'local',
            httpOnly: true,
            sameSite: 'Strict'
        );
    }
}
