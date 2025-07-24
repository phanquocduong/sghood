<?php

namespace App\Services\Apis;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Crypt;

class UserService
{
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        $user->update($data);

        if ($avatar) {
            $filename = 'images/avatars/user-' . time() . '.webp';
            $image = (new ImageManager(new Driver()))
                ->read($avatar)
                ->toWebp(quality: 85)
                ->toString();

            Storage::disk('public')->put($filename, $image);
            $user->update(['avatar' => '/storage/' . $filename]);
        }

        return $user;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): User
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Mật khẩu hiện tại không đúng.');
        }

        $user->update(['password' => Hash::make($newPassword)]);
        return $user;
    }

    public function updateFcmToken(User $user, string $fcmToken): void
    {
        $user->update(['fcm_token' => $fcmToken]);
    }

    public function extractAndSaveIdentityImages(User $user, array $images): void
    {
        $identityDocumentPaths = [];
        foreach ($images as $index => $imageFile) {
            $filename = "images/identity_document/user-{$user->id}-" . time() . "-{$index}.webp.enc";
            $imageContent = (new ImageManager(new Driver()))
                ->read($imageFile)
                ->toWebp(quality: 85)
                ->toString();
            $encryptedContent = Crypt::encrypt($imageContent);
            Storage::disk('private')->put($filename, $encryptedContent);
            $identityDocumentPaths[] = $filename;
        }
        $identityDocument = implode('|', $identityDocumentPaths);
        $user->update(['identity_document' => $identityDocument]);
    }
}
