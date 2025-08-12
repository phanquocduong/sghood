<?php

namespace App\Services\Apis;

use App\Models\Contract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractPdfService
{
    /**
     * Chuẩn bị nội dung HTML với CSS hỗ trợ tiếng Việt
     */
    private function prepareHtmlContent(string $content): string
    {
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
        $content = preg_replace('/<\?php.*?\?>/s', '', $content);
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }

        if (strpos($content, '&') !== false) {
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<[^>]+>/', $content) && strpos($content, '<div class=') !== false) {
            $content = htmlspecialchars_decode($content, ENT_QUOTES);
        }

        $content = preg_replace(
            '/font-family:\s*["\']?[^"\']*["\']?[^;]*/i',
            'font-family: "Noto Serif", "DejaVu Serif", serif',
            $content
        );

        $content = preg_replace('/style\s*=\s*["\']([^"\']*max-width:[^"\']*)["\']/', '', $content);
        $content = preg_replace('/box-shadow:\s*[^;]+;?/', '', $content);
        $content = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $content);

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
            $contract = Contract::query()
                ->where('id', $contractId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $filename = 'pdf/contracts/contract-' . $contract->id . '-' . time() . '.pdf';

            if ($contract->file && Storage::disk('private')->exists($contract->file)) {
                return ['data' => $contract->file];
            }

            if (!$contract->content) {
                return ['error' => 'Nội dung hợp đồng không tồn tại'];
            }

            $htmlContent = $this->prepareHtmlContent($contract->content);

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
