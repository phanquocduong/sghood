<?php

namespace App\Services\Apis;

use App\Models\Transaction;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến giao dịch.
 */
class TransactionService
{
    /**
     * Lấy danh sách giao dịch của người dùng với bộ lọc và phân trang.
     *
     * @param int $userId ID của người dùng
     * @param array $filters Mảng chứa các bộ lọc (sort, type)
     * @param int $perPage Số lượng giao dịch mỗi trang
     * @return \Illuminate\Pagination\LengthAwarePaginator Danh sách giao dịch đã phân trang
     */
    public function getUserTransactions($userId, array $filters, $perPage = 10)
    {
        // Xây dựng truy vấn lấy giao dịch với thông tin liên quan
        $query = Transaction::query()
            ->select([
                'transactions.id', // ID giao dịch
                'transactions.transaction_date', // Ngày giao dịch
                'transactions.content', // Nội dung giao dịch
                'transactions.transfer_type', // Loại giao dịch
                'transactions.transfer_amount', // Số tiền giao dịch
                'transactions.reference_code', // Mã tham chiếu
                'invoices.code as invoice_code' // Mã hóa đơn
            ])
            ->join('invoices', 'transactions.invoice_id', '=', 'invoices.id') // Kết nối với bảng hóa đơn
            ->join('contracts', 'invoices.contract_id', '=', 'contracts.id') // Kết nối với bảng hợp đồng
            ->where('contracts.user_id', $userId); // Lọc theo ID người dùng

        // Áp dụng bộ lọc loại giao dịch
        if (!empty($filters['type'])) {
            $query->where('transactions.transfer_type', $filters['type']); // Lọc theo loại giao dịch
        }

        // Áp dụng sắp xếp dựa trên tiêu chí
        switch ($filters['sort']) {
            case 'oldest':
                $query->orderBy('transactions.transaction_date', 'asc'); // Sắp xếp theo ngày giao dịch tăng dần
                break;
            case 'latest':
                $query->orderBy('transactions.transaction_date', 'desc'); // Sắp xếp theo ngày giao dịch giảm dần
                break;
            default:
                $query->orderBy('transactions.transaction_date', 'desc'); // Sắp xếp mặc định theo ngày giao dịch giảm dần
                break;
        }

        // Thực hiện phân trang
        $transactions = $query->paginate($perPage);

        // Biến đổi dữ liệu giao dịch để trả về định dạng mong muốn
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
