<?php

namespace App\Services\Apis;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

class UserService
{
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null)
    {
        $user->update($data);

        if ($avatar) {
            $manager = new ImageManager(new Driver());
            $filename = 'images/avatars/user-' . time() . '.' . 'webp';
            $image = $manager->read($avatar)->toWebp(quality: 85)->toString();
            Storage::disk('public')->put($filename, $image);

            $user->update(['avatar' => '/storage/' . $filename]);
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

    public function updateFcmToken(User $user, $fcm_token) {
        $user->update(['fcm_token' => $fcm_token]);
    }

    public function extractAndSaveIdentityImages(User $user, array $identityImages)
    {
        // Kiểm tra số lượng ảnh (phải có 2 ảnh: mặt trước và mặt sau)
        if (count($identityImages) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        $identityDocument = [
            'full_name' => '',
            'birthdate' => '',
            'identity_number' => '',
            'date_of_issue' => '',
            'place_of_issue' => '',
            'permanent_address' => '',
            'identity_images' => '',
            'has_valid' => false
        ];

        $imageAnnotator = new ImageAnnotatorClient();
        $identityDocumentPaths = [];

        foreach ($identityImages as $index => $imageFile) {
            $imagePath = $imageFile->store('images/temp', 'private');
            $fullImagePath = storage_path('app/private/' . $imagePath);

            $imageContent = file_get_contents($fullImagePath);

            $image = (new VisionImage())->setContent($imageContent);
            $feature = (new Feature())->setType(Feature\Type::TEXT_DETECTION);
            $requestAnnotate = (new AnnotateImageRequest())->setImage($image)->setFeatures([$feature]);
            $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$requestAnnotate]);

            try {
                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $responses = $response->getResponses();

                if (empty($responses) || !isset($responses[0])) {
                    Storage::disk('private')->delete($imagePath);
                    throw new \Exception('Không thể phân tích ảnh. Vui lòng kiểm tra định dạng hoặc chất lượng ảnh.');
                }

                $annotations = $responses[0]->getTextAnnotations();

                if (empty($annotations) || !isset($annotations[0])) {
                    Storage::disk('private')->delete($imagePath);
                    throw new \Exception('Ảnh CCCD không hợp lệ.');
                }

                $extractedText = $annotations[0]->getDescription();

                if (strpos($extractedText, 'Họ và tên') !== false || strpos($extractedText, 'Full name') !== false) {
                    $frontData = $this->parseCccdFrontText($extractedText);
                    $identityDocument['identity_number'] = $frontData['identity_number'] ?: $identityDocument['identity_number'];
                    $identityDocument['full_name'] = $frontData['full_name'] ?: $identityDocument['full_name'];
                    $identityDocument['birthdate'] = $frontData['birthdate'] ?: $identityDocument['birthdate'];
                    $identityDocument['permanent_address'] = $frontData['permanent_address'] ?: $identityDocument['permanent_address'];
                } elseif (strpos($extractedText, 'Ngày, tháng, năm') !== false || strpos($extractedText, 'Cục Trưởng') !== false) {
                    $backData = $this->parseCccdBackText($extractedText);
                    $identityDocument['date_of_issue'] = $backData['date_of_issue'] ?: $identityDocument['date_of_issue'];
                    $identityDocument['place_of_issue'] = $backData['place_of_issue'] ?: $identityDocument['place_of_issue'];
                }

                $manager = new ImageManager(new Driver());
                $filename = 'images/identity_document/user-' . $user->id . '-' . time() . '-' . $index . '.webp';
                $image = $manager->read($imageFile)->toWebp(quality: 85);
                Storage::disk('private')->put($filename, $image->toString());
                $identityDocumentPaths[] = $filename;

                Storage::disk('private')->delete($imagePath);
            } catch (\Google\Cloud\Core\Exception\GoogleException $e) {
                Storage::disk('private')->delete($imagePath);
                Log::error('Google Vision API error: ' . $e->getMessage());
                throw new \Exception('Lỗi khi phân tích ảnh từ Google Vision API. Vui lòng thử lại.');
            }
        }

        // Kiểm tra tính hợp lệ của dữ liệu trích xuất
        if (
            empty($identityDocument['identity_number']) ||
            empty($identityDocument['full_name']) ||
            empty($identityDocument['birthdate']) ||
            empty($identityDocument['date_of_issue'])
        ) {
            foreach ($identityDocumentPaths as $path) {
                Storage::disk('private')->delete($path);
            }
            throw new \Exception('Không thể trích xuất đầy đủ thông tin từ CCCD. Vui lòng thử lại với ảnh rõ nét hơn.');
        }

        // Kiểm tra định dạng số CCCD
        if (!preg_match('/^\d{9}|\d{12}$/', $identityDocument['identity_number'])) {
            foreach ($identityDocumentPaths as $path) {
                Storage::disk('private')->delete($path);
            }
            throw new \Exception('Số CCCD không hợp lệ. Vui lòng kiểm tra lại.');
        }

        $identityDocument['identity_images'] = implode('|', $identityDocumentPaths);

        // Chuẩn bị dữ liệu để cập nhật vào user
        $userData = [
            'name' => $identityDocument['full_name'],
            'identity_document' => $identityDocument['identity_images'],
        ];

        // Chuyển date_of_birth từ "DD/MM/YYYY" thành "YYYY-MM-DD" cho kiểu DATE
        if (!empty($identityDocument['birthdate']) && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $identityDocument['birthdate'], $matches)) {
            $userData['birthdate'] = "{$matches[3]}-{$matches[2]}-{$matches[1]}"; // YYYY-MM-DD
        }

        // Cập nhật address nếu có
        if (!empty($identityDocument['permanent_address'])) {
            $userData['address'] = $identityDocument['permanent_address'];
        }

        $user->update($userData);

        $identityDocument['has_valid'] = true;
        return $identityDocument;
    }

    private function parseCccdFrontText($text)
    {
        $data = [
            'identity_number' => '',
            'full_name' => '',
            'birthdate' => '',
            'permanent_address' => '',
        ];

        $lines = preg_split('/\r\n|\r|\n/', $text);

        $addressStarted = false;
        $addressLines = [];
        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            if (preg_match('/\b\d{12}\b/', $trimmedLine, $matches)) {
                $data['identity_number'] = $matches[0];
            }
            if (preg_match('/^(Họ và tên|Full name)[:\s]*[:\s]*/iu', $trimmedLine) && isset($lines[$index + 1]) && trim($lines[$index + 1]) !== '') {
                $data['full_name'] = trim($lines[$index + 1]);
            }
            if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', $trimmedLine, $matches)) {
                $data['birthdate'] = $matches[1]; // Giữ nguyên định dạng DD/MM/YYYY
            }
            if (preg_match('/^(Nơi thường trú|Place of residence)[:\s]*[:\s]*/iu', $trimmedLine)) {
                $addressStarted = true;
            } elseif ($addressStarted && !preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/u', $trimmedLine)) {
                $addressLines[] = $trimmedLine;
            } elseif ($addressStarted && (preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/u', $trimmedLine) || empty($trimmedLine))) {
                $addressStarted = false;
            }
        }

        $data['permanent_address'] = implode(', ', array_filter(array_unique($addressLines)));
        return $data;
    }

    private function parseCccdBackText($text)
    {
        $data = [
            'date_of_issue' => '',
            'place_of_issue' => '',
        ];

        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            if (preg_match('/^Ngày, tháng, năm[:\s]*(\d{2}\/\d{2}\/\d{4})/iu', $trimmedLine, $matches)) {
                $data['date_of_issue'] = $matches[1];
            }

            if (preg_match('/^Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội/i', $trimmedLine)) {
                $data['place_of_issue'] = $trimmedLine;
            } elseif (preg_match('/^Cục Trưởng/i', $trimmedLine) && isset($lines[$index + 1]) && !empty(trim($lines[$index + 1]))) {
                $data['place_of_issue'] = trim($trimmedLine . ' ' . $lines[$index + 1]);
            } elseif (preg_match('/^DIRECTOR GENERAL FOR ADMINISTRATIVE MANAGEMENT OF SOCIAL ORDER/i', $trimmedLine)) {
                $data['place_of_issue'] = $trimmedLine;
            }
        }

        if (empty($data['date_of_issue'])) {
            foreach ($lines as $line) {
                if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', trim($line), $matches)) {
                    $data['date_of_issue'] = $matches[1];
                    break;
                }
            }
        }

        if (empty($data['place_of_issue'])) {
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if (preg_match('/^Cục Trưởng|Cục Cảnh Sát/i', $trimmedLine)) {
                    $data['place_of_issue'] = $trimmedLine;
                    break;
                } elseif (preg_match('/^DIRECTOR GENERAL/i', $trimmedLine)) {
                    $data['place_of_issue'] = 'Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội';
                    break;
                }
            }
        }

        return $data;
    }
}
