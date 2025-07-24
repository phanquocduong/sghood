<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Lấy tất cả các contract có status 'Hoạt động'
        $contracts = Contract::where('status', 'Hoạt động')->get();

        foreach ($contracts as $contract) {
            // Tạo invoice cho contract
            Invoice::create([
                'code' => 'INV' . $contract->id . now()->format('YmdHis'),
                'contract_id' => $contract->id,
                'meter_reading_id' => null,
                'type' => 'Đặt cọc',
                'month' => $contract->created_at->month,
                'year' => $contract->created_at->year,
                'electricity_fee' => 0,
                'water_fee' => 0,
                'parking_fee' => 0,
                'junk_fee' => 0,
                'internet_fee' => 0,
                'service_fee' => 0,
                'total_amount' => $contract->deposit_amount,
                'status' => 'Đã trả',
                'refunded_at' => null,
                'created_at' => $contract->created_at->addMinutes(rand(1, 10)), // Cộng thêm 1-10 phút ngẫu nhiên
                'updated_at' => $contract->created_at->addMinutes(rand(11, 20)), // Cộng thêm 11-20 phút ngẫu nhiên
            ]);
        }
    }
}
