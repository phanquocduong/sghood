<?php

namespace App\Services\Apis;

use App\Mail\ContractPendingEmail;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;

class ContractService
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

    public function getUserContracts(): array
    {
        try {
            return Contract::with(['room.motel', 'room.mainImage'])
                ->where('user_id', Auth::id())
                ->get()
                ->map(fn ($contract) => [
                    'id' => $contract->id,
                    'room_name' => $contract->room->name,
                    'motel_name' => $contract->room->motel->name,
                    'room_image' => $contract->room->mainImage->image_url,
                    'start_date' => $contract->start_date->toIso8601String(),
                    'end_date' => $contract->end_date->toIso8601String(),
                    'status' => $contract->status,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hợp đồng', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getContractDetail(int $id): array
    {
        try {
            $contract = Contract::where('id', $id)
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
                'otp_expires_at' => $contract->otp_expires_at?->toDateTimeString(),
                'signed_at' => $contract->signed_at?->toDateTimeString(),
                'created_at' => $contract->created_at->toDateTimeString(),
                'updated_at' => $contract->updated_at->toDateTimeString(),
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function rejectContract(int $id): array
    {
        try {
            $contract = Contract::where('id', $id)
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

            if ($contract->user->identity_document) {
                $paths = explode('|', $contract->user->identity_document);
                foreach ($paths as $path) {
                    Storage::disk('private')->delete($path);
                }
                $contract->user->update(['identity_document' => null]);
            }

            $contract->update(['status' => 'Huỷ bỏ']);

            return [
                'data' => $contract,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function extractIdentityImages(array $images): array
    {
        if (count($images) !== 2) {
            throw new \Exception('Vui lòng upload đúng 2 ảnh: mặt trước và mặt sau CCCD.');
        }

        $identityDocument = self::IDENTITY_FIELDS;
        $imageAnnotator = new ImageAnnotatorClient();

        foreach ($images as $imageFile) {
            $imagePath = $imageFile->store('images/temp', 'private');
            $fullImagePath = storage_path('app/private/' . $imagePath);

            try {
                $imageContent = file_get_contents($fullImagePath);
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
                    $identityDocument['year_of_birth'] = $frontData['birthdate']
                        ? date('Y', strtotime(str_replace('/', '-', $frontData['birthdate'])))
                        : $identityDocument['year_of_birth'];
                } elseif (str_contains($text, 'Ngày, tháng, năm') || str_contains($text, 'Cục Trưởng')) {
                    $backData = $this->parseCccdBackText($text);
                    $identityDocument = array_merge($identityDocument, array_filter($backData));
                }
            } catch (\Throwable $e) {
                Log::error('Lỗi Google Vision API', ['error' => $e->getMessage()]);
                throw new \Exception('Lỗi phân tích ảnh CCCD.');
            } finally {
                Storage::disk('private')->delete($imagePath);
            }
        }

        $requiredFields = ['identity_number', 'full_name', 'year_of_birth', 'date_of_issue'];
        if (array_diff($requiredFields, array_keys(array_filter($identityDocument)))) {
            throw new \Exception('Không thể trích xuất đầy đủ thông tin CCCD.');
        }

        if (!preg_match('/^\d{9}|\d{12}$/', $identityDocument['identity_number'])) {
            throw new \Exception('Số CCCD không hợp lệ.');
        }

        $identityDocument['has_valid'] = true;
        return $identityDocument;
    }

    public function saveContract(string $content, string $status, int $id): Contract
    {
        try {
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $oldStatus = $contract->status;

            $contract->update([
                'content' => $content,
                'status' => $status,
                'updated_at' => now()
            ]);

            // Log hoạt động
            Log::info('Hợp đồng được cập nhật', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            // Chỉ thông báo admin khi trạng thái chuyển sang "Chờ duyệt"
            if ($status === 'Chờ duyệt') {
                $this->notifyAdmins($contract, $oldStatus);
            }

            return $contract->fresh(); // Reload để lấy dữ liệu mới nhất

        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
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

    private function notifyAdmins(Contract $contract, string $oldStatus): void
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();

            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            // Điều chỉnh tiêu đề và nội dung thông báo dựa trên trạng thái cũ
            $title = $oldStatus === 'Chờ chỉnh sửa'
                ? 'Hợp đồng đã được chỉnh sửa'
                : 'Hợp đồng mới đang chờ duyệt';
            $body = $oldStatus === 'Chờ chỉnh sửa'
                ? "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được chỉnh sửa và gửi lại để duyệt."
                : "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được gửi để duyệt.";

            Mail::to($admins->pluck('email'))->send(new ContractPendingEmail($contract));

            $messaging = app('firebase.messaging');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                    ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi thông báo cho admin', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
