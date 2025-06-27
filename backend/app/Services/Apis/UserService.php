<?php

namespace App\Services\Apis;

use App\Models\User;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Crypt;

class UserService
{
    private const IDENTITY_FIELDS = [
        'full_name' => '',
        'birthdate' => '',
        'identity_number' => '',
        'date_of_issue' => '',
        'place_of_issue' => '',
        'permanent_address' => '',
        'identity_images' => '',
        'has_valid' => false,
    ];

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

    public function extractAndSaveIdentityImages(User $user, array $images): array
    {
        if (count($images) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        $identityDocument = self::IDENTITY_FIELDS;
        $imageAnnotator = new ImageAnnotatorClient();
        $identityDocumentPaths = [];

        foreach ($images as $index => $imageFile) {
            $tempPath = $imageFile->store('images/temp', 'private');
            $fullTempPath = storage_path('app/private/' . $tempPath);

            try {
                $imageContent = file_get_contents($fullTempPath);
                $visionImage = (new VisionImage())->setContent($imageContent);
                $feature = (new Feature())->setType(Feature\Type::TEXT_DETECTION);
                $request = (new AnnotateImageRequest())->setImage($visionImage)->setFeatures([$feature]);
                $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$request]);

                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $annotations = $response->getResponses()[0]->getTextAnnotations();

                if (empty($annotations)) {
                    throw new \Exception('Ảnh CCCD không hợp lệ.');
                }

                $text = $annotations[0]->getDescription();

                if (str_contains($text, 'Họ và tên') || str_contains($text, 'Full name')) {
                    $frontData = $this->parseCccdFrontText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($frontData));
                } elseif (str_contains($text, 'Ngày, tháng, năm') || str_contains($text, 'Cục Trưởng')) {
                    $backData = $this->parseCccdBackText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($backData));
                }

                $filename = "images/identity_document/user-{$user->id}-" . time() . "-{$index}.webp.enc";
                $imageContent = (new ImageManager(new Driver()))
                    ->read($imageFile)
                    ->toWebp(quality: 85)
                    ->toString();
                $encryptedContent = Crypt::encrypt($imageContent);
                Storage::disk('private')->put($filename, $encryptedContent);
                $identityDocumentPaths[] = $filename;
            } catch (\Throwable $e) {
                Log::error('Lỗi Google Vision API', ['error' => $e->getMessage()]);
                throw new \Exception('Lỗi phân tích ảnh CCCD.');
            } finally {
                Storage::disk('private')->delete($tempPath);
            }
        }

        $requiredFields = ['identity_number', 'full_name', 'birthdate', 'date_of_issue'];
        if (array_diff($requiredFields, array_keys(array_filter($identityDocument)))) {
            foreach ($identityDocumentPaths as $path) {
                Storage::disk('private')->delete($path);
            }
            throw new \Exception('Không thể trích xuất đầy đủ thông tin CCCD.');
        }

        if (!preg_match('/^\d{9}|\d{12}$/', $identityDocument['identity_number'])) {
            foreach ($identityDocumentPaths as $path) {
                Storage::disk('private')->delete($path);
            }
            throw new \Exception('Số CCCD không hợp lệ.');
        }

        $identityDocument['identity_images'] = implode('|', $identityDocumentPaths);

        $userData = [
            'name' => $identityDocument['full_name'],
            'identity_document' => $identityDocument['identity_images'],
            'address' => $identityDocument['permanent_address'] ?: $user->address,
        ];

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $identityDocument['birthdate'], $matches)) {
            $userData['birthdate'] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        $user->update($userData);
        $identityDocument['has_valid'] = true;

        return $identityDocument;
    }

    private function parseCccdFrontText(string $text): array
    {
        $data = [
            'identity_number' => '',
            'full_name' => '',
            'birthdate' => '',
            'permanent_address' => '',
        ];

        $lines = explode("\n", trim($text));
        $addressStarted = false;
        $addressLines = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (!$line) continue;

            if (preg_match('/\b\d{12}\b/', $line, $matches)) {
                $data['identity_number'] = $matches[0];
            }

            if (preg_match('/^(Họ và tên|Full name)[:\s]*/iu', $line) && isset($lines[$index + 1])) {
                $data['full_name'] = trim($lines[$index + 1]);
            }

            if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', $line, $matches)) {
                $data['birthdate'] = $matches[1];
            }

            if (preg_match('/^(Nơi thường trú|Place of residence)[:\s]*/iu', $line)) {
                $addressStarted = true;
            } elseif ($addressStarted && !preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/iu', $line)) {
                $addressLines[] = $line;
            } elseif ($addressStarted && preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/iu', $line)) {
                $addressStarted = false;
            }
        }

        $data['permanent_address'] = implode(', ', array_unique(array_filter($addressLines)));
        return $data;
    }

    private function parseCccdBackText(string $text): array
    {
        $data = [
            'date_of_issue' => '',
            'place_of_issue' => '',
        ];

        $lines = explode("\n", trim($text));

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (!$line) continue;

            if (preg_match('/^Ngày, tháng, năm[:\s]*(\d{2}\/\d{2}\/\d{4})/iu', $line, $matches)) {
                $data['date_of_issue'] = $matches[1];
            }

            if (preg_match('/^Cục Trưởng Cục Cảnh Sát/i', $line)) {
                $data['place_of_issue'] = $line;
            } elseif (preg_match('/^Cục Trưởng/i', $line) && isset($lines[$index + 1])) {
                $data['place_of_issue'] = trim($line . ' ' . $lines[$index + 1]);
            } elseif (preg_match('/^DIRECTOR GENERAL/i', $line)) {
                $data['place_of_issue'] = 'Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội';
            }
        }

        if (!$data['date_of_issue']) {
            foreach ($lines as $line) {
                if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', trim($line), $matches)) {
                    $data['date_of_issue'] = $matches[1];
                    break;
                }
            }
        }

        return array_filter($data);
    }
}
