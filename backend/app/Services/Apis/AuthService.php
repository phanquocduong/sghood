<?php

namespace App\Services\Apis;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;

/**
 * Service class for handling authentication business logic.
 */
class AuthService
{
    /**
     * Attempt user login with provided credentials.
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        try {
            if (!Auth::attempt($credentials)) {
                return ['error' => 'Thông tin đăng nhập không chính xác', 'status' => 401];
            }

            /** @var User $user */
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return ['error' => 'Vui lòng xác minh email trước khi đăng nhập', 'status' => 403];
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'data' => $user,
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            Log::error('Login Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['error' => 'Đã xảy ra lỗi khi đăng nhập', 'status' => 500];
        }
    }

    /**
     * Register a new user and send email verification.
     *
     * @param array $payload
     * @return array
     */
    public function register(array $payload): array
    {
        DB::beginTransaction();
        try {
            $payload['password'] = Hash::make($payload['password']);
            $user = User::create($payload);
            $token = $user->createToken('auth_token')->plainTextToken;

            $user->notify(new VerifyEmail());

            DB::commit();

            return [
                'data' => $user,
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['error' => 'Đã xảy ra lỗi khi đăng ký', 'status' => 500];
        }
    }

    /**
     * Reset user password using validated data.
     *
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data): array
    {
        try {
            $user = User::where('phone', $data['phone'])->first();

            if (!$user) {
                return ['error' => 'Số điện thoại không tồn tại', 'status' => 404];
            }

            $user->forceFill(['password' => Hash::make($data['password'])])->save();
            event(new PasswordReset($user));

            return ['data' => []];
        } catch (\Throwable $e) {
            Log::error('Reset Password Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['error' => 'Đã xảy ra lỗi khi đặt lại mật khẩu', 'status' => 500];
        }
    }

    /**
     * Revoke all tokens for the user during logout.
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
