<?php

namespace App\Services\Apis;

use App\Models\Transaction;

class TransactionService
{
    /**
     * Lấy danh sách giao dịch của user với bộ lọc và phân trang
     */
    public function getUserTransactions($userId, array $filters, $perPage = 10)
    {
        $query = Transaction::query()
            ->select([
                'transactions.id',
                'transactions.transaction_date',
                'transactions.content',
                'transactions.transfer_type',
                'transactions.transfer_amount',
                'transactions.reference_code',
                'invoices.code as invoice_code'
            ])
            ->join('invoices', 'transactions.invoice_id', '=', 'invoices.id')
            ->join('contracts', 'invoices.contract_id', '=', 'contracts.id')
            ->where('contracts.user_id', $userId);

        // Áp dụng bộ lọc loại giao dịch
        if (!empty($filters['type'])) {
            $query->where('transactions.transfer_type', $filters['type']);
        }

        // Áp dụng sắp xếp
        switch ($filters['sort']) {
            case 'oldest':
                $query->orderBy('transactions.transaction_date', 'asc');
                break;
            case 'latest':
                $query->orderBy('transactions.transaction_date', 'desc');
                break;
            default:
                $query->orderBy('transactions.transaction_date', 'desc');
                break;
        }

        // Sử dụng paginate thay vì get
        $transactions = $query->paginate($perPage);

        return $transactions->through(function ($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date,
                'content' => $transaction->content,
                'transfer_type' => $transaction->transfer_type,
                'transfer_amount' => $transaction->transfer_amount,
                'reference_code' => $transaction->reference_code,
                'invoice_code' => $transaction->invoice_code,
            ];
        });
    }
}
