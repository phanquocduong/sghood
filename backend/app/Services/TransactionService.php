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

    // Lấy thống kê giao dịch với bộ lọc
    public function getTransactionStats(array $filters = []): array
    {
        $query = Transaction::query();

        // Nếu có filter theo tháng/năm thì áp dụng filter
        if (!empty($filters['month'])) {
            $query->whereMonth('transaction_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('transaction_date', $filters['year']);
        }

        // Lọc theo loại giao dịch
        if (!empty($filters['transfer_type'])) {
            $query->where('transfer_type', $filters['transfer_type']);
        }

        // Lọc theo tìm kiếm
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('content', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($filters) {
                      $invoiceQuery->where('code', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        $transactions = $query->get(['transfer_type', 'transfer_amount']);

        $inTransactions = $transactions->where('transfer_type', 'in');
        $outTransactions = $transactions->where('transfer_type', 'OUT');

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

    // Lấy danh sách tháng có giao dịch
    public function getMonths(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = 'Tháng ' . $i;
        }
        return $months;
    }

    // Lấy danh sách năm có giao dịch
    public function getYears(): array
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        if (empty($years)) {
            $years = [date('Y') => date('Y')];
        }

        return $years;
    }

    // Lấy danh sách loại giao dịch
    public function getTransferTypes(): array
    {
        return [
            'in' => 'Tiền vào (in)',
            'OUT' => 'Tiền ra (OUT)'
        ];
    }
}
