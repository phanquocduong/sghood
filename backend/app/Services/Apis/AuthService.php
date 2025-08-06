<?php

namespace App\Services\Apis;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service class for handling authentication business logic.
 */
class AuthService
{
    /**
     * Các trường cần trả về trong phản hồi API.
     *
     * @var array
     */
    protected $userFields = [
        'id',
        'name',
        'email',
        'phone',
        'birthdate',
        'address',
        'avatar',
        'role',
        'email_verified_at',
    ];

    /**
     * Attempt user login with provided credentials.
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        try {
            // Clean up any existing auth state
            Auth::logout();

            if (!Auth::attempt($credentials)) {
                return ['error' => 'Thông tin đăng nhập không chính xác', 'status' => 401];
            }

            /** @var User $user */
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return ['error' => 'Vui lòng xác minh email trước khi đăng nhập', 'status' => 403];
            }

            // Delete all existing tokens for this user to prevent conflicts
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'data' => $user->fresh()->only($this->userFields), // Chỉ trả về các trường cần thiết
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            Log::error('Login Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'credentials' => array_keys($credentials) // Don't log actual values
            ]);
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
            // Clean up any existing auth state
            Auth::logout();

            $payload['password'] = Hash::make($payload['password']);

            // Check if user already exists
            $existingUser = User::where('email', $payload['email'])
                ->orWhere('phone', $payload['phone'])
                ->first();

            if ($existingUser) {
                DB::rollBack();
                return ['error' => 'Email hoặc số điện thoại đã được sử dụng', 'status' => 422];
            }

            $user = User::create($payload);

            // Delete any existing tokens for this user
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            $user->notify(new VerifyEmail());

            DB::commit();

            return [
                'data' => $user->fresh()->only($this->userFields), // Chỉ trả về các trường cần thiết
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Register Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => array_keys($payload) // Don't log actual values
            ]);
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

            // Delete all existing tokens for this user
            $user->tokens()->delete();

            $user->forceFill(['password' => Hash::make($data['password'])])->save();
            event(new PasswordReset($user));

            return ['data' => []];
        } catch (\Throwable $e) {
            Log::error('Reset Password Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $data['phone'] ?? 'unknown'
            ]);
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
        try {
            // Xóa tất cả token của người dùng
            $user->tokens()->delete();

            // Xóa toàn bộ session
            session()->flush();

            // Đảm bảo xóa cache liên quan đến user
            Cache::forget('user_' . $user->id);

            Log::info('User logged out successfully', ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            Log::error('Logout Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown'
            ]);
            throw $e; // Để debug lỗi nếu cần
        }
    }
}
