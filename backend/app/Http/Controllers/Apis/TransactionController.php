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
     * Lấy danh sách giao dịch của user đang đăng nhập
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'sort' => $request->query('sort', 'default'),
                'type' => $request->query('type', ''),
            ];

            $transactions = $this->transactionService->getUserTransactions(Auth::id(), $filters);

            return response()->json([
                'data' => $transactions,
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
