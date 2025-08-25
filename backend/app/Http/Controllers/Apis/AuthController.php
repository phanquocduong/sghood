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
 * API Controller xử lý các endpoint liên quan đến xác thực người dùng.
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    // Khởi tạo AuthService để sử dụng trong controller
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Xử lý đăng nhập người dùng bằng email hoặc số điện thoại và mật khẩu.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Kiểm tra và đăng xuất phiên hiện tại nếu người dùng đã đăng nhập
            if (Auth::check()) {
                $this->authService->logout(Auth::user());
                Auth::logout();
            }

            // Xóa toàn bộ session hiện tại và tạo lại token CSRF
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Xác định trường đăng nhập là email hay số điện thoại
            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $credentials = [$field => $request->username, 'password' => $request->password];

            // Thực hiện đăng nhập thông qua AuthService
            $result = $this->authService->login($credentials);

            // Kiểm tra nếu có lỗi trong quá trình đăng nhập
            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], $result['status']);
            }

            // Trả về phản hồi thành công với dữ liệu người dùng và cookie chứa token
            return response()->json([
                'message' => 'Đăng nhập thành công',
                'data' => $result['data'],
            ])->withCookie($this->createAuthCookie($result['token']));

        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi trong controller đăng nhập: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đăng nhập'], 500);
        }
    }

    /**
     * Đăng ký người dùng mới và gửi email xác minh.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Đăng xuất phiên hiện tại nếu đã đăng nhập
            if (Auth::check()) {
                $this->authService->logout(Auth::user());
                Auth::logout();
            }

            // Lấy dữ liệu từ request, bỏ qua trường xác nhận mật khẩu
            $payload = $request->except('password_confirmation');
            $result = $this->authService->register($payload);

            // Kiểm tra nếu có lỗi trong quá trình đăng ký
            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], $result['status']);
            }

            // Trả về phản hồi thành công với thông báo và cookie chứa token
            return response()->json([
                'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác minh tài khoản.',
                'data' => $result['data'] ?? [],
            ])->withCookie($this->createAuthCookie($result['token']));

        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi trong controller đăng ký: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi đăng ký'], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết của người dùng đã xác thực.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUser(Request $request): JsonResponse
    {
        try {
            // Kiểm tra xem người dùng có đăng nhập hay không
            if (!Auth::check()) {
                return response()->json(['error' => 'Chưa đăng nhập'], 401);
            }

            $user = $request->user();
            // Chỉ trả về các trường thông tin cần thiết của người dùng
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
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy thông tin người dùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra', 'data' => []], 500);
        }
    }

    /**
     * Đăng xuất người dùng và xóa session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Lấy thông tin người dùng trước khi đăng xuất
            $user = $request->user();

            // Hủy tất cả token của người dùng
            if ($user) {
                $this->authService->logout($user);
            }

            // Đăng xuất khỏi hệ thống xác thực
            Auth::logout();

            // Xóa session và tạo lại token CSRF
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Trả về phản hồi thành công và xóa cookie xác thực
            return response()->json(['message' => 'Đăng xuất thành công'])
                ->withCookie($this->clearAuthCookie());

        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi đăng xuất: ' . $e->getMessage());

            // Dù có lỗi, vẫn đảm bảo xóa cookie xác thực
            return response()->json(['message' => 'Đăng xuất thành công'])
                ->withCookie($this->clearAuthCookie());
        }
    }

    /**
     * Xác minh email người dùng bằng ID và hash.
     *
     * @param Request $request
     * @param int $id
     * @param string $hash
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        // Tìm người dùng theo ID
        $user = User::findOrFail($id);
        $frontendUrl = config('app.frontend_url') . '/xac-minh-email';

        // Kiểm tra tính hợp lệ của hash xác minh
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->to("{$frontendUrl}?error=" . urlencode('Liên kết xác minh không hợp lệ'));
        }

        // Kiểm tra xem email đã được xác minh chưa
        if ($user->hasVerifiedEmail()) {
            return redirect()->to("{$frontendUrl}?message=" . urlencode('Email đã được xác minh'));
        }

        // Đánh dấu email đã được xác minh và kích hoạt sự kiện xác minh
        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->to("{$frontendUrl}?message=" . urlencode('Xác minh email thành công'));
    }

    /**
     * Đặt lại mật khẩu người dùng bằng số điện thoại.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Gọi AuthService để xử lý logic đặt lại mật khẩu
        $result = $this->authService->resetPassword($request->validated());

        // Trả về phản hồi với thông báo thành công hoặc lỗi
        return $this->respondWithResult($result, 'Đặt lại mật khẩu thành công');
    }

    /**
     * Xử lý phản hồi API cho các trường hợp thành công hoặc lỗi.
     *
     * @param array $result
     * @param string $successMessage
     * @param callable|null $cookieCallback
     * @return JsonResponse
     */
    protected function respondWithResult(array $result, string $successMessage, ?callable $cookieCallback = null): JsonResponse
    {
        // Kiểm tra nếu có lỗi trong kết quả
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        // Tạo phản hồi JSON với thông báo thành công
        $response = response()->json([
            'message' => $successMessage,
            'data' => $result['data'] ?? [],
        ]);

        // Thêm cookie nếu có callback được cung cấp
        return $cookieCallback ? $response->withCookie($cookieCallback($result)) : $response;
    }

    /**
     * Tạo cookie xác thực với các thiết lập cấu hình.
     *
     * @param string $token
     * @return Cookie
     */
    protected function createAuthCookie(string $token): Cookie
    {
        return cookie(
            name: 'sanctum_token', // Tên cookie
            value: $token, // Giá trị token
            minutes: config('auth.token_expiration', 120), // Thời gian hết hạn
            path: '/', // Đường dẫn áp dụng cookie
            domain: null, // Không giới hạn domain
            secure: env('APP_ENV') !== 'local', // Chỉ dùng HTTPS ở môi trường production
            httpOnly: true, // Chỉ truy cập qua HTTP
            sameSite: 'Strict' // Ngăn chặn CSRF
        );
    }

    /**
     * Xóa cookie xác thực.
     *
     * @return Cookie
     */
    protected function clearAuthCookie(): Cookie
    {
        return cookie(
            name: 'sanctum_token', // Tên cookie
            value: '', // Giá trị rỗng để xóa
            minutes: -1, // Hết hạn ngay lập tức
            path: '/', // Đường dẫn áp dụng cookie
            domain: null, // Không giới hạn domain
            secure: env('APP_ENV') !== 'local', // Chỉ dùng HTTPS ở môi trường production
            httpOnly: true, // Chỉ truy cập qua HTTP
            sameSite: 'Strict' // Ngăn chặn CSRF
        );
    }
}
