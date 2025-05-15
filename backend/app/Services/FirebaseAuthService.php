<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseAuthService
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth) {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function verifyToken(string $idToken) {
        if (empty($idToken)) {
            throw new \InvalidArgumentException('ID Token không được để trống');
        }

        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            return $verifiedToken->claims()->get('phone_number');
        } catch (\Throwable $e) {
            throw new \Exception('Xác thực thất bại: ' . $e->getMessage());
        }
    }

    protected function setCustomClaims(string $uid, array $claims) {
        try {
            $this->firebaseAuth->setCustomUserClaims($uid, $claims);
            Log::info("Custom claims set for user {$uid}: ", $claims);
        } catch (FirebaseException $e) {
            Log::error("Failed to set custom claims for user {$uid}: " . $e->getMessage());
            throw new \Exception('Không thể thiết lập vai trò người dùng: ' . $e->getMessage());
        }
    }

    public function authenticate(string $idToken, string $type) {
        try {
            $phone = $this->verifyToken($idToken);
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return ['error' => 'Người dùng chưa tồn tại', 'status' => 200];
            }

            if ($type === 'admin' && $user->role !== 'Quản trị viên') {
                return ['error' => 'Chỉ Quản trị viên được phép đăng nhập', 'status' => 403];
            }

            // Lấy UID từ token
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            $uid = $verifiedToken->claims()->get('sub');

            // Thêm role vào Custom Claims
            $this->setCustomClaims($uid, ['role' => $user->role]);

            return [
                'data' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->role
                ]
            ];
        } catch (\Throwable $e) {
            Log::error('Authentication failed: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'status' => 401];
        }
    }

    public function register(string $idToken, array $data) {
        try {
            $phone = $this->verifyToken($idToken);
            $user = User::where('phone', $phone)->first();

            if ($user) {
                return ['error' => 'Số điện thoại đã được sử dụng.', 'status' => 400];
            }

            $user = User::create([
                'phone' => $phone,
                'name' => $data['name'],
                'email' => $data['email'],
                'birthdate' => $data['birthdate'],
            ]);

            // Lấy UID từ token
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            $uid = $verifiedToken->claims()->get('sub');

            // Thêm role vào Custom Claims
            $this->setCustomClaims($uid, ['role' => $user->role]);

            return ['data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role
            ]];
        } catch (\Throwable $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return ['error' => 'Đăng ký thất bại: ' . $e->getMessage(), 'status' => 401];
        }
    }
}
