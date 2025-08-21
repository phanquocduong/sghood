<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Room;
use Illuminate\Database\Seeder;

class ContractTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả các contract
        $contracts = Contract::all();

        foreach ($contracts as $contract) {
            // Lấy room liên kết với contract
            $room = Room::find($contract->room_id);
            if (!$room) {
                continue; // Bỏ qua nếu không tìm thấy room
            }

            // Xác định số lượng tenant ngẫu nhiên, tổng tenant + 1 không vượt quá max_occupants
            $maxTenants = min($room->max_occupants - 1, 3); // Giới hạn tối đa 3, trừ 1 cho chủ hợp đồng
            $tenantCount = rand(0, $maxTenants);

            // Tạo dữ liệu giả cho contract tenant
            ContractTenant::factory()
                ->count($tenantCount)
                ->forContract($contract)
                ->create();
        }
    }
}
