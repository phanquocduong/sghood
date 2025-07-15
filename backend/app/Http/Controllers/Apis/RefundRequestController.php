<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\RefundRequestService;
use Illuminate\Http\Request;

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

    public function reject($id)
    {
        try {
            $refundRequest = $this->refundRequestService->rejectRefundRequest($id);
            return response()->json([
                'message' => 'Hủy yêu cầu hoàn tiền thành công',
                'data' => $refundRequest
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu hoàn tiền.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy yêu cầu hoàn tiền. Vui lòng thử lại.'
            ], 500);
        }
    }
}
