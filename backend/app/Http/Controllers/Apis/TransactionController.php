<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý các yêu cầu API liên quan đến giao dịch của người dùng.
 */
class TransactionController extends Controller
{
    /**
     * @var TransactionService Dịch vụ xử lý logic giao dịch
     */
    protected $transactionService;

    /**
     * Khởi tạo controller với dịch vụ quản lý giao dịch.
     *
     * @param TransactionService $transactionService Dịch vụ xử lý logic giao dịch
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Lấy danh sách giao dịch của người dùng đang đăng nhập với phân trang.
     *
     * @param Request $request Yêu cầu chứa các tham số lọc và phân trang
     * @return \Illuminate\Http\JsonResponse Phản hồi JSON chứa danh sách giao dịch
     */
    public function index(Request $request)
    {
        try {
            // Tạo mảng bộ lọc từ tham số truy vấn
            $filters = [
                'sort' => $request->query('sort', 'default'), // Tiêu chí sắp xếp (mặc định, cũ nhất, mới nhất)
                'type' => $request->query('type', ''), // Loại giao dịch
            ];

            // Lấy số lượng giao dịch mỗi trang, mặc định là 10
            $perPage = $request->query('per_page', 10);

            // Gọi dịch vụ để lấy danh sách giao dịch của người dùng
            $transactions = $this->transactionService->getUserTransactions(Auth::id(), $filters, $perPage);

            // Trả về phản hồi JSON với danh sách giao dịch và thông tin phân trang
            return response()->json([
                'data' => $transactions->items(), // Dữ liệu giao dịch
                'current_page' => $transactions->currentPage(), // Trang hiện tại
                'total_pages' => $transactions->lastPage(), // Tổng số trang
                'total' => $transactions->total(), // Tổng số giao dịch
                'per_page' => $transactions->perPage(), // Số lượng giao dịch mỗi trang
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            // Trả về phản hồi JSON với thông báo lỗi nếu có ngoại lệ
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách giao dịch.',
                'success' => false
            ], 500);
        }
    }
}
