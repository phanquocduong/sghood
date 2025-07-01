<?php

namespace App\Services\Apis;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    public function __construct(
        private readonly IdentityDocumentService $identityDocumentService,
        private readonly NotificationService $notificationService,
    ) {}

    public function getSuccessMessage(string $oldStatus): string
    {
        return match ($oldStatus) {
            'Chờ xác nhận' => 'Hợp đồng đã được lưu và đang chờ duyệt',
            'Chờ chỉnh sửa' => 'Hợp đồng đã được chỉnh sửa và gửi lại để duyệt',
            default => 'Hợp đồng đã được cập nhật thành công',
        };
    }

    public function getUserContracts(): array
    {
        try {
            return Contract::where('user_id', Auth::id())
                ->select('id', 'room_id', 'start_date', 'end_date', 'status')
                ->with([
                    'room' => fn($query) => $query->select('id', 'name', 'motel_id')
                        ->with(['motel' => fn($query) => $query->select('id', 'name')]),
                    'room.mainImage' => fn($query) => $query->select('id', 'room_id', 'image_url')
                ])
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
                'signature' => $contract->signature,
                'status' => $contract->status,
                'file' => $contract->file ? url($contract->file) : null,
                'signed_at' => $contract->signed_at?->toDateTimeString(),
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
        return $this->identityDocumentService->extractIdentityImages($images);
    }

    public function saveContract(string $content, int $id): Contract
    {
        try {
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $oldStatus = $contract->status;

            $contract->update([
                'content' => $content,
                'status' => 'Chờ duyệt'
            ]);

            $this->notificationService->notifyContractForAdmins($contract, $oldStatus);

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function signContract(int $contractId, string $signature, string $content): Contract
    {
        try {
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $contractId)
                ->where('status', 'Chờ ký')
                ->firstOrFail();

            // Lưu chữ ký
            $signaturePath = $this->saveSignature($signature, $contractId);

            // Cập nhật hợp đồng
            $contract->update([
                'signature' => $signaturePath,
                'content' => $content,
                'status' => 'Chờ thanh toán tiền cọc',
                'signed_at' => now(),
            ]);

            // Thông báo admin
            $this->notificationService->notifyContractForAdmins($contract, 'Chờ ký');

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi ký hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $contractId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function saveSignature(string $signature, int $contractId): string
    {
        $signature = preg_replace('#^data:image/\w+;base64,#i', '', $signature);
        $signatureData = base64_decode($signature);

        $path = "images/signatures/contract-{$contractId}-" . time() . '.png';
        Storage::disk('private')->put($path, $signatureData);

        return $path;
    }

    public function generateAndSaveContractPdf(int $contractId): string
    {
        try {
            // Lấy thông tin hợp đồng
            $contract = Contract::where('id', $contractId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Lấy nội dung HTML của hợp đồng
            $content = $contract->content;

            // Tạo PDF từ nội dung HTML
            $pdf = Pdf::loadHTML($content);

            // Tạo tên file dựa trên contract_id và timestamp
            $fileName = "contracts/contract-{$contractId}-" . time() . '.pdf';
            $pdfPath = 'pdf/' . $fileName;

            // Lưu file PDF vào storage private
            Storage::disk('private')->put($pdfPath, $pdf->output());

            // Cập nhật field file trong contract
            $contract->update(['file' => $pdfPath]);

            return $pdfPath;
        } catch (\Throwable $e) {
            Log::error('Lỗi tạo và lưu PDF hợp đồng', [
                'contract_id' => $contractId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
