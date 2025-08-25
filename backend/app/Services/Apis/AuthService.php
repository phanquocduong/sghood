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
 * Lớp dịch vụ xử lý logic nghiệp vụ xác thực cho người dùng.
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
     * Thử đăng nhập người dùng với thông tin đăng nhập.
     *
     * @param array $credentials Thông tin đăng nhập (email hoặc số điện thoại, mật khẩu)
     * @return array Kết quả đăng nhập với dữ liệu người dùng, token hoặc thông báo lỗi
     */
    public function login(array $credentials): array
    {
        try {
            // Đăng xuất trạng thái xác thực hiện tại để tránh xung đột
            Auth::logout();

            // Kiểm tra thông tin đăng nhập
            if (!Auth::attempt($credentials)) {
                return ['error' => 'Thông tin đăng nhập không chính xác', 'status' => 401];
            }

            /** @var User $user */
            $user = Auth::user();

            // Kiểm tra xem người dùng đã xác minh email chưa
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return ['error' => 'Vui lòng xác minh email trước khi đăng nhập', 'status' => 403];
            }

            // Kiểm tra trạng thái tài khoản
            if ($user->status !== 'Hoạt động') {
                Auth::logout();
                return ['error' => 'Tài khoản của bạn hiện không hoạt động', 'status' => 403];
            }

            // Xóa tất cả token hiện tại của người dùng để tránh xung đột
            $user->tokens()->delete();

            // Tạo token mới
            $token = $user->createToken('auth_token')->plainTextToken;

            // Trả về dữ liệu người dùng và token
            return [
                'data' => $user->fresh()->only($this->userFields),
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi đăng nhập', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'credentials' => array_keys($credentials) // Không ghi lại giá trị thực
            ]);
            return ['error' => 'Đã xảy ra lỗi khi đăng nhập', 'status' => 500];
        }
    }

    /**
     * Đăng ký người dùng mới và gửi email xác minh.
     *
     * @param array $payload Dữ liệu đăng ký (email, số điện thoại, mật khẩu, v.v.)
     * @return array Kết quả đăng ký với dữ liệu người dùng, token hoặc thông báo lỗi
     */
    public function register(array $payload): array
    {
        // Bắt đầu giao dịch cơ sở dữ liệu
        DB::beginTransaction();
        try {
            // Đăng xuất trạng thái xác thực hiện tại
            Auth::logout();

            // Mã hóa mật khẩu
            $payload['password'] = Hash::make($payload['password']);

            // Kiểm tra xem email hoặc số điện thoại đã được sử dụng chưa
            $existingUser = User::where('email', $payload['email'])
                ->orWhere('phone', $payload['phone'])
                ->first();

            if ($existingUser) {
                DB::rollBack();
                return ['error' => 'Email hoặc số điện thoại đã được sử dụng', 'status' => 422];
            }

            // Tạo người dùng mới
            $user = User::create($payload);

            // Xóa tất cả token hiện tại của người dùng
            $user->tokens()->delete();

            // Tạo token mới
            $token = $user->createToken('auth_token')->plainTextToken;

            // Gửi thông báo xác minh email
            $user->notify(new VerifyEmail());

            // Xác nhận giao dịch
            DB::commit();

            // Trả về dữ liệu người dùng và token
            return [
                'data' => $user->fresh()->only($this->userFields),
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            // Hủy giao dịch nếu có lỗi
            DB::rollBack();
            // Ghi log lỗi
            Log::error('Lỗi đăng ký', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => array_keys($payload) // Không ghi lại giá trị thực
            ]);
            return ['error' => 'Đã xảy ra lỗi khi đăng ký', 'status' => 500];
        }
    }

    /**
     * Đặt lại mật khẩu người dùng bằng dữ liệu đã xác thực.
     *
     * @param array $data Dữ liệu chứa số điện thoại và mật khẩu mới
     * @return array Kết quả đặt lại mật khẩu hoặc thông báo lỗi
     */
    public function resetPassword(array $data): array
    {
        try {
            // Tìm người dùng theo số điện thoại
            $user = User::where('phone', $data['phone'])->first();

            if (!$user) {
                return ['error' => 'Số điện thoại không tồn tại', 'status' => 404];
            }

            // Xóa tất cả token hiện tại của người dùng
            $user->tokens()->delete();

            // Cập nhật mật khẩu mới
            $user->forceFill(['password' => Hash::make($data['password'])])->save();
            event(new PasswordReset($user));

            return ['data' => []];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi đặt lại mật khẩu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $data['phone'] ?? 'unknown'
            ]);
            return ['error' => 'Đã xảy ra lỗi khi đặt lại mật khẩu', 'status' => 500];
        }
    }

    /**
     * Hủy tất cả token của người dùng khi đăng xuất.
     *
     * @param User $user Người dùng cần đăng xuất
     * @return void
     */
    public function logout(User $user): void
    {
        try {
            // Xóa tất cả token của người dùng
            $user->tokens()->delete();

            // Xóa toàn bộ session
            session()->flush();

            // Xóa cache liên quan đến người dùng
            Cache::forget('user_' . $user->id);

            // Ghi log thông báo đăng xuất thành công
            Log::info('Người dùng đăng xuất thành công', ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi đăng xuất', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown'
            ]);
            throw $e; // Ném lại ngoại lệ để debug nếu cần
        }
    }
}
