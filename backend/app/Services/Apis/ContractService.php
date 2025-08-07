<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendContractNotification;
use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    public function __construct(
        private readonly IdentityDocumentService $identityDocumentService,
    ) {}

    public function getUserContracts(): array
    {
        try {
            return Contract::query()
                ->where('user_id', Auth::id())
                ->select('id', 'room_id', 'start_date', 'end_date', 'status', 'deposit_amount', 'rental_price', 'signed_at', 'early_terminated_at')
                ->with([
                    'room' => fn($query) => $query->select('id', 'name', 'motel_id', 'price')
                        ->with(['motel' => fn($query) => $query->select('id', 'name', 'slug')]),
                    'invoices' => fn($query) => $query->select('id', 'contract_id')
                        ->where('type', 'Đặt cọc'),
                    'extensions' => fn($query) => $query->select('id', 'contract_id', 'status')
                        ->latest(),
                    'checkouts' => fn($query) => $query->select(
                        'id',
                        'contract_id',
                        'check_out_date',
                        'canceled_at',
                        'has_left'
                    )->latest(),
                ])
                ->get()
                ->map(fn (Contract $contract) => [
                    'id' => $contract->id,
                    'room_name' => $contract->room->name,
                    'room_price' => $contract->room->price,
                    'motel_name' => $contract->room->motel->name,
                    'motel_slug' => $contract->room->motel->slug,
                    'room_image' => $contract->room->main_image->image_url,
                    'start_date' => $contract->start_date->toDateString(),
                    'end_date' => $contract->end_date->toDateString(),
                    'status' => $contract->status,
                    'deposit_amount' => $contract->deposit_amount,
                    'rental_price' => $contract->rental_price,
                    'signed_at' => $contract->signed_at?->toDateTimeString(),
                    'early_terminated_at' => $contract->early_terminated_at?->toDateTimeString(),
                    'invoice_id' => $contract->invoices->first()?->id,
                    'latest_extension_status' => $contract->extensions->first()?->status,
                    'latest_checkout_status' => $contract->checkouts->first()?->canceled_at,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function getContractDetail(int $id): array
    {
        try {
            $contract = Contract::query()
                ->with(['user', 'extensions'])
                ->where('id', $id)
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
                'user_phone' => $contract->user->phone,
                'active_extensions' => $contract->extensions
                    ->filter(fn($ext) => $ext->status === 'Hoạt động')
                    ->map(fn($ext) => [
                        'id' => $ext->id,
                        'new_end_date' => $ext->new_end_date->toIso8601String(),
                        'new_rental_price' => $ext->new_rental_price,
                        'content' => $ext->content,
                        'file' => $ext->file ? url($ext->file) : null,
                        'status' => $ext->status,
                    ])->values()->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelContract(int $id): array
    {
        try {
            $contract = Contract::query()
                ->where('id', $id)
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

            $contract->update(['status' => 'Huỷ bỏ']);

            SendContractNotification::dispatch(
                $contract,
                'canceled',
                "Hợp đồng #{$contract->id} đã bị hủy",
                "Người dùng {$contract->user->name} đã hủy hợp đồng #{$contract->id}."
            );

            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function saveContract(string $content, int $id, bool $bypassExtract = false): Contract
    {
        try {
            $contract = Contract::query()
                ->where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            $oldStatus = $contract->status;

            // Đặt trạng thái dựa trên bypassExtract
            $newStatus = $bypassExtract ? 'Chờ duyệt thủ công' : 'Chờ duyệt';

            $contract->update([
                'content' => $content,
                'status' => $newStatus
            ]);

            $type = $bypassExtract ? 'bypass_pending' : ($oldStatus === 'Chờ xác nhận' ? 'pending' : 'updated');
            $title = $bypassExtract
                ? "Hợp đồng #{$contract->id} đang chờ duyệt thủ công"
                : ($oldStatus === 'Chờ xác nhận'
                    ? "Hợp đồng mới #{$contract->id} đang chờ duyệt"
                    : "Hợp đồng #{$contract->id} đã được chỉnh sửa");
            $body = $bypassExtract
                ? "Người dùng {$contract->user->name} đã nhập thông tin CCCD trực tiếp và gửi hợp đồng #{$contract->id} để duyệt thủ công."
                : ($oldStatus === 'Chờ xác nhận'
                    ? "Người dùng {$contract->user->name} đã gửi hợp đồng #{$contract->id} để duyệt."
                    : "Người dùng {$contract->user->name} đã chỉnh sửa hợp đồng #{$contract->id}.");

            SendContractNotification::dispatch($contract, $type, $title, $body);

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    public function signContract(int $contractId, string $signature, string $content): Contract
    {
        try {
            $contract = Contract::query()
                ->where('user_id', Auth::id())
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

            SendContractNotification::dispatch(
                $contract,
                'signed',
                "Hợp đồng #{$contract->id} đã được ký",
                "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được ký và đang chờ thanh toán tiền cọc."
            );

            return $contract->fresh();
        } catch (\Throwable $e) {
            Log::error('Lỗi ký hợp đồng:' . $e->getMessage());
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

    public function earlyTermination(int $id): array
    {
        try {
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền kết thúc sớm',
                    'status' => 404,
                ];
            }

            if ($contract->end_date <= now()) {
                return [
                    'error' => 'Hợp đồng đã hết hạn, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            $existingCheckout = $contract->checkouts()
                ->where('canceled_at', '==', NULL)
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            $contract->update([
                'status' => 'Kết thúc sớm',
                'early_terminated_at' => now(),
            ]);

            SendContractNotification::dispatch(
                $contract,
                'early_terminated',
                "Hợp đồng #{$contract->id} đã được kết thúc sớm",
                "Người dùng {$contract->user->name} đã kết thúc sớm hợp đồng #{$contract->id}."
            );

            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            Log::error('Lỗi kết thúc hợp đồng sớm:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Chuẩn bị nội dung HTML với CSS hỗ trợ tiếng Việt
     */
    private function prepareHtmlContent(string $content): string
    {
        // CSS được thiết kế để hỗ trợ tiếng Việt và đảm bảo định dạng A4
        $css = '
        <style>
            @page { margin: 15mm 20mm; size: A4;}
            @page { @top { content: ""; height: 15mm; } }
            * { font-family: "Noto Serif", "DejaVu Serif", serif; box-sizing: border-box; margin: 0; padding: 0; }
            body { font-size: 12px; line-height: 1.4; color: #212529; background: white; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-document { max-width: 210mm; min-height: 297mm; background: white; font-size: 12px; line-height: 1.4; padding: 15mm 20mm; margin: 0 auto; font-family: "Noto Serif", "DejaVu Serif", serif; }
            h1, h2, h3, h4, h5, h6 { font-family: "Noto Serif", "DejaVu Serif", serif; font-weight: bold; margin: 0; }
            h3 { font-size: 16px; font-weight: bold; letter-spacing: 0.3px; margin: 0; font-family: "Noto Serif", "DejaVu Serif", serif; }
            p { margin-bottom: 0.4rem; font-family: "Noto Serif", "DejaVu Serif", serif; line-height: 1.4; }
            strong, b { font-weight: bold; font-family: "Noto Serif", "DejaVu Serif", serif; }
            u { text-decoration: underline; }
            em, i { font-style: italic; font-family: "Noto Serif", "DejaVu Serif", serif;}
            .text-center { text-align: center; }
            .text-end { text-align: right; }
            .text-justify { text-align: justify; }
            .mb-0 { margin-bottom: 0; }
            .mb-1 { margin-bottom: 0.25rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mb-3 { margin-bottom: 1rem; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mb-5 { margin-bottom: 3rem; }
            .mt-3 { margin-top: 1rem; }
            .mt-5 { margin-top: 3rem; }
            .page-break { margin-top: 3rem; }
            .my-4 { margin-top: 1.5rem; margin-bottom: 1.5rem; }
            .pt-3 { padding-top: 1rem; }
            .pt-4 { padding-top: 1.5rem; }
            .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
            .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
            .ms-3 { margin-left: 1rem; }
            .border { border: 1px solid #000; }
            .border-dark { border-color: #000; }
            .d-inline-block { display: inline-block; }
            .row { display: table; width: 100%; margin-bottom: 1rem; }
            .col-2 { display: table-cell; width: 16.66%; vertical-align: top; padding-right: 15px; }
            .col-6 { display: table-cell; width: 50%; vertical-align: top; padding-right: 15px; }
            .col-10 { display: table-cell; width: 83.33%; vertical-align: top; padding-right: 15px; }
            .col-6:last-child, .col-2:last-child { padding-right: 0; padding-left: 15px; }
            .form-control.flat-line { border: none; border-bottom: 1px dotted #666; border-radius: 0; outline: none; background: transparent; height: auto; box-shadow: none; font-weight: bold; display: inline-block; vertical-align: bottom; margin: 0 0 5px 0; padding: 0 0 2px 0; min-width: 100px; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-document .text-center.mb-4 > div:first-child strong { font-size: 12px; letter-spacing: 0.2px; font-weight: bold; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-document .text-center.mb-4 > div:nth-child(2) u strong { font-weight: bold; text-decoration: underline; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-document .text-center.mb-4 > div .my-4 h3 { font-size: 16px; font-weight: bold; letter-spacing: 0.3px; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-document .text-end .border { display: inline-block; border: 1px solid #000; padding: 0.2rem 0.4rem; font-weight: bold; font-size: 11px; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-content-section p { margin-bottom: 0.4rem; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .contract-content-section .ms-3 p { margin-left: 1rem; margin-bottom: 0.4rem; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .party-section p { margin-bottom: 0.2rem; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .signature-row { margin-top: 3rem; padding-top: 1.5rem; }
            input[type="text"], .form-control { border: none; border-bottom: 1px dotted #666; background: transparent; font-weight: bold; display: inline-block; vertical-align: bottom; margin: 0 5px; padding: 0 0 2px 0; outline: none; box-shadow: none; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .input-placeholder { display: inline-block; border-bottom: 1px dotted #666; min-width: 150px; height: 22px; margin: 0 5px; vertical-align: bottom; font-family: "Noto Serif", "DejaVu Serif", serif; }
            .input-placeholder.wide { min-width: 300px; }
            .input-placeholder.medium { min-width: 200px; }
            .input-placeholder.small { min-width: 100px; }
            div, span, td, th, input, label, select, textarea { font-family: "Noto Serif", "DejaVu Serif", serif !important; color: #212529; }
            .contract-document { box-shadow: none; }
            .vietnamese-text { font-family: "Noto Serif", "DejaVu Serif", serif; }
        </style>';

        // Xử lý content để tạo giao diện tương tự
        $processedContent = $this->processContent($content);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Hợp đồng</title>
            {$css}
        </head>
        <body class="vietnamese-text">
            {$processedContent}
        </body>
        </html>
        HTML;
    }

    /**
     * Xử lý nội dung để tối ưu cho PDF
     */
    private function processContent(string $content): string
    {
        // Loại bỏ các thẻ PHP và script
        $content = preg_replace('/<\?php.*?\?>/s', '', $content);
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        // Đảm bảo encoding UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        // Xử lý HTML entities
        if (strpos($content, '&') !== false) {
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Xử lý escaped HTML
        if (preg_match('/<[^>]+>/', $content) && strpos($content, '<div class=') !== false) {
            $content = htmlspecialchars_decode($content, ENT_QUOTES);
        }

        // Đảm bảo font Noto Serif
        $content = preg_replace(
            '/font-family:\s*["\']?[^"\']*["\']?[^;]*/i',
            'font-family: "Noto Serif", "DejaVu Serif", serif',
            $content
        );

        // Loại bỏ style có thể gây lỗi
        $content = preg_replace('/style\s*=\s*["\']([^"\']*max-width:[^"\']*)["\']/', '', $content);
        $content = preg_replace('/box-shadow:\s*[^;]+;?/', '', $content);

        // Loại bỏ style tag để tránh conflict
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);

        // Đảm bảo ký tự tiếng Việt hiển thị đúng
        $content = preg_replace_callback('/[\x{00A0}-\x{FFFF}]/u', function ($matches) {
            return $matches[0];
        }, $content);

        return $content;
    }

    /**
     * Tạo và lưu file PDF hợp đồng
     */
    public function generateAndSaveContractPdf(int $contractId): array
    {
        try {
            // Lấy thông tin hợp đồng
            $contract = Contract::query()
                ->where('id', $contractId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Kiểm tra xem file PDF đã tồn tại chưa
            $filename = 'pdf/contracts/contract-' . $contract->id . '-' . time() . '.pdf';

            if ($contract->file && Storage::disk('private')->exists($contract->file)) {
                return ['data' => $contract->file];
            }

            // Kiểm tra nội dung hợp đồng
            if (!$contract->content) {
                return ['error' => 'Nội dung hợp đồng không tồn tại'];
            }

            // Chuẩn bị HTML content
            $htmlContent = $this->prepareHtmlContent($contract->content);

            // Tạo PDF
            $pdf = Pdf::loadHTML($htmlContent)
                ->setOptions([
                    'defaultFont' => 'Noto Serif',
                    'fontCache' => storage_path('fonts/'),
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'dpi' => 96,
                    'isFontSubsettingEnabled' => true,
                ]);

            Storage::disk('private')->put($filename, $pdf->output());
            $contract->update(['file' => $filename]);

            return ['data' => $filename];
        } catch (\Throwable $e) {
            Log::error('Lỗi tạo và lưu PDF hợp đồng:' . $e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo file PDF: ' . $e->getMessage()];
        }
    }
}
