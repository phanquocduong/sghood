<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function attemptLogin(array $credentials, bool $remember = false): array
    {
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->role == 'Người đăng ký' || $user->role == 'Người thuê') {
                Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản không có quyền admin'];
            }

            if ($user->status !== 'Hoạt động') {
                Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản của bạn hiện không hoạt động'];
            }

            return ['success' => true, 'message' => 'Đăng nhập admin thành công'];
        }

        return ['success' => false, 'message' => 'Thông tin đăng nhập không chính xác'];
    }
}
