<?php

namespace App\Services\Apis;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UserService
{
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null)
    {
        // Cập nhật thông tin cơ bản
        $user->update($data);

        // Xử lý avatar nếu có
        if ($avatar) {
            $manager = new ImageManager(new Driver());
            $filename = 'images/avatars/user-' . time() . '.' . 'webp';
            $image = $manager->read($avatar)->toWebp(quality: 85)->toString();
            Storage::disk('public')->put($filename, $image);

            $user->update(['avatar' => Storage::url($filename)]);
        }

        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Mật khẩu hiện tại không đúng.');
        }

        $user->update(['password' => Hash::make($newPassword)]);
        return $user;
    }
}
