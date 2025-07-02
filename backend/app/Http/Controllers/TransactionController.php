<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    // Hiển thị danh sách giao dịch
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'transfer_type' => $request->get('transfer_type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $perPage = $request->get('per_page', 15);
        $transactions = $this->transactionService->getAllTransactions($filters, $perPage);
        $stats = $this->transactionService->getTransactionStats();

        return view('transactions.index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }

    // Hiển thị chi tiết giao dịch
    public function show(int $id): JsonResponse
    {
        try {
            $transaction = $this->transactionService->getTransactionById($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giao dịch'
                ], 404);
            }

            // Đảm bảo các relationship được load
            $transaction->load(['invoice']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'content' => $transaction->content ?? 'N/A',
                    'transfer_type' => $transaction->transfer_type,
                    'amount' => number_format($transaction->transfer_amount ?? 0, 0, ',', '.'),
                    'reference_code' => $transaction->reference_code ?? 'N/A',
                    'created_at' => $transaction->created_at->format('d/m/Y H:i'),

                    // Thông tin hóa đơn (nếu có)
                    'invoice' => $transaction->invoice ? [
                        'code' => $transaction->invoice->code,
                        'status' => $transaction->invoice->status,
                        'total_amount' => number_format($transaction->invoice->total_amount ?? 0, 0, ',', '.'),
                        'month' => $transaction->invoice->month,
                        'year' => $transaction->invoice->year,
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in transaction show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin giao dịch'
            ], 500);
        }
    }
}
