<?php
namespace App\Services;

use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractRevisionNotification;
use App\Mail\ContractSignNotification;
use App\Mail\ContractConfirmNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', int $perPage = 10): array
    {
        try {
            DB::enableQueryLog();
            $query = Contract::with(['user', 'room', 'booking']);

            // Apply search filter
           if ($querySearch) {
                $querySearch = trim($querySearch);
                $query->where(function ($q) use ($querySearch) {
                    $q->orWhereHas('user', function ($userQuery) use ($querySearch) {
                        $userQuery->where('name', 'like', "%{$querySearch}%");
                    })
                    ->orWhereHas('room', function ($roomQuery) use ($querySearch) {
                        $roomQuery->where('name', 'like', "%{$querySearch}%");
                    });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);
            Log::info('SQL Query', DB::getQueryLog());
            return ['data' => $contracts];
        } catch (\Throwable $e) {
            Log::error('Error getting contracts: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'per_page' => $perPage
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng', 'status' => 500];
        }
    }

    public function getContractById(int $id): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Error getting contract by ID: ' . $e->getMessage(), [
                'contract_id' => $id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy thông tin hợp đồng', 'status' => 500];
        }
    }

    public function updateContractStatus(int $id, string $status): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            $oldStatus = $contract->status;

            Log::info('Updating contract status', [
                'contract_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            $contract->update(['status' => $status]);
            $contract->refresh();

            // Tự động tạo PDF khi trạng thái chuyển thành "Hoạt động"
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                $this->generateContractPdf($contract);
            }

            // Gửi email thông báo khi trạng thái chuyển thành "Chờ chỉnh sửa"
            if ($status === 'Chờ chỉnh sửa' && $oldStatus !== 'Chờ chỉnh sửa') {
                $this->sendContractRevisionEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng cần chỉnh sửa',
                    'content' => 'Hợp đồng của bạn cần chỉnh sửa. Vui lòng kiểm tra email để biết chi tiết.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract revision', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Gửi email thông báo khi trạng thái chuyển thành "Chờ ký"
            if ($status === 'Chờ ký' && $oldStatus !== 'Chờ ký') {
                $this->sendContractSignEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng cần ký',
                    'content' => 'Hợp đồng của bạn cần ký. Vui lòng kiểm tra email để biết chi tiết.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract sign', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Gửi email thông báo khi trạng thái chuyển thành "Hoạt động"
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                $this->sendContractConfirmEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng đã được xác nhận',
                    'content' => 'Hợp đồng của bạn đã được xác nhận và đang hoạt động.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract confirmation', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cập nhật trạng thái hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $id,
                'status' => $status
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái hợp đồng', 'status' => 500];
        }
    }

    // Gửi email thông báo khi hợp đồng cần chỉnh sửa
    private function sendContractRevisionEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email sửa đổi - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractRevisionNotification($contract));

            Log::info('Email sửa đổi hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email sửa đổi hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Gửi email thông báo khi hợp đồng cần ký
    private function sendContractSignEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email đăng nhập - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractSignNotification($contract));

            Log::info('Email ký hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email ký hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Gửi email thông báo khi hợp đồng đã được xác nhận
    private function sendContractConfirmEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email xác nhận - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractConfirmNotification($contract));

            Log::info('Email xác nhận hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email xác nhận hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Tạo file PDF từ nội dung hợp đồng với font hỗ trợ tốt tiếng Việt
    private function prepareHtmlContent(string $content): string
    {
        // CSS được thiết kế để khớp với giao diện generateContractContent và sử dụng Noto Serif
        $css = '
        <style>
            @page {
                margin: 15mm 20mm;
                size: A4;
            }

            /* Đảm bảo khoảng cách đầu trang giống nhau trên mọi trang */
            @page {
                @top {
                    content: "";
                    height: 15mm; /* Đảm bảo khoảng cách 15mm từ mép trên */
                }
            }

            * {
                font-family: "Noto Serif", "DejaVu Serif", serif;
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-size: 12px;
                line-height: 1.4;
                color: #212529;
                background: white;
                padding: 0;
                margin: 0;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .container-fluid {
                width: 100%;
                padding: 0;
            }

            .contract-document {
                max-width: 210mm;
                min-height: 297mm;
                background: white;
                font-size: 12px;
                line-height: 1.4;
                padding: 15mm 20mm;
                margin: 0 auto;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: "Noto Serif", "DejaVu Serif", serif;
                font-weight: bold;
                margin: 0;
            }

            h3 {
                font-size: 16px;
                font-weight: bold;
                letter-spacing: 0.3px;
                margin: 0;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            p {
                margin-bottom: 0.4rem;
                font-family: "Noto Serif", "DejaVu Serif", serif;
                line-height: 1.4;
            }

            strong, b {
                font-weight: bold;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            u {
                text-decoration: underline;
            }

            em, i {
                font-style: italic;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .text-center {
                text-align: center;
            }

            .text-end {
                text-align: right;
            }

            .text-justify {
                text-align: justify;
            }

            .mb-0 { margin-bottom: 0; }
            .mb-1 { margin-bottom: 0.25rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mb-3 { margin-bottom: 1rem; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mb-5 { margin-bottom: 3rem; }

            .mt-3 { margin-top: 1rem; }
            .mt-5 { margin-top: 3rem; }

            .my-4 {
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .pt-3 { padding-top: 1rem; }
            .pt-4 { padding-top: 1.5rem; }

            .px-2 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .py-1 {
                padding-top: 0.25rem;
                padding-bottom: 0.25rem;
            }

            .ms-3 {
                margin-left: 1rem;
            }

            .border {
                border: 1px solid #000;
            }

            .border-dark {
                border-color: #000;
            }

            .d-inline-block {
                display: inline-block;
            }

            .row {
                display: table;
                width: 100%;
                margin-bottom: 1rem;
            }

            .col-2 {
                display: table-cell;
                width: 16.66%;
                vertical-align: top;
                padding-right: 15px;
            }

            .col-6 {
                display: table-cell;
                width: 50%;
                vertical-align: top;
                padding-right: 15px;
            }

            .col-10 {
                display: table-cell;
                width: 83.33%;
                vertical-align: top;
                padding-right: 15px;
            }

            .col-6:last-child,
            .col-2:last-child {
                padding-right: 0;
                padding-left: 15px;
            }

            .form-control.flat-line {
                border: none;
                border-bottom: 1px dotted #666;
                border-radius: 0;
                outline: none;
                background: transparent;
                height: auto;
                box-shadow: none;
                font-weight: bold;
                display: inline-block;
                vertical-align: bottom;
                margin: 0 0 5px 0;
                padding: 0 0 2px 0;
                min-width: 100px;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-document .text-center.mb-4 > div:first-child strong {
                font-size: 12px;
                letter-spacing: 0.2px;
                font-weight: bold;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-document .text-center.mb-4 > div:nth-child(2) u strong {
                font-weight: bold;
                text-decoration: underline;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-document .text-center.mb-4 > div .my-4 h3 {
                font-size: 16px;
                font-weight: bold;
                letter-spacing: 0.3px;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-document .text-end .border {
                display: inline-block;
                border: 1px solid #000;
                padding: 0.2rem 0.4rem;
                font-weight: bold;
                font-size: 11px;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-content-section p {
                margin-bottom: 0.4rem;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .contract-content-section .ms-3 p {
                margin-left: 1rem;
                margin-bottom: 0.4rem;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .party-section p {
                margin-bottom: 0.2rem;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .signature-row {
                margin-top: 3rem;
                padding-top: 1.5rem;
            }

            input[type="text"], .form-control {
                border: none;
                border-bottom: 1px dotted #666;
                background: transparent;
                font-weight: bold;
                display: inline-block;
                vertical-align: bottom;
                margin: 0 5px;
                padding: 0 0 2px 0;
                outline: none;
                box-shadow: none;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .input-placeholder {
                display: inline-block;
                border-bottom: 1px dotted #666;
                min-width: 150px;
                height: 22px;
                margin: 0 5px;
                vertical-align: bottom;
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            .input-placeholder.wide {
                min-width: 300px;
            }

            .input-placeholder.medium {
                min-width: 200px;
            }

            .input-placeholder.small {
                min-width: 100px;
            }

            div, span, td, th, input, label, select, textarea {
                font-family: "Noto Serif", "DejaVu Serif", serif !important;
                color: #212529;
            }

            .contract-document {
                box-shadow: none;
            }

            .vietnamese-text {
                font-family: "Noto Serif", "DejaVu Serif", serif;
            }

            /* Đảm bảo nội dung đầu tiên trên mỗi trang mới có khoảng cách phù hợp */
            .contract-document > *:first-child {
                margin-top: 0;
            }
            .contract-document > div:not(:first-child) {
                margin-top: 15mm;
            }
        </style>';

        // Xử lý content để tạo giao diện tương tự generateContractContent
        $processedContent = $this->processContent($content);

        // Tạo HTML hoàn chỉnh với meta charset UTF-8
        $htmlContent = '<!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Hợp đồng cho thuê</title>
            ' . $css . '
        </head>
        <body class="vietnamese-text">';

        $htmlContent .= $processedContent;
        $htmlContent .= '</body></html>';

        return $htmlContent;
    }

    /**
     * Xử lý nội dung để tối ưu cho PDF - Đảm bảo hỗ trợ tiếng Việt
     */
    private function processContent(string $content): string
    {
        // Loại bỏ các thẻ PHP và script
        $content = preg_replace('/<\?php.*?\?>/s', '', $content);
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        // Đảm bảo encoding UTF-8 chính xác
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        // Xử lý HTML entities một cách cẩn thận
        if (strpos($content, '&') !== false) {
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Xử lý escaped HTML
        if (preg_match('/<[^>]+>/', $content) && strpos($content, '<div class=') !== false) {
            $content = htmlspecialchars_decode($content, ENT_QUOTES);
        }

        // Đảm bảo font Noto Serif với fallback DejaVu Serif
        $content = preg_replace(
            '/font-family:\s*["\']?[^"\']*["\']?[^;]*/i',
            'font-family: "Noto Serif", "DejaVu Serif", serif',
            $content
        );

        // Loại bỏ các style có thể gây lỗi trong PDF
        $content = preg_replace('/style\s*=\s*["\']([^"\']*max-width:[^"\']*)["\']/', '', $content);
        $content = preg_replace('/box-shadow:\s*[^;]+;?/', '', $content);

        // Loại bỏ style tag hiện tại để tránh conflict
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);

        // Đảm bảo các ký tự tiếng Việt được hiển thị đúng
        $content = preg_replace_callback('/[\x{00A0}-\x{FFFF}]/u', function ($matches) {
            return $matches[0]; // Giữ nguyên ký tự Unicode
        }, $content);

        return $content;
    }

    // Tạo file PDF từ nội dung hợp đồng
    public function generateContractPdf(Contract $contract): array
    {
        try {
            $filename = 'contracts/contract-' . $contract->id . '-' . time() . '-' . uniqid() . '.pdf';

            if ($contract->file && Storage::disk('public')->exists(str_replace('/storage/', '', $contract->file))) {
                Log::info('PDF file already exists for contract', ['contract_id' => $contract->id]);
                return ['data' => '/storage/' . str_replace('/storage/', '', $contract->file)];
            }

            if (!$contract->content) {
                return ['error' => 'Nội dung hợp đồng không tồn tại'];
            }

            // Debug: Log content trước khi xử lý
            Log::info('Original content preview', [
                'contract_id' => $contract->id,
                'content_length' => strlen($contract->content),
                'content_preview' => substr($contract->content, 0, 200)
            ]);

            // Chuẩn bị HTML content với CSS khớp giao diện gốc
            $htmlContent = $this->prepareHtmlContent($contract->content);

            // Debug: Log processed content
            Log::info('Processed HTML preview', [
                'contract_id' => $contract->id,
                'html_length' => strlen($htmlContent),
                'html_preview' => substr($htmlContent, 0, 500)
            ]);

            $pdf = Pdf::loadHTML($htmlContent)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Noto Serif',
                    'fontCache' => storage_path('fonts/'),
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'dpi' => 96,
                    'defaultPaperSize' => 'a4',
                    'fontHeightRatio' => 1.0,
                    'isFontSubsettingEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'chroot' => public_path(),
                    'enable_font_subsetting' => true,
                    'tempDir' => storage_path('app/dompdf/'),
                    'isUnicode' => true,
                    'enable_html5_parser' => true,
                    'enable_remote' => false,
                    'logOutputFile' => storage_path('logs/dompdf.log'),
                ]);

            $pdfContent = $pdf->output();
            Storage::disk('public')->put($filename, $pdfContent);

            $contract->update(['file' => $filename]);

            Log::info('PDF generated successfully', [
                'contract_id' => $contract->id,
                'file_name' => $filename,
                'file_size' => strlen($pdfContent)
            ]);

            return ['data' => '/storage/' . $filename];

        } catch (\Throwable $e) {
            Log::error('Error generating contract PDF: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Đã xảy ra lỗi khi tạo file PDF: ' . $e->getMessage()];
        }
    }

    // Tải file PDF hợp đồng
    public function downloadContractPdf(int $id): array
    {
        try {
            $contract = Contract::find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            if (!$contract->file) {
                $result = $this->generateContractPdf($contract);

                if (isset($result['error'])) {
                    return $result;
                }

                $contract->refresh();
            }

            if (!Storage::disk('public')->exists($contract->file)) {
                $result = $this->generateContractPdf($contract);

                if (isset($result['error'])) {
                    return $result;
                }

                $contract->refresh();
            }

            $filePath = Storage::disk('public')->path($contract->file);

            return [
                'data' => [
                    'file_path' => $filePath,
                    'file_name' => "Hop_dong_cho_thue_{$contract->id}.pdf",
                    'mime_type' => 'application/pdf',
                    'public_url' => '/storage/' . $contract->file
                ]
            ];

        } catch (\Throwable $e) {
            Log::error('Error downloading contract PDF: ' . $e->getMessage(), [
                'contract_id' => $id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi tải file PDF'];
        }
    }

    // Lấy hình ảnh căn cước công dân từ hợp đồng
    public function getIdentityDocument(int $contractId, string $imagePath): array
    {
        try {
            $contract = Contract::with('user')->find($contractId);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            if (!$contract->user || !$contract->user->identity_document) {
                return ['error' => 'Không tìm thấy hình ảnh căn cước công dân', 'status' => 404];
            }

            $imagePaths = explode('|', $contract->user->identity_document);
            $fullImagePath = 'images/identity_document/' . $imagePath;

            if (!in_array($fullImagePath, $imagePaths)) {
                return ['error' => 'Hình ảnh không hợp lệ', 'status' => 404];
            }

            $encryptedContent = Storage::disk('public')->get($fullImagePath);
            $decryptedContent = decrypt($encryptedContent);

            return [
                'data' => [
                    'content' => $decryptedContent,
                    'mime_type' => 'image/webp'
                ]
            ];

        } catch (\Throwable $e) {
            Log::error('Error retrieving identity document: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'image_path' => $imagePath
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy hình ảnh căn cước công dân', 'status' => 500];
        }
    }

}
