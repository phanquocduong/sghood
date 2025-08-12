<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy tất cả các invoice có type là 'Đặt cọc'
        $invoices = Invoice::where('type', 'Hàng tháng')->where('status', 'Đã trả')->get();

        foreach ($invoices as $invoice) {
            // Tạo reference_code ngẫu nhiên và unique
            $referenceCode = Str::random(10);

            // Đảm bảo reference_code là unique
            while (Transaction::where('reference_code', $referenceCode)->exists()) {
                $referenceCode = Str::random(10);
            }

            // Tạo content theo định dạng yêu cầu
            $transactionDate = $invoice->updated_at;
            $timeString = $transactionDate->format('H:i:s');
            $content = sprintf(
                '%s-%s-%s GD %s-%s %s',
                rand(10000000000, 99999999999),
                rand(1000000000, 9999999999),
                'INV' . $invoice->code,
                rand(100000, 999999),
                $transactionDate->format('mdy'),
                $timeString
            );

            $created_at = $transactionDate->copy()->addSeconds(rand(1, 10));

            // Tạo transaction
            Transaction::create([
                'invoice_id' => $invoice->id,
                'transaction_date' => $transactionDate,
                'content' => $content,
                'transfer_type' => 'in',
                'transfer_amount' => $invoice->total_amount,
                'reference_code' => $referenceCode,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ]);
        }
    }
}
