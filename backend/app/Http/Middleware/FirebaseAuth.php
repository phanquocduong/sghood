<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Kreait\Firebase\Auth as FirebaseAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FirebaseAuth
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuthentication $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function handle(Request $request, Closure $next)
    {

        // Lấy token từ cookie
        $idToken = $request->cookie('firebase_token');

        if (!$idToken) {
            return response()->json(['error' => 'Không tìm thấy token'], 401);
        }

        try {
            // Xác minh token với Firebase
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            $phone = $verifiedToken->claims()->get('phone_number');

            // Tìm user trong database
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return response()->json(['error' => 'Người dùng không tồn tại'], 401);
            }

            // Đăng nhập user vào session của Laravel
            Auth::login($user);

            // Lưu thông tin user vào request
            $request->attributes->set('firebase_user', $user);

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Token không hợp lệ: ' . $e->getMessage()], 401);
        }
    }
}
