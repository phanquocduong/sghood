<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
   protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Lấy danh sách giao dịch của user đang đăng nhập với phân trang
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'sort' => $request->query('sort', 'default'),
                'type' => $request->query('type', ''),
            ];

            $perPage = $request->query('per_page', 10); // Số lượng giao dịch mỗi trang
            $transactions = $this->transactionService->getUserTransactions(Auth::id(), $filters, $perPage);

            return response()->json([
                'data' => $transactions->items(), // Dữ liệu giao dịch
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi lấy danh sách giao dịch.',
                'success' => false
            ], 500);
        }
    }
}
