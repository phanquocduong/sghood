<?php
namespace App\Services\Apis;

use App\Models\Contract;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractPendingEmail;
use App\Models\User;
use App\Notifications\ContractPendingNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Support\Facades\Notification;

class ContractService
{
    public function getUserContracts()
    {
        try {
            $contracts = Contract::with([
                'room.motel',
                'room.mainImage',
            ])
                ->where('user_id', Auth::id())
                ->get()
                ->map(function ($contract) {
                    return [
                        'id' => $contract->id,
                        'room_name' => $contract->room->name,
                        'motel_name' => $contract->room->motel->name,
                        'room_image' => $contract->room->mainImage->image_url,
                        'start_date' => $contract->start_date->toIso8601String(),
                        'end_date' => $contract->end_date->toIso8601String(),
                        'status' => $contract->status,
                    ];
                });

            return $contracts->toArray();
        } catch (\Throwable $e) {
            Log::error('Error in getUserContracts: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }

    public function getContractDetail($contractId)
    {
        try {
            $contract = Contract::where('id', $contractId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền truy cập',
                    'status' => 404,
                ];
            }

            return [
                'id' => $contract->id,
                'room_id' => $contract->room_id,
                'user_id' => $contract->user_id,
                'booking_id' => $contract->booking_id,
                'start_date' => $contract->start_date->toIso8601String(),
                'end_date' => $contract->end_date->toIso8601String(),
                'rental_price' => $contract->rental_price,
                'deposit_amount' => $contract->deposit_amount,
                'content' => $contract->content,
                'status' => $contract->status,
                'file' => $contract->file ? url($contract->file) : null,
                'otp_code' => $contract->otp_code,
                'otp_expires_at' => $contract->otp_expires_at ? $contract->otp_expires_at->toDateTimeString() : null,
                'signed_at' => $contract->signed_at ? $contract->signed_at->toDateTimeString() : null,
                'created_at' => $contract->created_at->toDateTimeString(),
                'updated_at' => $contract->updated_at->toDateTimeString(),
            ];
        } catch (\Throwable $e) {
            Log::error('Error in getContractDetail: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }

    public function rejectContract($contractId)
    {
        try {
            $contract = Contract::where('id', $contractId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền hủy',
                    'status' => 404,
                ];
            }

            if ($contract->status !== 'Chờ xác nhận') {
                return [
                    'error' => 'Hợp đồng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            $contract->update([
                'status' => 'Huỷ bỏ',
            ]);

            return [
                'data' => $contract,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Error in rejectContract: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'user_id' => Auth::id(),
            ]);
            throw $e;
        }
    }

    public function extractIdentityImages(array $identityImages)
    {
        // Kiểm tra số lượng ảnh (phải có 2 ảnh: mặt trước và mặt sau)
        if (count($identityImages) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        $identityDocument = [
            'full_name' => '',
            'year_of_birth' => '',
            'identity_number' => '',
            'date_of_issue' => '',
            'place_of_issue' => '',
            'permanent_address' => '',
            'has_valid' => false
        ];

        $imageAnnotator = new ImageAnnotatorClient();

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
                    $identityDocument['year_of_birth'] = $frontData['birthdate'] ? date('Y', strtotime(str_replace('/', '-', $frontData['birthdate']))) : $identityDocument['year_of_birth'];
                    $identityDocument['permanent_address'] = $frontData['permanent_address'] ?: $identityDocument['permanent_address'];
                } elseif (strpos($extractedText, 'Ngày, tháng, năm') !== false || strpos($extractedText, 'Cục Trưởng') !== false) {
                    $backData = $this->parseCccdBackText($extractedText);
                    $identityDocument['date_of_issue'] = $backData['date_of_issue'] ?: $identityDocument['date_of_issue'];
                    $identityDocument['place_of_issue'] = $backData['place_of_issue'] ?: $identityDocument['place_of_issue'];
                }

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
            empty($identityDocument['year_of_birth']) ||
            empty($identityDocument['date_of_issue'])
        ) {
            throw new \Exception('Không thể trích xuất đầy đủ thông tin từ CCCD. Vui lòng thử lại với ảnh rõ nét hơn.');
        }

        // Kiểm tra định dạng số CCCD
        if (!preg_match('/^\d{9}|\d{12}$/', $identityDocument['identity_number'])) {
            throw new \Exception('Số CCCD không hợp lệ. Vui lòng kiểm tra lại.');
        }

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


    public function saveContract($content, $status)
    {
        $contract = Contract::updateOrCreate(
            ['user_id' => Auth::id(), 'id' => request()->route('id')],
            [
                'content' => $content,
                'status' => $status,
            ]
        );

        // Gửi email thông báo cho admin
        $adminEmail = config('mail.admin_email');
        Mail::to($adminEmail)->send(new ContractPendingEmail($contract));

        // Gửi thông báo Firebase cho admin
        $admin = User::where('email', $adminEmail)->first();
        if ($admin && $admin->fcm_token) {
            Notification::send($admin, new ContractPendingNotification($contract));
        }

        return $contract;
    }
}
