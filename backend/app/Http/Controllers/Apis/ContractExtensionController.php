<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractExtensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến gia hạn hợp đồng.
 */
class ContractExtensionController extends Controller
{
    /**
     * Khởi tạo controller với dịch vụ gia hạn hợp đồng.
     *
     * @param ContractExtensionService $contractExtensionService Dịch vụ xử lý logic gia hạn hợp đồng
     */
    public function __construct(
        private readonly ContractExtensionService $contractExtensionService,
    ) {}

    /**
     * Gửi yêu cầu gia hạn hợp đồng.
     *
     * @param int $id ID của hợp đồng
     * @param Request $request Yêu cầu chứa số tháng gia hạn
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function extend(int $id, Request $request): JsonResponse
    {
        try {
            // Lấy số tháng gia hạn từ yêu cầu, mặc định là 6 tháng
            $months = $request->input('months', 6);
            // Gọi dịch vụ để xử lý gia hạn hợp đồng
            $result = $this->contractExtensionService->extendContract($id, $months);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công và ID yêu cầu gia hạn
            return response()->json(['message' => 'Yêu cầu gia hạn hợp đồng đã được gửi', 'extension_id' => $result['extension_id']], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi gia hạn hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi gia hạn hợp đồng'], 500);
        }
    }

    /**
     * Lấy danh sách các yêu cầu gia hạn hợp đồng của người dùng hiện tại.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách yêu cầu gia hạn
     */
    public function index()
    {
        try {
            // Gọi dịch vụ để lấy danh sách yêu cầu gia hạn
            $extensions = $this->contractExtensionService->getExtensions();
            // Trả về phản hồi JSON với danh sách yêu cầu gia hạn
            return response()->json([
                'data' => $extensions
            ], 200);
        } catch (\Exception $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách yêu cầu gia hạn. Vui lòng thử lại.'], 500);
        }
    }

    /**
     * Hủy yêu cầu gia hạn hợp đồng theo ID.
     *
     * @param int $id ID của yêu cầu gia hạn
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            // Gọi dịch vụ để hủy yêu cầu gia hạn
            $result = $this->contractExtensionService->cancelContractExtension($id);

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['message' => 'Hủy gia hạn thành công'], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy gia hạn:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy gia hạn'], 500);
        }
    }
}
