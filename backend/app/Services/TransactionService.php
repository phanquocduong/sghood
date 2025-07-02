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

        // Lọc theo ngày
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    // Lấy chi tiết giao dịch theo ID
    public function getTransactionById(int $id): ?Transaction
    {
        return Transaction::with(['invoice'])->find($id);
    }

    // Lấy thống kê giao dịch
    public function getTransactionStats(): array
    {
        $total = Transaction::count();
        $inCount = Transaction::where('transfer_type', 'in')->count();
        $outCount = Transaction::where('transfer_type', 'out')->count();

        $inAmount = Transaction::where('transfer_type', 'in')->sum('transfer_amount');
        $outAmount = Transaction::where('transfer_type', 'out')->sum('transfer_amount');
        $balance = $inAmount - $outAmount;

        return [
            'total' => $total,
            'in' => $inCount,
            'out' => $outCount,
            'balance' => $balance,
            'in_amount' => $inAmount,
            'out_amount' => $outAmount,
        ];
    }
}
