<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\CheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
                'check_out_date' => 'required|date_format:d/m/Y|after_or_equal:today',
            ]);

            $bankInfo = [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder' => $validated['account_holder'],
            ];

            $check_out_date = \DateTime::createFromFormat('d/m/Y', $validated['check_out_date'])->format('Y-m-d');

            $result = $this->checkoutService->requestReturn($id, $bankInfo, $check_out_date);

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
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi huỷ bỏ trả phòng', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi hủy yêu cầu trả phòng. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function confirm(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:Đồng ý,Từ chối',
                'user_rejection_reason' => 'required_if:status,Từ chối|string|max:1000|nullable',
            ]);

            $checkout = $this->checkoutService->confirmCheckout($id, $validated['status'], $validated['user_rejection_reason'] ?? null);

            return response()->json([
                'message' => $validated['status'] === 'Đồng ý' ? 'Xác nhận kiểm kê thành công' : 'Từ chối kiểm kê thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi xác nhận kiểm kê', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi xác nhận kiểm kê. Vui lòng thử lại.'
            ], 500);
        }
    }
}
