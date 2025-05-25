<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để truy cập admin');
        }

        $user = Auth::user();
        if ($user->role !== 'Quản trị viên') {
            return redirect()->route('login')->with('error', 'Tài khoản không có quyền admin');
        }

        if ($user->status !== 'Hoạt động') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tài khoản của bạn hiện không hoạt động');
        }

        return $next($request);
    }
}
