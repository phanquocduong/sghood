<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\IdentityDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IdentityDocumentController extends Controller
{
    public function __construct(
        private readonly IdentityDocumentService $identityDocumentService
    ) {}

    public function extractIdentityImages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'identity_images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            if (count($request->file('identity_images')) !== 2) {
                return response()->json([
                    'error' => 'Vui lòng tải lên đúng 2 ảnh: mặt trước và mặt sau CCCD.',
                    'status' => 422,
                ], 422);
            }

            $cccdData = $this->identityDocumentService->extractIdentityImages($request->file('identity_images'));

            return response()->json([
                'data' => $cccdData,
                'message' => 'Trích xuất thông tin CCCD thành công',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Lỗi tải lên ảnh CCCD: ';
            if (isset($errors['identity_images.0'])) {
                $errorMessage .= 'Ảnh không hợp lệ (kiểm tra định dạng JPEG/PNG hoặc kích thước tối đa 2MB).';
            } else {
                $errorMessage .= 'Vui lòng kiểm tra lại ảnh tải lên.';
            }

            return response()->json([
                'error' => $errorMessage,
                'status' => 422,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Lỗi trích xuất CCCD', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $errorMessage = match (true) {
                str_contains($e->getMessage(), 'đúng 2 ảnh') => $e->getMessage(),
                str_contains($e->getMessage(), 'định dạng JPEG hoặc PNG') => $e->getMessage(),
                str_contains($e->getMessage(), 'Google Vision API') => 'Lỗi xử lý ảnh CCCD. Vui lòng kiểm tra lại ảnh và thử lại.',
                str_contains($e->getMessage(), 'Số CCCD không hợp lệ') => 'Số CCCD không đúng định dạng (9-12 số).',
                str_contains($e->getMessage(), 'Không thể trích xuất đầy đủ thông tin') => 'Không thể nhận diện đầy đủ thông tin từ ảnh CCCD. Vui lòng đảm bảo thực hiện đúng hướng dẫn',
                default => 'Lỗi xử lý ảnh CCCD. Vui lòng thử lại sau.'
            };

            return response()->json([
                'error' => $errorMessage,
                'status' => 422,
            ], 422);
        }
    }
}
