<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractExtensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContractExtensionController extends Controller
{
    public function __construct(
        private readonly ContractExtensionService $contractExtensionService,
    ) {}

    public function extend(int $id, Request $request): JsonResponse
    {
        try {
            $months = $request->input('months', 6);
            $result = $this->contractExtensionService->extendContract($id, $months);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json(['message' => 'Yêu cầu gia hạn hợp đồng đã được gửi', 'extension_id' => $result['extension_id']], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi gia hạn hợp đồng:' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi gia hạn hợp đồng'], 500);
        }
    }

    public function index()
    {
        try {
            $bookings = $this->contractExtensionService->getExtensions();
            return response()->json([
                'data' => $bookings
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã có lỗi xảy ra khi lấy danh sách đặt phòng. Vui lòng thử lại.'], 500);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $result = $this->contractExtensionService->cancelContractExtension($id);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json(['message' => 'Hủy gia hạn thành công'], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy gia hạn:' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy gia hạn'], 500);
        }
    }
}
