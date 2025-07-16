<?php
namespace App\Services\Apis;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IdentityDocumentService
{
    private const IDENTITY_FIELDS = [
        'full_name' => '',
        'year_of_birth' => '',
        'identity_number' => '',
        'date_of_issue' => '',
        'place_of_issue' => '',
        'permanent_address' => '',
        'has_valid' => false,
    ];

    public function extractIdentityImages(array $images): array
    {
        if (count($images) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        $identityDocument = self::IDENTITY_FIELDS;
        $imageAnnotator = new ImageAnnotatorClient();

        foreach ($images as $imageFile) {
            if (!$imageFile->isValid() || !in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                throw new \Exception('Ảnh CCCD phải là định dạng JPEG hoặc PNG.');
            }

            $imagePath = $imageFile->store('images/temp', 'private');
            $fullImagePath = storage_path('app/private/' . $imagePath);

            try {
                $imageContent = file_get_contents($fullImagePath);
                $visionImage = (new VisionImage())->setContent($imageContent);
                $feature = (new Feature())->setType(Feature\Type::DOCUMENT_TEXT_DETECTION);
                $request = (new AnnotateImageRequest())->setImage($visionImage)->setFeatures([$feature]);
                $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$request]);

                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $responses = $response->getResponses();

                if (empty($responses) || !isset($responses[0])) {
                    throw new \Exception('Không nhận được phản hồi từ Google Vision API.');
                }

                if ($responses[0]->hasError()) {
                    throw new \Exception('Lỗi từ Google Vision API: ' . $responses[0]->getError()->getMessage());
                }

                $annotations = $responses[0]->getTextAnnotations();
                if (empty($annotations)) {
                    throw new \Exception('Không tìm thấy văn bản trong ảnh CCCD.');
                }

                $text = $annotations[0]->getDescription();
                Log::info('Văn bản trích xuất từ ảnh CCCD', ['text' => $text]);

                // Kiểm tra mặt trước
                if (str_contains($text, 'Họ và tên') || str_contains($text, 'Họ, chữ đệm và tên khai sinh') ||
                    str_contains($text, 'Full name') || str_contains($text, 'Số định danh cá nhân') ||
                    str_contains($text, 'Personal identification number') || str_contains($text, 'Số/ No.')) {
                    $frontData = $this->parseCccdFrontText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($frontData));
                    $identityDocument['year_of_birth'] = $frontData['birthdate']
                        ? date('Y', strtotime(str_replace('/', '-', $frontData['birthdate'])))
                        : $identityDocument['year_of_birth'];
                }
                // Kiểm tra mặt sau
                elseif (str_contains($text, 'Ngày, tháng, năm cấp') || str_contains($text, 'Date of issue') ||
                        str_contains($text, 'Cục Trưởng') || str_contains($text, 'BỘ CÔNG AN') ||
                        str_contains($text, 'MINISTRY OF PUBLIC SECURITY') || str_contains($text, 'Đặc điểm nhân dạng') ||
                        str_contains($text, 'Nơi đăng ký khai sinh') || str_contains($text, 'Place of birth')) {
                    $backData = $this->parseCccdBackText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($backData));
                } else {
                    throw new \Exception('Không thể xác định mặt trước hoặc mặt sau của CCCD.');
                }
            } catch (\Throwable $e) {
                Log::error('Lỗi Google Vision API', ['error' => $e->getMessage(), 'text' => $text ?? '']);
                throw new \Exception('Lỗi phân tích ảnh CCCD: ' . $e->getMessage());
            } finally {
                Storage::disk('private')->delete($imagePath);
            }
        }

        $requiredFields = ['identity_number', 'full_name', 'year_of_birth', 'date_of_issue'];
        if ($missingFields = array_diff($requiredFields, array_keys(array_filter($identityDocument)))) {
            Log::warning('Thiếu thông tin CCCD', ['missing_fields' => $missingFields]);
            throw new \Exception('Không thể trích xuất đầy đủ thông tin CCCD.');
        }

        if (!preg_match('/^\d{9,12}$/', $identityDocument['identity_number'])) {
            throw new \Exception('Số CCCD không hợp lệ.');
        }

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

            // Tìm số định danh cá nhân hoặc số CCCD
            if (preg_match('/\b\d{9,12}\b/', $line, $matches)) {
                $data['identity_number'] = $matches[0];
            }

            // Tìm họ và tên
            if (preg_match('/^(Họ và tên|Họ, chữ đệm và tên khai sinh|Full name)[\sI]*[:\s]*(.*)/iu', $line, $matches)) {
                $name = trim(str_replace(':', '', $matches[2]));
                if ($name && !str_contains($name, 'Họ, chữ đệm và tên khai sinh') && !str_contains($name, 'Full name')) {
                    $data['full_name'] = $name;
                } elseif (isset($lines[$index + 1])) {
                    $data['full_name'] = trim(str_replace(':', '', $lines[$index + 1]));
                }
            }

            // Tìm ngày sinh
            if (preg_match('/\b(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})\b/', $line, $matches)) {
                $data['birthdate'] = str_replace(['-', '.'], '/', $matches[1]);
            }

            // Tìm nơi thường trú
            if (preg_match('/^(Nơi thường trú|Place of residence|Nơi cư trú|Piece of residence)[:\s]*(.*)/iu', $line, $matches)) {
                $addressStarted = true;
                if ($matches[2]) {
                    $addressLines[] = trim(str_replace([':', 'I Piece of residence', 'I Place of residence', '/ Place of residence'], '', $matches[2]));
                }
            } elseif ($addressStarted && !preg_match('/^(Họ và tên|Họ, chữ đệm và tên khai sinh|Full name|Nơi thường trú|Place of residence|Nơi cư trú|Piece of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex|Có giá trị đến|Date of expiry)/iu', $line)) {
                $addressLines[] = $line;
            } elseif ($addressStarted && preg_match('/^(Họ và tên|Họ, chữ đệm và tên khai sinh|Full name|Nơi thường trú|Place of residence|Nơi cư trú|Piece of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex|Có giá trị đến|Date of expiry)/iu', $line)) {
                $addressStarted = false;
            }
        }

        $data['permanent_address'] = implode(', ', array_unique(array_filter($addressLines)));
        Log::info('Dữ liệu trích xuất từ mặt trước CCCD', ['frontData' => $data]);
        return $data;
    }

    private function parseCccdBackText(string $text): array
    {
        $data = [
            'date_of_issue' => '',
            'place_of_issue' => '',
            'permanent_address' => '',
        ];

        $lines = explode("\n", trim($text));
        $addressStarted = false;
        $addressLines = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (!$line) continue;

            // Log dòng để debug
            Log::debug('Xử lý dòng mặt sau CCCD', ['line' => $line]);

            // Tìm ngày cấp
            if (preg_match('/(Ngày, tháng, năm cấp|Ngày cấp|Issued on|Date of issue|Ngày, tháng, năm)\s*[:\/]?\s*(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/iu', $line, $matches)) {
                $data['date_of_issue'] = str_replace(['-', '.'], '/', $matches[2]);
            } elseif (preg_match('/(Ngày, tháng, năm|Date, month, year)\s*(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/iu', $line, $matches)) {
                // Xử lý trường hợp ngày dính với từ khóa (như year25/04/2021)
                $data['date_of_issue'] = str_replace(['-', '.'], '/', $matches[2]);
            }

            // Tìm nơi cấp
            if (preg_match('/(BỘ CÔNG AN|MINISTRY OF PUBLIC SECURITY)/iu', $line, $matches)) {
                $data['place_of_issue'] = strtoupper($matches[0]) === 'MINISTRY OF PUBLIC SECURITY' ? 'BỘ CÔNG AN' : $matches[0];
            } elseif (preg_match('/(Cục Trưởng|DIRECTOR GENERAL)/iu', $line)) {
                $data['place_of_issue'] = 'Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội';
            }

            // Tìm nơi cư trú
            if (preg_match('/^(Nơi cư trú|Piece of residence|Place of residence)[:\s]*(.*)/iu', $line, $matches)) {
                $addressStarted = true;
                if ($matches[2]) {
                    $addressLines[] = trim(str_replace([':', '/ Piece of residence', '/ Place of residence'], '', $matches[2]));
                }
            } elseif ($addressStarted && !preg_match('/^(Nơi cư trú|Piece of residence|Place of residence|Ngày, tháng, năm|Ngày, tháng, năm cấp|Ngày cấp|Issued on|Date of issue|Date, month, year|Nơi đăng ký khai sinh|Place of birth|BỘ CÔNG AN|MINISTRY OF PUBLIC SECURITY)/iu', $line)) {
                $addressLines[] = $line;
            } elseif ($addressStarted && preg_match('/^(Nơi cư trú|Piece of residence|Place of residence|Ngày, tháng, năm|Ngày, tháng, năm cấp|Ngày cấp|Issued on|Date of issue|Date, month, year|Nơi đăng ký khai sinh|Place of birth|BỘ CÔNG AN|MINISTRY OF PUBLIC SECURITY)/iu', $line)) {
                $addressStarted = false;
            }
        }

        // Thử tìm ngày cấp trong toàn bộ văn bản nếu không tìm thấy
        if (!$data['date_of_issue']) {
            foreach ($lines as $line) {
                // Tìm ngày với định dạng dd/mm/yyyy, dd-mm-yyyy, hoặc dd.mm.yyyy, kể cả khi dính với văn bản
                if (preg_match('/(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/', trim($line), $matches)) {
                    // Tránh lấy ngày hết hạn
                    if (!str_contains(strtoupper($line), 'CÓ GIÁ TRỊ ĐẾN') && !str_contains(strtoupper($line), 'DATE OF EXPIRY')) {
                        $data['date_of_issue'] = str_replace(['-', '.'], '/', $matches[1]);
                        break;
                    }
                }
            }
        }

        $data['permanent_address'] = implode(', ', array_unique(array_filter($addressLines)));
        Log::info('Dữ liệu trích xuất từ mặt sau CCCD', ['backData' => $data]);
        return $data;
    }
}
