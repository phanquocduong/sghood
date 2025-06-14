<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth as FirebaseAuth;

class FirebaseAuthService
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function verifyToken(string $idToken)
    {
        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            return $verifiedToken->claims()->get('phone_number');
        } catch (\Throwable $e) {
            throw new \Exception('Xác thực thất bại: ' . $e->getMessage());
        }
    }

    public function authenticate(string $idToken)
    {
        try {
            $phone = $this->verifyToken($idToken);
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return response()->json(['message' => 'Người dùng chưa tồn tại'], 200);
            }

            return response()->json(['message' => 'Đăng nhập thành công'])
                ->cookie('firebase_token', $idToken, 60, null, null, true, true, false, 'Strict');
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function register(string $idToken, array $data)
    {
        try {
            $phone = $this->verifyToken($idToken);
            $user = User::where('phone', $phone)->first();

            if ($user) {
                return response()->json(['error' => 'Số điện thoại đã được sử dụng.'], 400);
            }

            $user = User::create([
                'phone' => $phone,
                'name' => $data['name'],
                'email' => $data['email'],
                'birthdate' => $data['birthdate'],
            ]);

            return response()->json(['message' => 'Đăng ký thành công'])
                ->cookie('firebase_token', $idToken, 60, null, null, true, true, false, 'Strict');
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Đăng ký thất bại: ' . $e->getMessage()], 401);
        }
    }

    public function logout()
    {
        return response()->json(['message' => 'Đăng xuất thành công'])
            ->cookie('firebase_token', '', -1);
    }
}
