<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dịch vụ xử lý logic nghiệp vụ xác thực người dùng.
 */
class AuthService
{
    /**
     * Thử đăng nhập với thông tin xác thực.
     *
     * @param array $credentials Thông tin đăng nhập (email hoặc số điện thoại, mật khẩu)
     * @param bool $remember Tùy chọn "nhớ tôi"
     * @return array Kết quả đăng nhập với trạng thái và thông báo
     */
    public function attemptLogin(array $credentials, bool $remember = false): array
    {
        // Thử đăng nhập với thông tin xác thực và tùy chọn "nhớ tôi"
        if (Auth::attempt($credentials, $remember)) {
            // Lấy thông tin người dùng hiện tại
            $user = Auth::user();

            // Kiểm tra vai trò người dùng (chỉ admin được phép đăng nhập)
            if ($user->role == 'Người đăng ký' || $user->role == 'Người thuê') {
                // Đăng xuất nếu không phải admin
                Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản không có quyền admin'];
            }

            // Kiểm tra trạng thái tài khoản
            if ($user->status !== 'Hoạt động') {
                // Đăng xuất nếu tài khoản không hoạt động
                Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản của bạn hiện không hoạt động'];
            }

            // Trả về kết quả thành công nếu kiểm tra hợp lệ
            return ['success' => true, 'message' => 'Đăng nhập admin thành công'];
        }

        // Trả về lỗi nếu thông tin đăng nhập không chính xác
        return ['success' => false, 'message' => 'Thông tin đăng nhập không chính xác'];
    }
}
