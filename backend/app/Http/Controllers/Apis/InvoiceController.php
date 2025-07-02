<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\InvoiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Lấy danh sách tháng và năm từ hóa đơn của user
     *
     * @return JsonResponse
     */
    public function getMonthsAndYears(): JsonResponse
    {
        try {
            $data = $this->invoiceService->getInvoiceMonthsAndYears();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách tháng và năm', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi lấy danh sách tháng và năm'
            ], 500);
        }
    }

   /**
     * Lấy danh sách hóa đơn của user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['sort', 'type', 'month', 'year']);
            $invoices = $this->invoiceService->getUserInvoices($filters);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hoá đơn', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi lấy danh sách hóa đơn'
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một hóa đơn
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            return response()->json([
                'success' => true,
                'data' => $invoice
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hóa đơn', [
                'user_id' => Auth::id(),
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

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
