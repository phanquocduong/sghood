<?php
namespace App\Services\Apis;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Dịch vụ xử lý logic trích xuất thông tin từ ảnh căn cước công dân (CCCD) sử dụng Google Vision API.
 */
class IdentityDocumentService
{
    /**
     * Mảng định nghĩa các trường thông tin cần trích xuất từ CCCD.
     *
     * @var array
     */
    private const IDENTITY_FIELDS = [
        'full_name' => '', // Họ và tên
        'year_of_birth' => '', // Năm sinh
        'identity_number' => '', // Số định danh cá nhân
        'date_of_issue' => '', // Ngày cấp
        'place_of_issue' => '', // Nơi cấp
        'permanent_address' => '', // Nơi thường trú
        'has_valid' => false, // Trạng thái hợp lệ của thông tin
    ];

    /**
     * Trích xuất thông tin từ ảnh CCCD (mặt trước và mặt sau).
     *
     * @param array $images Mảng chứa các tệp ảnh CCCD
     * @return array Dữ liệu thông tin trích xuất hoặc ngoại lệ nếu có lỗi
     * @throws \Exception Nếu số lượng ảnh không đúng hoặc trích xuất thất bại
     */
    public function extractIdentityImages(array $images): array
    {
        // Kiểm tra số lượng ảnh tải lên (phải đúng 2 ảnh)
        if (count($images) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        // Khởi tạo mảng thông tin CCCD với giá trị mặc định
        $identityDocument = self::IDENTITY_FIELDS;
        // Khởi tạo client Google Vision API
        $imageAnnotator = new ImageAnnotatorClient();

        foreach ($images as $imageFile) {
            // Kiểm tra tính hợp lệ của tệp ảnh và định dạng (jpeg/png)
            if (!$imageFile->isValid() || !in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png'])) {
                throw new \Exception('Ảnh CCCD phải là định dạng JPEG hoặc PNG.');
            }

            // Lưu ảnh tạm thời vào thư mục private
            $imagePath = $imageFile->store('images/temp', 'private');
            $fullImagePath = storage_path('app/private/' . $imagePath);

            try {
                // Đọc nội dung ảnh
                $imageContent = file_get_contents($fullImagePath);
                // Tạo đối tượng VisionImage từ nội dung ảnh
                $visionImage = (new VisionImage())->setContent($imageContent);
                // Cấu hình tính năng nhận diện văn bản
                $feature = (new Feature())->setType(Feature\Type::DOCUMENT_TEXT_DETECTION);
                // Tạo yêu cầu cho Google Vision API
                $request = (new AnnotateImageRequest())->setImage($visionImage)->setFeatures([$feature]);
                $batchRequest = (new BatchAnnotateImagesRequest())->setRequests([$request]);

                // Gửi yêu cầu đến Google Vision API
                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $responses = $response->getResponses();

                // Kiểm tra phản hồi từ API
                if (empty($responses) || !isset($responses[0])) {
                    throw new \Exception('Không nhận được phản hồi từ Google Vision API.');
                }

                // Kiểm tra lỗi từ API
                if ($responses[0]->hasError()) {
                    throw new \Exception('Lỗi từ Google Vision API: ' . $responses[0]->getError()->getMessage());
                }

                // Lấy kết quả nhận diện văn bản
                $annotations = $responses[0]->getTextAnnotations();
                if (empty($annotations)) {
                    throw new \Exception('Không tìm thấy văn bản trong ảnh CCCD.');
                }

                // Lấy toàn bộ văn bản trích xuất
                $text = $annotations[0]->getDescription();

                // Kiểm tra xem ảnh là mặt trước hay mặt sau dựa trên nội dung văn bản
                if (str_contains($text, 'Họ và tên') || str_contains($text, 'Họ, chữ đệm và tên khai sinh') ||
                    str_contains($text, 'Full name') || str_contains($text, 'Số định danh cá nhân') ||
                    str_contains($text, 'Personal identification number') || str_contains($text, 'Số/ No.')) {
                    // Xử lý mặt trước CCCD
                    $frontData = $this->parseCccdFrontText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($frontData));
                    $identityDocument['year_of_birth'] = $frontData['birthdate']
                        ? date('Y', strtotime(str_replace('/', '-', $frontData['birthdate'])))
                        : $identityDocument['year_of_birth'];
                } elseif (str_contains($text, 'Ngày, tháng, năm cấp') || str_contains($text, 'Date of issue') ||
                        str_contains($text, 'Cục Trưởng') || str_contains($text, 'BỘ CÔNG AN') ||
                        str_contains($text, 'MINISTRY OF PUBLIC SECURITY') || str_contains($text, 'Đặc điểm nhân dạng') ||
                        str_contains($text, 'Nơi đăng ký khai sinh') || str_contains($text, 'Place of birth')) {
                    // Xử lý mặt sau CCCD
                    $backData = $this->parseCccdBackText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($backData));
                } else {
                    throw new \Exception('Không thể xác định mặt trước hoặc mặt sau của CCCD.');
                }
            } catch (\Throwable $e) {
                // Ghi log lỗi nếu có ngoại lệ xảy ra trong quá trình xử lý
                Log::error('Lỗi Google Vision API', ['error' => $e->getMessage(), 'text' => $text ?? '']);
                throw new \Exception('Lỗi phân tích ảnh CCCD: ' . $e->getMessage());
            } finally {
                // Xóa ảnh tạm thời sau khi xử lý
                Storage::disk('private')->delete($imagePath);
            }
        }

        // Kiểm tra các trường bắt buộc
        $requiredFields = ['identity_number', 'full_name', 'year_of_birth', 'date_of_issue'];
        if ($missingFields = array_diff($requiredFields, array_keys(array_filter($identityDocument)))) {
            Log::warning('Thiếu thông tin CCCD', ['missing_fields' => $missingFields]);
            throw new \Exception('Không thể trích xuất đầy đủ thông tin CCCD.');
        }

        // Kiểm tra định dạng số CCCD (9-12 số)
        if (!preg_match('/^\d{9,12}$/', $identityDocument['identity_number'])) {
            throw new \Exception('Số CCCD không hợp lệ.');
        }

        // Đánh dấu thông tin CCCD là hợp lệ
        $identityDocument['has_valid'] = true;
        return $identityDocument;
    }

    /**
     * Phân tích văn bản từ mặt trước CCCD để trích xuất thông tin.
     *
     * @param string $text Văn bản trích xuất từ ảnh
     * @return array Dữ liệu trích xuất (số CCCD, họ tên, ngày sinh, nơi thường trú)
     */
    private function parseCccdFrontText(string $text): array
    {
        // Khởi tạo mảng dữ liệu trích xuất
        $data = [
            'identity_number' => '',
            'full_name' => '',
            'birthdate' => '',
            'permanent_address' => '',
        ];

        // Tách văn bản thành các dòng
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

        // Gộp các dòng địa chỉ thành chuỗi duy nhất
        $data['permanent_address'] = implode(', ', array_unique(array_filter($addressLines)));
        return $data;
    }

    /**
     * Phân tích văn bản từ mặt sau CCCD để trích xuất thông tin.
     *
     * @param string $text Văn bản trích xuất từ ảnh
     * @return array Dữ liệu trích xuất (ngày cấp, nơi cấp, nơi cư trú)
     */
    private function parseCccdBackText(string $text): array
    {
        // Khởi tạo mảng dữ liệu trích xuất
        $data = [
            'date_of_issue' => '',
            'place_of_issue' => '',
            'permanent_address' => '',
        ];

        // Tách văn bản thành các dòng
        $lines = explode("\n", trim($text));
        $addressStarted = false;
        $addressLines = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (!$line) continue;

            // Tìm ngày cấp
            if (preg_match('/(Ngày, tháng, năm cấp|Ngày cấp|Issued on|Date of issue|Ngày, tháng, năm)\s*[:\/]?\s*(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/iu', $line, $matches)) {
                $data['date_of_issue'] = str_replace(['-', '.'], '/', $matches[2]);
            } elseif (preg_match('/(Ngày, tháng, năm|Date, month, year)\s*(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/iu', $line, $matches)) {
                // Xử lý trường hợp ngày dính với từ khóa
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
                // Tìm ngày với định dạng dd/mm/yyyy, dd-mm-yyyy, hoặc dd.mm.yyyy
                if (preg_match('/(\d{2}[-\/\.]\d{2}[-\/\.]\d{4})/', trim($line), $matches)) {
                    // Tránh lấy ngày hết hạn
                    if (!str_contains(strtoupper($line), 'CÓ GIÁ TRỊ ĐẾN') && !str_contains(strtoupper($line), 'DATE OF EXPIRY')) {
                        $data['date_of_issue'] = str_replace(['-', '.'], '/', $matches[1]);
                        break;
                    }
                }
            }
        }

        // Gộp các dòng địa chỉ thành chuỗi duy nhất
        $data['permanent_address'] = implode(', ', array_unique(array_filter($addressLines)));
        return $data;
    }
}
