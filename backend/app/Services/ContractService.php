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
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', int $perPage = 10): array
    {
        try {
            \DB::enableQueryLog();
            $query = Contract::with(['user', 'room', 'booking']);

            // Apply search filter
           if ($querySearch) {
                $querySearch = trim($querySearch); // Loại bỏ khoảng trắng thừa
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
            \Log::info('SQL Query', \DB::getQueryLog());
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

    // Tạo file PDF từ nội dung hợp đồng
    public function generateContractPdf(Contract $contract): array
    {
        try {
            $filename = 'contracts/hopdong-' . $contract->id . '-' . time() . '-' . uniqid() . '.pdf';

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
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'dpi' => 150,
                    'defaultPaperSize' => 'a4',
                    'fontHeightRatio' => 1.1,
                    'isFontSubsettingEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'chroot' => public_path(),
                    'enable_font_subsetting' => true,
                    'font_cache' => storage_path('fonts/'),
                    'fontDir' => storage_path('fonts/'),
                    'tempDir' => storage_path('app/dompdf/'),
                    'isUnicode' => true,
                    'enable_html5_parser' => true,
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

    /**
     * Chuẩn bị HTML content với CSS khớp với giao diện gốc - Fixed Vietnamese Font Issues
     */
    private function prepareHtmlContent(string $content): string
    {
        // CSS được thiết kế để khớp với giao diện generateContractContent và hỗ trợ tiếng Việt
        $css = '
        <style>
            @page {
                margin: 15mm 20mm;
                size: A4;
            }

            * {
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-size: 16px; /* Tăng từ 14px lên 16px */
                line-height: 1.6;
                color: #212529;
                background: white;
                padding: 0;
                margin: 0;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
            }

            .container-fluid {
                width: 100%;
                padding: 0;
            }

            .contract-document {
                max-width: 210mm;
                min-height: 297mm;
                background: white;
                font-size: 16px; /* Tăng từ 14px lên 16px */
                line-height: 1.6;
                padding: 15mm 20mm;
                margin: 0 auto;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
            }

            /* Typography - Sử dụng font hỗ trợ tiếng Việt */
            h1, h2, h3, h4, h5, h6 {
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-weight: bold;
                margin: 0;
            }

            h3 {
                font-size: 20px; /* Tăng từ 18px lên 20px */
                font-weight: bold;
                letter-spacing: 1px;
                margin: 0;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
            }

            p {
                margin-bottom: 0.5rem;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                line-height: 1.6;
                font-size: 16px; /* Tăng font size cho paragraph */
            }

            strong, b {
                font-weight: bold;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Đảm bảo strong text cũng có font size lớn */
            }

            u {
                text-decoration: underline;
            }

            em, i {
                font-style: italic;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px;
            }

            /* Layout utilities */
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

            /* Border utilities */
            .border {
                border: 1px solid #000;
            }

            .border-dark {
                border-color: #000;
            }

            .d-inline-block {
                display: inline-block;
            }

            /* Grid system for PDF */
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

            /* Form controls - chuyển đổi input thành text với gạch chân */
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
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng font size cho form control */
            }

            /* Header specific styles */
            .contract-document .text-center.mb-4 > div:first-child strong {
                font-size: 16px; /* Tăng từ 14px lên 16px */
                letter-spacing: 0.5px;
                font-weight: bold;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
            }

            .contract-document .text-center.mb-4 > div:nth-child(2) u strong {
                font-weight: bold;
                text-decoration: underline;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px;
            }

            .contract-document .text-center.mb-4 > div .my-4 h3 {
                font-size: 20px; /* Tăng từ 18px lên 20px */
                font-weight: bold;
                letter-spacing: 1px;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
            }

            /* Contract title box */
            .contract-document .text-end .border {
                display: inline-block;
                border: 1px solid #000;
                padding: 0.25rem 0.5rem;
                font-weight: bold;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng font size cho title box */
            }

            /* Content sections */
            .contract-content-section {
                margin-bottom: 1.5rem;
            }

            .contract-content-section p {
                margin-bottom: 0.5rem;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng font size cho content section */
            }

            .contract-content-section .ms-3 p {
                margin-left: 1rem;
                margin-bottom: 0.5rem;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px;
            }

            /* Party information styling */
            .party-section {
                margin-bottom: 1rem;
            }

            .party-section p {
                margin-bottom: 0.25rem;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng font size cho party section */
            }

            /* Signature section */
            .signature-row {
                margin-top: 3rem;
                padding-top: 1.5rem;
            }

            /* Input field replacement for PDF */
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
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng font size cho input */
            }

            /* Style for input placeholders in PDF */
            .input-placeholder {
                display: inline-block;
                border-bottom: 1px dotted #666;
                min-width: 150px;
                height: 22px; /* Tăng height để phù hợp với font lớn hơn */
                margin: 0 5px;
                vertical-align: bottom;
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px;
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

            /* Đảm bảo font nhất quán cho tất cả các phần tử */
            div, span, td, th, input, label, select, textarea {
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif !important;
                color: #212529;
                font-size: 16px; /* Tăng font size cho tất cả elements */
            }

            /* Loại bỏ box shadow để in sạch */
            .contract-document {
                box-shadow: none;
            }

            /* Đặc biệt xử lý các ký tự tiếng Việt */
            .vietnamese-text {
                font-family: "DejaVu Sans", "Arial Unicode MS", "Lucida Sans Unicode", sans-serif;
                font-size: 16px; /* Tăng từ 14px lên 16px */
                line-height: 1.6;
            }

            /* Các class size đặc biệt nếu cần điều chỉnh riêng */
            .font-large {
                font-size: 18px !important;
            }

            .font-extra-large {
                font-size: 20px !important;
            }

            .font-small {
                font-size: 14px !important;
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
     * Xử lý nội dung để tối ưu cho PDF - Cải thiện xử lý font
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

        // Thay thế font Times New Roman bằng DejaVu Sans trong inline styles
        $content = preg_replace(
            '/font-family:\s*["\']?Times New Roman["\']?[^;]*/i',
            'font-family: "DejaVu Sans", Arial, sans-serif',
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

}
