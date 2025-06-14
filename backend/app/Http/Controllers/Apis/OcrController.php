<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrController extends Controller
{
    public function processCitizenId(Request $request)
    {
        // Validate file upload
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png|max:2048',
        ]);

        // Lưu ảnh tạm thời
        $image = $request->file('image');
        $path = $image->store('temp', 'public');

        // Đường dẫn đầy đủ tới ảnh
        $fullPath = storage_path('app/public/' . $path);

        try {
            // Gọi Tesseract để trích xuất văn bản
            $ocr = new TesseractOCR($fullPath);
            $text = $ocr->lang('vie')->run(); // Sử dụng ngôn ngữ tiếng Việt

            // Phân tích dữ liệu (giả định định dạng CCCD Việt Nam)
            $data = $this->parseCitizenIdData($text);

            // Xóa ảnh tạm
            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // Xóa ảnh tạm nếu có lỗi
            Storage::disk('public')->delete($path);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function parseCitizenIdData($text)
    {
        // Logic đơn giản để trích xuất thông tin (có thể tùy chỉnh regex cho chính xác hơn)
        $data = [
            'id_number' => '',
            'name' => '',
            'dob' => '',
            'address' => '',
        ];

        // Ví dụ regex để trích xuất thông tin
        if (preg_match('/\d{12}/', $text, $matches)) {
            $data['id_number'] = $matches[0];
        }
        if (preg_match('/Họ và tên: ([^\n]+)/i', $text, $matches)) {
            $data['name'] = trim($matches[1]);
        }
        if (preg_match('/Ngày sinh: (\d{2}\/\d{2}\/\d{4})/i', $text, $matches)) {
            $data['dob'] = $matches[1];
        }
        if (preg_match('/Quê quán: ([^\n]+)/i', $text, $matches)) {
            $data['address'] = trim($matches[1]);
        }

        return $data;
    }
}
