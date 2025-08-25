<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\InvoiceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến hóa đơn.
 */
class InvoiceController extends Controller
{
    /**
     * Khởi tạo controller với dịch vụ quản lý hóa đơn.
     *
     * @param InvoiceService $invoiceService Dịch vụ xử lý logic hóa đơn
     */
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    /**
     * Lấy danh sách tháng và năm duy nhất từ các hóa đơn của người dùng.
     *
     * @return JsonResponse Phản hồi JSON chứa danh sách tháng và năm
     */
    public function getMonthsAndYears(): JsonResponse
    {
        try {
            // Gọi dịch vụ để lấy danh sách tháng và năm từ hóa đơn
            $data = $this->invoiceService->getInvoiceMonthsAndYears();

            // Trả về phản hồi JSON với dữ liệu tháng và năm
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách tháng và năm', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi lấy danh sách tháng và năm'
            ], 500);
        }
    }

    /**
     * Lấy danh sách hóa đơn của người dùng với bộ lọc và phân trang.
     *
     * @param Request $request Yêu cầu chứa các tham số lọc và phân trang
     * @return JsonResponse Phản hồi JSON chứa danh sách hóa đơn
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Lấy các tham số lọc từ yêu cầu (sắp xếp, loại, tháng, năm)
            $filters = $request->only(['sort', 'type', 'month', 'year']);
            // Lấy số lượng hóa đơn mỗi trang, mặc định là 10
            $perPage = $request->query('per_page', 10);

            // Gọi dịch vụ để lấy danh sách hóa đơn
            $invoices = $this->invoiceService->getUserInvoices($filters, $perPage);

            // Trả về phản hồi JSON với dữ liệu hóa đơn và thông tin phân trang
            return response()->json([
                'success' => true,
                'data' => $invoices->items(), // Danh sách hóa đơn
                'current_page' => $invoices->currentPage(), // Trang hiện tại
                'total_pages' => $invoices->lastPage(), // Tổng số trang
                'total' => $invoices->total(), // Tổng số hóa đơn
                'per_page' => $invoices->perPage() // Số lượng mỗi trang
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách hoá đơn', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi lấy danh sách hóa đơn'
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một hóa đơn theo mã hóa đơn.
     *
     * @param string $code Mã hóa đơn
     * @return JsonResponse Phản hồi JSON chứa chi tiết hóa đơn
     */
    public function show(string $code): JsonResponse
    {
        try {
            // Gọi dịch vụ để lấy chi tiết hóa đơn
            $invoice = $this->invoiceService->getInvoiceById($code);

            // Trả về phản hồi JSON với chi tiết hóa đơn
            return response()->json([
                'success' => true,
                'data' => $invoice
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy chi tiết hóa đơn', [
                'user_id' => Auth::id(),
                'invoice_code' => $code,
                'error' => $e->getMessage(),
            ]);

            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Kiểm tra trạng thái thanh toán của một hóa đơn.
     *
     * @param string $code Mã hóa đơn
     * @return JsonResponse Phản hồi JSON chứa trạng thái và loại hóa đơn
     */
    public function checkStatus(string $code): JsonResponse
    {
        try {
            // Gọi dịch vụ để kiểm tra trạng thái hóa đơn
            $invoiceData = $this->invoiceService->checkStatus($code, Auth::id());

            // Trả về phản hồi JSON với trạng thái và loại hóa đơn
            return response()->json($invoiceData);
        } catch (ModelNotFoundException) {
            // Trả về lỗi nếu hóa đơn không tồn tại hoặc người dùng không có quyền
            return response()->json(['error' => 'Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi kiểm tra trạng thái thanh toán', [
                'user_id' => Auth::id(),
                'invoice_code' => $code,
                'error' => $e->getMessage(),
            ]);

            // Trả về phản hồi JSON với thông báo lỗi
            return response()->json(['error' => 'Đã có lỗi xảy ra khi kiểm tra trạng thái thanh toán.'], 500);
        }
    }
}
