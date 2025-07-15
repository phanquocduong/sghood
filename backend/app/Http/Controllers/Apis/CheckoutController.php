<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
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
