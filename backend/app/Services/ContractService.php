<?php
namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', int $perPage = 10): array
    {
        try {
            $query = Contract::with(['user', 'room', 'booking']);

            // Apply search filter
            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('content', 'like', "%$querySearch%")
                      ->orWhere('file', 'like', "%$querySearch%")
                      ->orWhereHas('user', function($userQuery) use ($querySearch) {
                          $userQuery->where('name', 'like', "%$querySearch%");
                      })
                      ->orWhereHas('room', function($roomQuery) use ($querySearch) {
                          $roomQuery->where('name', 'like', "%$querySearch%");
                      });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);
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

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Error updating contract status: ' . $e->getMessage(), [
                'contract_id' => $id,
                'status' => $status
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái hợp đồng', 'status' => 500];
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
                    'defaultFont' => 'DejaVu Sans', // Thay đổi font chính
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'dpi' => 150,
                    'defaultPaperSize' => 'a4',
                    'fontHeightRatio' => 1.1, // Giảm tỷ lệ font
                    'isFontSubsettingEnabled' => true,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'chroot' => public_path(),
                    'enable_font_subsetting' => true, // Thêm option này
                    'font_cache' => storage_path('fonts/'), // Cache font
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
     * Chuẩn bị HTML content với CSS khớp với giao diện gốc - Fixed Font Issues
     */
    private function prepareHtmlContent(string $content): string
    {
        // CSS được cải thiện với font hỗ trợ tiếng Việt tốt hơn
        $css = '
        <style>
            @page {
                margin: 15mm 20mm;
                size: A4;
            }

            * {
                font-family: "DejaVu Sans", "Arial", sans-serif;
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-size: 24px;
                line-height: 1.5;
                color: #000;
                background: white;
                padding: 0;
                margin: 0;
                font-family: "DejaVu Sans", "Arial", sans-serif;
            }

            .container-fluid {
                width: 100%;
                padding: 0;
            }

            .contract-document {
                max-width: 210mm;
                min-height: 297mm;
                background: white;
                font-family: "DejaVu Sans", "Arial", sans-serif;
                font-size: 16px;
                line-height: 1.5;
                padding: 15mm 20mm;
                margin: 0 auto;
            }

            /* Header styling */
            .text-center {
                text-align: center;
            }

            .text-end {
                text-align: right;
            }

            .mb-2 {
                margin-bottom: 0.5rem;
            }

            .mb-3 {
                margin-bottom: 1rem;
            }

            .mb-4 {
                margin-bottom: 1.5rem;
            }

            .mb-5 {
                margin-bottom: 3rem;
            }

            .my-4 {
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .mt-3 {
                margin-top: 1rem;
            }

            .mt-5 {
                margin-top: 3rem;
            }

            .pt-3 {
                padding-top: 1rem;
            }

            .pt-4 {
                padding-top: 1.5rem;
            }

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

            /* Typography - Font được cải thiện */
            strong, b {
                font-weight: bold;
                font-family: "DejaVu Sans", "Arial", sans-serif;
            }

            u {
                text-decoration: underline;
            }

            em, i {
                font-style: italic;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: "DejaVu Sans", "Arial", sans-serif;
                font-weight: bold;
                margin: 0;
            }

            h3 {
                font-size: 20px;
                font-weight: bold;
                letter-spacing: 0.5px;
                margin: 0;
            }

            p {
                margin-bottom: 0.5rem;
                font-family: "DejaVu Sans", "Arial", sans-serif;
                line-height: 1.5;
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

            /* Row/Column system for PDF */
            .row {
                display: table;
                width: 100%;
                margin-bottom: 1rem;
            }

            .col-6 {
                display: table-cell;
                width: 50%;
                vertical-align: top;
                padding-right: 15px;
            }

            .col-6:last-child {
                padding-right: 0;
                padding-left: 15px;
            }

            /* Header specific styles */
            .contract-document .text-center.mb-4 > div:first-child {
                font-size: 17px;
                font-weight: bold;
                letter-spacing: 0.3px;
                margin-bottom: 0.5rem;
            }

            .contract-document .text-center.mb-4 > div:nth-child(2) {
                margin-bottom: 1rem;
            }

            .contract-document .text-center.mb-4 > div:nth-child(2) u strong {
                font-weight: bold;
            }

            /* Contract title box */
            .contract-document .text-end .border {
                display: inline-block;
                border: 1px solid #000;
                padding: 0.25rem 0.5rem;
                font-weight: bold;
            }

            /* Form number box */
            .contract-document .text-end .border small {
                font-size: 14px;
            }

            /* Content sections */
            .contract-content-section {
                margin-bottom: 1.5rem;
            }

            .contract-content-section p {
                margin-bottom: 0.5rem;
            }

            .contract-content-section .ms-3 p {
                margin-left: 1rem;
                margin-bottom: 0.5rem;
            }

            /* Party information styling */
            .party-section {
                margin-bottom: 1rem;
            }

            .party-section p {
                margin-bottom: 0.25rem;
            }

            .party-section strong {
                font-weight: bold;
            }

            /* Signature section */
            .signature-row {
                margin-top: 3rem;
                padding-top: 1.5rem;
                display: table;
                width: 100%;
            }

            .signature-col {
                display: table-cell;
                width: 50%;
                text-align: center;
                vertical-align: top;
                padding: 0 15px;
            }

            .signature-col p {
                margin-bottom: 0.25rem;
            }

            .signature-col .mb-5 {
                margin-bottom: 3rem;
            }

            .signature-col .mt-5 {
                margin-top: 3rem;
            }

            /* Ensure proper spacing for signature names */
            .signature-name {
                margin-top: 3rem;
                padding-top: 1rem;
            }

            /* Money amounts highlighting */
            .money-highlight {
                font-weight: bold;
            }

            /* List styling */
            .contract-list {
                margin-left: 1rem;
            }

            .contract-list p {
                margin-bottom: 0.25rem;
                text-indent: -15px;
                padding-left: 15px;
            }

            /* Special formatting for specific sections */
            .date-info {
                margin-bottom: 1.5rem;
            }

            .contract-terms {
                margin-bottom: 1.5rem;
            }

            .responsibilities {
                margin-bottom: 1.5rem;
            }

            .general-terms {
                margin-bottom: 1.5rem;
            }

            /* Page break control */
            .no-break {
                page-break-inside: avoid;
            }

            /* Font fallback cho các ký tự đặc biệt */
            .vietnamese-text {
                font-family: "DejaVu Sans", "Arial Unicode MS", "Arial", sans-serif;
            }

            /* Ensure proper line height for Vietnamese text */
            .contract-document {
                line-height: 1.5;
            }

            /* Remove any conflicting Bootstrap styles that might not work in PDF */
            .container-fluid {
                padding: 0 !important;
            }

            /* Additional spacing for better readability */
            .section-break {
                margin: 1.5rem 0;
            }

            /* Specific styles for contract elements */
            .contract-document > .text-center.mb-4 {
                text-align: center;
                margin-bottom: 1.5rem;
            }

            .contract-document > .text-center.mb-4 > .mb-2 > strong {
                font-size: 17px;
                letter-spacing: 0.3px;
                font-weight: bold;
            }

            .contract-document > .text-center.mb-4 > .mb-3 > u > strong {
                font-weight: bold;
                text-decoration: underline;
            }

            .contract-document > .text-center.mb-4 > .my-4 > h3 {
                font-size: 20px;
                font-weight: bold;
                letter-spacing: 0.5px;
                margin: 0;
            }

            .contract-document > .text-center.mb-4 > .text-end.mb-4 > .d-inline-block.border.border-dark.px-2.py-1 > strong {
                font-weight: bold;
            }

            /* Cải thiện hiển thị số và ký tự đặc biệt */
            .numeric {
                font-family: "DejaVu Sans Mono", monospace;
            }

            /* Đảm bảo tất cả text đều sử dụng font phù hợp */
            div, span, td, th {
                font-family: "DejaVu Sans", "Arial", sans-serif;
            }
        </style>';

        // Xử lý content để đảm bảo encoding UTF-8
        $processedContent = $this->processContent($content);

        // Tạo HTML hoàn chỉnh với meta UTF-8 được cải thiện
        $htmlContent = '<!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Hợp đồng cho thuê</title>
            ' . $css . '
        </head>
        <body>';

        // Thêm content đã được xử lý với class vietnamese-text
        $htmlContent .= '<div class="vietnamese-text">' . $processedContent . '</div>';

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
        $content = preg_replace_callback('/[\x{00A0}-\x{FFFF}]/u', function($matches) {
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
