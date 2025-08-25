<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý các yêu cầu liên quan đến xác thực người dùng (đăng nhập, đăng xuất, lưu FCM token).
 */
class AuthController extends Controller
{
    /**
     * @var AuthService Dịch vụ xử lý logic xác thực
     */
    protected $authService;

    /**
     * Khởi tạo controller với dịch vụ xác thực.
     *
     * @param AuthService $authService Dịch vụ xử lý logic xác thực
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị form đăng nhập.
     *
     * @return \Illuminate\View\View Giao diện form đăng nhập
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Xử lý yêu cầu đăng nhập.
     *
     * @param LoginRequest $request Yêu cầu chứa thông tin đăng nhập đã xác thực
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse Phản hồi JSON hoặc chuyển hướng
     */
    public function login(LoginRequest $request)
    {
        // Lấy thông tin đăng nhập (tên đăng nhập và mật khẩu)
        $credentials = $request->only('username', 'password');
        // Xác định trường đăng nhập là email hoặc số điện thoại
        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials[$field] = $credentials['username'];
        unset($credentials['username']);

        // Kiểm tra tùy chọn "nhớ tôi"
        $remember = $request->has('remember');

        // Thử đăng nhập bằng dịch vụ xác thực
        $result = $this->authService->attemptLogin($credentials, $remember);

        // Xử lý phản hồi cho yêu cầu JSON
        if ($request->expectsJson()) {
            if ($result['success']) {
                // Tái tạo session sau khi đăng nhập thành công
                $request->session()->regenerate();
                return response()->json(['success' => true, 'message' => $result['message']]);
            }
            // Trả về lỗi nếu đăng nhập thất bại
            return response()->json(['success' => false, 'message' => $result['message']], 401);
        }

        // Xử lý phản hồi cho yêu cầu HTML
        if ($result['success']) {
            // Tái tạo session sau khi đăng nhập thành công
            $request->session()->regenerate();
            // Chuyển hướng đến dashboard với thông báo thành công
            return redirect()->route('dashboard')->with('success', $result['message']);
        }

        // Quay lại trang trước với thông báo lỗi và giữ lại dữ liệu đầu vào
        return back()->with('error', $result['message'])->withInput();
    }

    /**
     * Lưu FCM token để gửi thông báo đẩy.
     *
     * @param Request $request Yêu cầu chứa FCM token
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON với thông báo kết quả
     */
    public function saveFcmToken(Request $request): \Illuminate\Http\JsonResponse
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'fcm_token' => 'required|string' // FCM token là bắt buộc và phải là chuỗi
        ]);

        // Lấy thông tin người dùng hiện tại
        $user = Auth::user();
        if ($user) {
            // Cập nhật FCM token cho người dùng
            $user->update(['fcm_token' => $request->fcm_token]);
            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['success' => true, 'message' => 'FCM token saved successfully']);
        }

        // Trả về lỗi nếu người dùng chưa được xác thực
        return response()->json(['success' => false, 'message' => 'Người dùng chưa được xác thực'], 401);
    }

    /**
     * Đăng xuất người dùng.
     *
     * @param Request $request Yêu cầu chứa thông tin session
     * @return RedirectResponse Chuyển hướng đến trang đăng nhập
     */
    public function logout(Request $request): RedirectResponse
    {
        // Đăng xuất người dùng
        Auth::logout();
        // Hủy session hiện tại
        $request->session()->invalidate();
        // Tái tạo token session mới
        $request->session()->regenerateToken();
        // Chuyển hướng đến trang đăng nhập
        return redirect()->route('login');
    }
}
