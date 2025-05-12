<?php
namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Auth as FirebaseAuth;

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

    public function authenticate(string $idToken) {
        try {
            $phone = $this->verifyToken($idToken);
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return ['error' => 'Người dùng chưa tồn tại', 'status' => 200];
            }

            return ['data' => $user];
        } catch (\Throwable $e) {
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

            return ['data' => $user];
        } catch (\Throwable $e) {
            return ['error' => 'Đăng ký thất bại: ' . $e->getMessage(), 'status' => 401];
        }
    }
}
