<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function createDepositInvoice(Contract $contract): Invoice
    {
        $code = 'INV' . $contract->id . now()->format('YmdHis');
        return Invoice::create([
            'contract_id' => $contract->id,
            'type' => 'Đặt cọc',
            'month' => now()->month,
            'year' => now()->year,
            'total_amount' => $contract->deposit_amount,
            'status' => 'Chưa trả',
            'code' => $code,
        ]);
    }

    // Trong InvoiceService.php
    public function checkStatus(string $code, int $userId): array
    {
        $invoice = Invoice::where('code', $code)
            ->whereHas('contract', fn($query) => $query->where('user_id', $userId))
            ->select('status')
            ->firstOrFail();

        return ['status' => $invoice->status];
    }

    public function processWebhook(object $data): Invoice
    {
        if (!$data->code) {
            throw new \Exception('Invalid invoice number format');
        }

        $invoice = Invoice::where('code', $data->code)
            ->where('type', 'Đặt cọc')
            ->where('status', 'Chưa trả')
            ->firstOrFail();

        if ($invoice->total_amount != $data->transferAmount) {
            throw new \Exception('Amount mismatch');
        }

        $transaction = Transaction::create([
            'id' => $data->id,
            'invoice_id' => $invoice->id,
            'transaction_date' => $data->transactionDate,
            'content' => $data->content,
            'transfer_type' => $data->transferType,
            'transfer_amount' => $data->transferAmount,
            'reference_code' => $data->referenceCode,
        ]);

        $invoice->update(['status' => 'Đã trả']);

        if ($contract = $invoice->contract) {
            $contract->update(['status' => 'Hoạt động']);
        }

        Log::info('Thanh toán thành công', ['invoice_id' => $invoice->id, 'transaction_id' => $transaction->id]);
        return $invoice;
    }
}
