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
                'cccd_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Store uploaded image
            $imagePath = $request->file('cccd_image')->store('cccd_images', 'public');

            // Get full path to image
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

            // Parse CCCD data (simple regex-based parsing for demo)
            $cccdData = $this->parseCccdText($extractedText);

            // Delete temporary image
            Storage::disk('public')->delete($imagePath);

            return response()->json([
                'status' => 'success',
                'data' => $cccdData,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function parseCccdText($text)
    {
        $data = [
            'id_number' => '',
            'full_name' => '',
            'date_of_birth' => '',
            'address' => '',
        ];

        // Split text into lines
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
            // Full Name (after "Họ và tên" or "Full name", capture next non-empty line)
            if (preg_match('/Họ và tên[:\s]*[:\s]*/u', $trimmedLine) && isset($lines[$index + 1]) && trim($lines[$index + 1]) !== '') {
                $data['full_name'] = trim($lines[$index + 1]);
            } elseif (preg_match('/Full name[:\s]*[:\s]*/i', $trimmedLine) && isset($lines[$index + 1]) && trim($lines[$index + 1]) !== '') {
                $data['full_name'] = trim($lines[$index + 1]);
            }
            // Date of Birth (format DD/MM/YYYY)
            if (preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/', $trimmedLine, $matches)) {
                $data['date_of_birth'] = $matches[1];
            }
            // Address (after "Nơi thường trú" or "Place of residence", collect multiple lines)
            if (preg_match('/Nơi thường trú[:\s]*[:\s]*/u', $trimmedLine) || preg_match('/Place of residence[:\s]*[:\s]*/i', $trimmedLine)) {
                $addressStarted = true;
            } elseif ($addressStarted && !preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex|Nơi cấp|Place of origin|Ngày cấp|Date of expiry)/u', $trimmedLine)) {
                $addressLines[] = $trimmedLine;
            } elseif ($addressStarted && (preg_match('/^(Họ và tên|Full name|Nơi thường trú|Place of residence|Ngày sinh|Date of birth|Quốc tịch|Nationality|Giới tính|Sex|Nơi cấp|Place of origin|Ngày cấp|Date of expiry)/u', $trimmedLine) || empty($trimmedLine))) {
                $addressStarted = false;
            }
        }

        // Join address lines and remove duplicates
        $data['address'] = implode(', ', array_filter(array_unique($addressLines)));

        return $data;
    }
}
