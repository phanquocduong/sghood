<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\RefundRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RefundRequestController extends Controller
{
    protected $refundRequestService;

    public function __construct(RefundRequestService $refundRequestService)
    {
        $this->refundRequestService = $refundRequestService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['sort', 'status']);
            $refundRequests = $this->refundRequestService->getRefundRequests($filters);
            return response()->json([
                'data' => $refundRequests
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách yêu cầu hoàn tiền. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'account_holder' => 'required|string|max:255',
            ]);

            $bankInfo = [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
            ];

            $result = $this->refundRequestService->updateBankInfo($id, $bankInfo);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Thông tin chuyển khoản đã được chỉnh sửa thành công.',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi chỉnh sửa thông tin chuyển khoản', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã xảy ra lỗi khi chỉnh sửa thông tin chuyển khoản.'], 500);
        }
    }
}
