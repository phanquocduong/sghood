<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\InvoiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function checkStatus(string $code): JsonResponse
    {
        try {
            $invoiceData = $this->invoiceService->checkStatus($code, Auth::id());
            return response()->json($invoiceData);
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            Log::error('Lỗi kiểm tra trạng thái thanh toán', [
                'user_id' => Auth::id(),
                'invoice_code' => $code,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã có lỗi xảy ra khi kiểm tra trạng thái thanh toán.'], 500);
        }
    }
}
