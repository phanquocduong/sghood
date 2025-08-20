<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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
            'month' => $request->get('month'),
            'year' => $request->get('year'),
        ];

        $perPage = $request->get('per_page', 15);
        $transactions = $this->transactionService->getAllTransactions($filters, $perPage);
        $stats = $this->transactionService->getTransactionStats($filters);

        // Lấy dữ liệu cho dropdown
        $months = $this->transactionService->getMonths();
        $years = $this->transactionService->getYears();
        $transferTypes = $this->transactionService->getTransferTypes();

        return view('transactions.index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $filters,
            'months' => $months,
            'years' => $years,
            'transferTypes' => $transferTypes,
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

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'content' => $transaction->content ?? 'N/A',
                    'transfer_type' => $transaction->transfer_type ?? 'N/A',
                    'amount' => number_format($transaction->transfer_amount ?? 0, 0, ',', '.'),
                    'reference_code' => $transaction->reference_code ?? 'N/A',
                    'created_at' => $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : 'N/A',

                    // Thông tin hóa đơn (nếu có)
                    'invoice' => $transaction->invoice ? [
                        'code' => $transaction->invoice->code ?? 'N/A',
                        'status' => $transaction->invoice->status ?? 'N/A',
                        'total_amount' => number_format($transaction->invoice->total_amount ?? 0, 0, ',', '.'),
                        'month' => $transaction->invoice->month ?? 'N/A',
                        'year' => $transaction->invoice->year ?? 'N/A',
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in transaction show: ' . $e->getMessage(), [
                'transaction_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin giao dịch: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getRevenueByYear(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $revenues = Transaction::selectRaw('MONTH(transaction_date) as month, SUM(transfer_amount) as total')
            ->whereYear('transaction_date', $year)
            ->where('transfer_type', 'in') // 💡 chỉ tính tiền vào
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return response()->json($revenues);
    }
}
