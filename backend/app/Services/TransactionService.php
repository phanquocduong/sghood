<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService
{
    // Lấy tất cả giao dịch với bộ lọc và phân trang
    public function getAllTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::with(['invoice']);

        // Tìm kiếm theo content
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('content', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('refund_request_id', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($filters) {
                      $invoiceQuery->where('code', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        // Lọc theo loại giao dịch
        if (!empty($filters['transfer_type'])) {
            $query->where('transfer_type', $filters['transfer_type']);
        }

        // Lọc theo tháng
        if (!empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        // Lọc theo năm
        if (!empty($filters['year'])) {
            $query->whereYear('transaction_date', $filters['year']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    // Lấy chi tiết giao dịch theo ID
    public function getTransactionById(int $id): ?Transaction
    {
        return Transaction::with(['invoice'])->find($id);
    }

    // Lấy thống kê giao dịch theo filter
    public function getTransactionStats(array $filters = []): array
    {
        $query = Transaction::query();

        // Áp dụng filters giống như trong getAllTransactions
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('content', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($filters) {
                      $invoiceQuery->where('code', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        if (!empty($filters['transfer_type'])) {
            $query->where('transfer_type', $filters['transfer_type']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('transaction_date', $filters['year']);
        }

        $transactions = $query->get(['transfer_type', 'transfer_amount']);

        $inTransactions = $transactions->where('transfer_type', 'in');
        $outTransactions = $transactions->where('transfer_type', 'out');

        return [
            'total' => $transactions->count(),
            'in' => $inTransactions->count(),
            'out' => $outTransactions->count(),
            'balance' => $inTransactions->sum('transfer_amount') - $outTransactions->sum('transfer_amount'),
            'in_amount' => $inTransactions->sum('transfer_amount'),
            'out_amount' => $outTransactions->sum('transfer_amount'),
            'total_amount' => $transactions->sum('transfer_amount'),
        ];
    }

    // Lấy danh sách tháng
    public function getMonths(): array
    {
        return [
            '1' => 'Tháng 1',
            '2' => 'Tháng 2',
            '3' => 'Tháng 3',
            '4' => 'Tháng 4',
            '5' => 'Tháng 5',
            '6' => 'Tháng 6',
            '7' => 'Tháng 7',
            '8' => 'Tháng 8',
            '9' => 'Tháng 9',
            '10' => 'Tháng 10',
            '11' => 'Tháng 11',
            '12' => 'Tháng 12',
        ];
    }

    // Lấy danh sách năm
    public function getYears(): array
    {
        $years = [];
        $currentYear = now()->year;

        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $years[$year] = 'Năm ' . $year;
        }

        return $years;
    }

    // Lấy danh sách loại giao dịch
    public function getTransferTypes(): array
    {
        return [
            'in' => 'Tiền vào (IN)',
            'out' => 'Tiền ra (OUT)',
        ];
    }
}
