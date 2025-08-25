<?php

namespace App\Services\Apis;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Crypt;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến người dùng.
 */
class UserService
{
    /**
     * Cập nhật thông tin hồ sơ người dùng.
     *
     * @param User $user Mô hình người dùng
     * @param array $data Dữ liệu hồ sơ (tên, giới tính, ngày sinh, địa chỉ)
     * @param UploadedFile|null $avatar File ảnh đại diện
     * @return User Mô hình người dùng đã cập nhật
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        // Cập nhật thông tin hồ sơ từ dữ liệu đã xác thực
        $user->update($data);

        // Xử lý upload ảnh đại diện nếu có
        if ($avatar) {
            // Tạo tên file duy nhất cho ảnh đại diện
            $filename = 'images/avatars/user-' . time() . '.webp';
            // Chuyển đổi ảnh sang định dạng WebP với chất lượng 85
            $image = (new ImageManager(new Driver()))
                ->read($avatar)
                ->toWebp(quality: 85)
                ->toString();

            // Lưu ảnh vào storage
            Storage::disk('public')->put($filename, $image);
            // Cập nhật đường dẫn ảnh đại diện vào người dùng
            $user->update(['avatar' => '/storage/' . $filename]);
        }

        return $user;
    }

    /**
     * Đổi mật khẩu người dùng.
     *
     * @param User $user Mô hình người dùng
     * @param string $currentPassword Mật khẩu hiện tại
     * @param string $newPassword Mật khẩu mới
     * @return User Mô hình người dùng đã cập nhật
     * @throws \Exception Nếu mật khẩu hiện tại không đúng
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): User
    {
        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Mật khẩu hiện tại không đúng.');
        }

        // Cập nhật mật khẩu mới đã được mã hóa
        $user->update(['password' => Hash::make($newPassword)]);
        return $user;
    }

    /**
     * Cập nhật FCM token để gửi thông báo đẩy.
     *
     * @param User $user Mô hình người dùng
     * @param string $fcmToken FCM token mới
     * @return void
     */
    public function updateFcmToken(User $user, string $fcmToken): void
    {
        // Cập nhật FCM token vào người dùng
        $user->update(['fcm_token' => $fcmToken]);
    }

    /**
     * Lưu giấy tờ tùy thân của người dùng.
     *
     * @param User $user Mô hình người dùng
     * @param array $images Mảng các file ảnh giấy tờ tùy thân
     * @return void
     */
    public function saveIdentityDocument(User $user, array $images): void
    {
        // Mảng lưu đường dẫn các ảnh giấy tờ tùy thân
        $identityDocumentPaths = [];
        foreach ($images as $index => $imageFile) {
            // Tạo tên file duy nhất cho mỗi ảnh
            $filename = "images/identity_document/user-{$user->id}-" . time() . "-{$index}.webp.enc";
            // Chuyển đổi ảnh sang định dạng WebP với chất lượng 85
            $imageContent = (new ImageManager(new Driver()))
                ->read($imageFile)
                ->toWebp(quality: 85)
                ->toString();
            // Mã hóa nội dung ảnh
            $encryptedContent = Crypt::encrypt($imageContent);
            // Lưu ảnh mã hóa vào storage riêng tư
            Storage::disk('private')->put($filename, $encryptedContent);
            // Thêm đường dẫn vào mảng
            $identityDocumentPaths[] = $filename;
        }
        // Chuyển mảng đường dẫn thành chuỗi ngăn cách bởi |
        $identityDocument = implode('|', $identityDocumentPaths);
        // Cập nhật trường giấy tờ tùy thân của người dùng
        $user->update(['identity_document' => $identityDocument]);
    }
}
