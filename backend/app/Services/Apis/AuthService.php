<?php
namespace App\Services\Apis;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login(array $credentials): array
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user instanceof User) {
                if (!$user->hasVerifiedEmail()) {
                    return ['error' => 'Vui lòng xác minh email trước khi đăng nhập', 'status' => 403];
                }
                $token = $user->createToken('auth_token')->plainTextToken;
            }

            return [
                'data' => $user,
                'token' => $token
            ];
        }

        return ['error' => 'Thông tin đăng nhập không chính xác', 'status' => 401];
    }

    public function register(array $payload): array
    {
        DB::beginTransaction();
        try {
            $payload['password'] = Hash::make($payload['password']);
            $user = User::create($payload);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Send email verification notification
            $user->notify(new VerifyEmail());

            DB::commit();
            return [
                'data' => $user,
                'token' => $token
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi đăng ký', 'status' => 500];
        }
    }
}
