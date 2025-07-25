<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\ContractExtension;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractExtensionSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run()
    {
        // Lấy tất cả các contract có end_date < now()
        $contracts = Contract::where('end_date', '<', now())->get();

        foreach ($contracts as $contract) {
            // Random lựa chọn giữa gia hạn hoặc trả phòng
            $action = rand(0, 1) ? 'extend' : 'checkout';

            if ($action === 'extend') {
                // Lấy room liên kết với contract
                $room = $contract->room;

                // Random số tháng gia hạn (6, 12, hoặc 24 tháng)
                $additionalMonths = [6, 12, 24][array_rand([6, 12, 24])];
                $newEndDate = Carbon::parse($contract->end_date)->addMonths($additionalMonths);

                // Random giá thuê mới (tăng 100,000 hoặc 200,000)
                $priceIncrease = [100000, 200000][array_rand([100000, 200000])];
                $newRentalPrice = $room->price + $priceIncrease;

                // Random ngày gia hạn (trừ ngẫu nhiên 1-15 ngày từ end_date)
                $extensionDate = Carbon::parse($contract->end_date)->subDays(rand(1, 15));

                // Tạo nội dung HTML
                $content = <<<HTML
<div class="contract-document">
    <p><strong>Hợp đồng số: </strong>{$contract->id}</p>
    <p><strong>Ngày gia hạn: </strong>{$extensionDate->format('d/m/Y')}</p>
    <p><strong>Ngày kết thúc mới: </strong><span class="end-date">{$newEndDate->format('d/m/Y')}</span></p>
    <p><strong>Giá thuê mới: </strong>" . number_format($newRentalPrice, 0, ',', '.') . " VND</p>
    <p><em>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</em></p>
</div>
HTML;

                // Tạo record trong contract_extensions
                $contractExtension = ContractExtension::create([
                    'contract_id' => $contract->id,
                    'new_end_date' => $newEndDate,
                    'new_rental_price' => $newRentalPrice,
                    'content' => $content,
                    'status' => 'Hoạt động',
                    'rejection_reason' => null,
                    'created_at' => $extensionDate,
                    'updated_at' => $extensionDate->addMinutes(rand(1, 60)),
                ]);

                // Cập nhật price của room và rental_price/end_date của contract
                $room->update(['price' => $newRentalPrice]);
                $contract->update([
                    'rental_price' => $newRentalPrice,
                    'end_date' => $newEndDate,
                ]);
            } else {
                // Trường hợp checkout: cập nhật status của contract và role của user
                $contract->update(['status' => 'Kết thúc']);
                $contract->user->update(['role' => 'Người đăng ký']);
            }
        }
    }
}
