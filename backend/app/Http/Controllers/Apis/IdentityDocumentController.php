<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\IdentityDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Controller xử lý các yêu cầu API liên quan đến trích xuất thông tin từ ảnh căn cước công dân (CCCD).
 */
class IdentityDocumentController extends Controller
{
    /**
     * Khởi tạo controller với dịch vụ trích xuất thông tin CCCD.
     *
     * @param IdentityDocumentService $identityDocumentService Dịch vụ xử lý logic trích xuất CCCD
     */
    public function __construct(
        private readonly IdentityDocumentService $identityDocumentService
    ) {}

    /**
     * Xử lý yêu cầu trích xuất thông tin từ ảnh CCCD.
     *
     * @param Request $request Yêu cầu chứa các tệp ảnh CCCD
     * @return JsonResponse Phản hồi JSON với dữ liệu trích xuất hoặc thông báo lỗi
     */
    public function extractIdentityImages(Request $request): JsonResponse
    {
        try {
            // Xác thực yêu cầu: kiểm tra ảnh CCCD phải là ảnh, định dạng jpeg/png, tối đa 2MB
            $request->validate([
                'identity_images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            // Kiểm tra số lượng ảnh tải lên (phải đúng 2 ảnh: mặt trước và mặt sau CCCD)
            if (count($request->file('identity_images')) !== 2) {
                return response()->json([
                    'error' => 'Vui lòng tải lên đúng 2 ảnh: mặt trước và mặt sau CCCD.',
                    'status' => 422,
                ], 422);
            }

            // Gọi dịch vụ để trích xuất thông tin từ ảnh CCCD
            $cccdData = $this->identityDocumentService->extractIdentityImages($request->file('identity_images'));

            // Trả về phản hồi JSON với dữ liệu trích xuất và thông báo thành công
            return response()->json([
                'data' => $cccdData,
                'message' => 'Trích xuất thông tin CCCD thành công',
            ]);
        } catch (ValidationException $e) {
            // Xử lý lỗi xác thực
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
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi trích xuất CCCD', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            // Tùy chỉnh thông báo lỗi dựa trên nội dung ngoại lệ
            $errorMessage = match (true) {
                str_contains($e->getMessage(), 'đúng 2 ảnh') => $e->getMessage(),
                str_contains($e->getMessage(), 'định dạng JPEG hoặc PNG') => $e->getMessage(),
                str_contains($e->getMessage(), 'Google Vision API') => 'Lỗi xử lý ảnh CCCD. Vui lòng kiểm tra lại ảnh và thử lại.',
                str_contains($e->getMessage(), 'Số CCCD không hợp lệ') => 'Số CCCD không đúng định dạng (9-12 số).',
                str_contains($e->getMessage(), 'Không thể trích xuất đầy đủ thông tin') => 'Không thể nhận diện đầy đủ thông tin từ ảnh CCCD. Vui lòng đảm bảo thực hiện đúng hướng dẫn',
                default => 'Lỗi xử lý ảnh CCCD. Vui lòng thử lại sau.'
            };

            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json([
                'error' => $errorMessage,
                'status' => 422,
            ], 422);
        }
    }
}
