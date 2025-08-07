<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\ReturnRequest;
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

    public function requestReturn(ReturnRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            $bankInfo = null;
            if (!$validated['is_cash_refunded'] && isset($validated['bank_name'], $validated['account_number'], $validated['account_holder'])) {
                $bankInfo = [
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'account_holder' => $validated['account_holder'],
                ];
            }

            $check_out_date = \DateTime::createFromFormat('d/m/Y', $validated['check_out_date'])->format('Y-m-d');

            $result = $this->checkoutService->requestReturn($id, $bankInfo, $check_out_date);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Yêu cầu trả phòng đã được gửi.',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi yêu cầu trả phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi gửi yêu cầu trả phòng.'], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $checkouts = $this->checkoutService->getCheckouts();
            return response()->json([
                'data' => $checkouts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách yêu cầu trả phòng.'
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $checkout = $this->checkoutService->cancelCheckout($id);
            return response()->json([
                'message' => 'Hủy yêu cầu trả phòng thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi huỷ bỏ trả phòng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã có lỗi xảy ra khi hủy yêu cầu trả phòng.'], 500);
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
                'error' => 'Đã có lỗi xảy ra khi xác nhận kiểm kê.'
            ], 500);
        }
    }

    public function leftRoom(int $id): JsonResponse
    {
        try {
            $checkout = $this->checkoutService->confirmLeftRoom($id);
            return response()->json([
                'message' => 'Xác nhận đã rời phòng thành công',
                'data' => $checkout
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu trả phòng.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Lỗi xác nhận rời phòng:', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi xác nhận rời phòng.'
            ], 500);
        }
    }

    public function updateBank(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'is_cash_refunded' => 'required|boolean',
                'bank_info' => 'required_if:is_cash_refunded,false|array|nullable',
                'bank_info.bank_name' => 'required_if:is_cash_refunded,false|string|max:255',
                'bank_info.account_number' => 'required_if:is_cash_refunded,false|string|max:50',
                'bank_info.account_holder' => 'required_if:is_cash_refunded,false|string|max:255',
            ]);

            $bankInfo = $validated['is_cash_refunded'] ? null : $validated['bank_info'];

            $result = $this->checkoutService->updateBankInfo($id, $bankInfo);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Cập nhật thông tin hoàn tiền thành công',
                'data' => $result['data'],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi chỉnh sửa thông tin hoàn tiền', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã xảy ra lỗi khi chỉnh sửa thông tin hoàn tiền.'], 500);
        }
    }
}
