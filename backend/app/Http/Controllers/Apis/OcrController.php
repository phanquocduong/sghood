<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OcrController extends Controller
{
    public function extractCccdData(Request $request)
    {
        try {
            // Validate file upload
            $request->validate([
                'cccd_images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $cccdData = [
                'id_number' => '',
                'full_name' => '',
                'date_of_birth' => '',
                'address' => '',
                'date_of_issue' => '',
                'place_of_issue' => '',
            ];

            // Lặp qua từng file trong request
            foreach ($request->file('cccd_images') as $index => $imageFile) {
                // Store uploaded image
                $imagePath = $imageFile->store('cccd_images', 'public');
                $fullImagePath = storage_path('app/public/' . $imagePath);

                // Initialize Google Cloud Vision client
                $imageAnnotator = new ImageAnnotatorClient();

                // Read image content
                $imageContent = file_get_contents($fullImagePath);

                // Create image object
                $image = (new Image())
                    ->setContent($imageContent);

                // Set feature for text detection
                $feature = (new Feature())
                    ->setType(Feature\Type::TEXT_DETECTION);

                // Create annotate image request
                $requestAnnotate = (new AnnotateImageRequest())
                    ->setImage($image)
                    ->setFeatures([$feature]);

                // Create batch annotate request
                $batchRequest = (new BatchAnnotateImagesRequest())
                    ->setRequests([$requestAnnotate]);

                // Perform batch text detection
                $response = $imageAnnotator->batchAnnotateImages($batchRequest);
                $annotations = $response->getResponses()[0]->getTextAnnotations();

                // Close client
                $imageAnnotator->close();

                // Extract text
                $extractedText = !empty($annotations) ? $annotations[0]->getDescription() : '';

                // Phân tích văn bản để xác định mặt trước hoặc mặt sau
                if (strpos($extractedText, 'Họ và tên') !== false || strpos($extractedText, 'Full name') !== false) {
                    // Xử lý mặt trước
                    $frontData = $this->parseCccdFrontText($extractedText);
                    $cccdData['id_number'] = $frontData['id_number'] ?: $cccdData['id_number'];
                    $cccdData['full_name'] = $frontData['full_name'] ?: $cccdData['full_name'];
                    $cccdData['date_of_birth'] = $frontData['date_of_birth'] ?: $cccdData['date_of_birth'];
                    $cccdData['address'] = $frontData['address'] ?: $cccdData['address'];
                } elseif (strpos($extractedText, 'Ngày, tháng, năm') !== false || strpos($extractedText, 'Cục Trưởng') !== false) {
                    // Xử lý mặt sau
                    $backData = $this->parseCccdBackText($extractedText);
                    $cccdData['date_of_issue'] = $backData['date_of_issue'] ?: $cccdData['date_of_issue'];
                    $cccdData['place_of_issue'] = $backData['place_of_issue'] ?: $cccdData['place_of_issue'];
                }

                // Delete temporary image
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'status' => 'success',
                'data' => $cccdData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function parseCccdFrontText($text)
    {
        $data = [
            'id_number' => '',
            'full_name' => '',
            'date_of_birth' => '',
            'address' => '',
        ];

        $lines = preg_split('/\r\n|\r|\n/', $text);

        $addressStarted = false;
        $addressLines = [];
        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            // ID Number (12 digits)
            if (preg_match('/\b\d{12}\b/', $trimmedLine, $matches)) {
                $data['id_number'] = $matches[0];
            }
            // Full Name (after "Họ và tên" or "Full name")
            if (preg_match('/^(Họ và tên|Full name)[:\s]*[:\s]*/iu', $trimmedLine) && isset($lines[$index + 1]) && trim($lines[$index + 1]) !== '') {
                $data['full_name'] = trim($lines[$index + 1]);
            }
            // Date of Birth (format DD/MM/YYYY)
            if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', $trimmedLine, $matches)) {
                $data['date_of_birth'] = $matches[1];
            }
            // Address (after "Nơi thường trú" or "Place of residence")
            if (preg_match('/^(Nơi thường trú|Place of residence)[:\s]*[:\s]*/iu', $trimmedLine)) {
                $addressStarted = true;
            } elseif ($addressStarted && !preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/u', $trimmedLine)) {
                $addressLines[] = $trimmedLine;
            } elseif ($addressStarted && (preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex)/u', $trimmedLine) || empty($trimmedLine))) {
                $addressStarted = false;
            }
        }

        $data['address'] = implode(', ', array_filter(array_unique($addressLines)));
        return $data;
    }

    private function parseCccdBackText($text)
    {
        $data = [
            'date_of_issue' => '',
            'place_of_issue' => '',
        ];

        $lines = preg_split('/\r\n|\r|\n/', $text);

        $potentialPlace = '';
        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            // Date of Issue (direct match after "Ngày, tháng, năm" with DD/MM/YYYY)
            if (preg_match('/^Ngày, tháng, năm[:\s]*(\d{2}\/\d{2}\/\d{4})/iu', $trimmedLine, $matches)) {
                $data['date_of_issue'] = $matches[1];
            }

            // Place of Issue (prioritize Vietnamese text)
            if (preg_match('/Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội/i', $trimmedLine)) {
                $data['place_of_issue'] = 'Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội';
            } elseif (preg_match('/Cục Trưởng.*(Cục Cảnh Sát|Quản Lý Hành Chính|Trật Tự Xã Hội)/i', $trimmedLine)) {
                if (mb_strlen($trimmedLine) > mb_strlen($potentialPlace)) {
                    $potentialPlace = $trimmedLine;
                }
            } elseif (preg_match('/Cục Trưởng/i', $trimmedLine) && isset($lines[$index + 1]) && preg_match('/(Cục Cảnh Sát|Quản Lý Hành Chính|Trật Tự Xã Hội)/i', $lines[$index + 1])) {
                $combinedLine = trim($trimmedLine . ' ' . $lines[$index + 1]);
                if (mb_strlen($combinedLine) > mb_strlen($potentialPlace)) {
                    $potentialPlace = $combinedLine;
                }
            }
        }

        // Assign the longest Vietnamese match, ignoring English if Vietnamese is found
        if (!empty($potentialPlace)) {
            $data['place_of_issue'] = $potentialPlace;
        } elseif (preg_match('/DIRECTOR GENERAL/i', $text) && empty($data['place_of_issue'])) {
            $data['place_of_issue'] = 'Cục Trưởng Cục Cảnh Sát Quản Lý Hành Chính Về Trật Tự Xã Hội'; // Fallback to correct Vietnamese if English detected
        }

        // Fallback: Check for date anywhere in text
        if (empty($data['date_of_issue'])) {
            foreach ($lines as $line) {
                if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', trim($line), $matches)) {
                    $data['date_of_issue'] = $matches[1];
                    break;
                }
            }
        }

        return $data;
    }
}
