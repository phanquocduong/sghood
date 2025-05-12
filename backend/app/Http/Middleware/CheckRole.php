<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Lấy thông tin người dùng từ middleware FirebaseAuth
        $user = $request->attributes->get('firebase_user');

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 401);
        }

        // Kiểm tra xem vai trò của người dùng có nằm trong danh sách vai trò được phép không
        if (!in_array($user->role, $roles)) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        return $next($request);
    }
}
