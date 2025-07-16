<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function requestReturn(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
                'account_holder' => 'required|string|max:255',
                'check_out_date' => 'required|date|after_or_equal:today',
            ]);

            $bankInfo = [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
            ];

            $result = $this->checkoutService->requestReturn($id, $bankInfo, $validated['check_out_date']);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Yêu cầu trả phòng và hoàn tiền cọc đã được gửi.',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi yêu cầu trả phòng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã xảy ra lỗi khi gửi yêu cầu trả phòng.'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['sort', 'status']);
            $checkouts = $this->checkoutService->getCheckouts($filters);
            return response()->json([
                'data' => $checkouts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách yêu cầu trả phòng. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $checkout = $this->checkoutService->rejectCheckout($id);
            return response()->json([
                'message' => 'Hủy yêu cầu trả phòng thành công',
                'data' => $checkout
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy yêu cầu trả phòng. Vui lòng thử lại.'
            ], 500);
        }
    }
}
