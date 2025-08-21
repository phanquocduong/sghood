<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContractTenantFactory extends Factory
{
    protected $model = \App\Models\ContractTenant::class;

    public function definition(): array
    {
        // Đảm bảo sử dụng tiếng Việt cho dữ liệu ngẫu nhiên
        $this->faker->addProvider(new \Faker\Provider\vi_VN\Person($this->faker));
        $this->faker->addProvider(new \Faker\Provider\vi_VN\Address($this->faker));

        $gender = $this->faker->randomElement(['Nam', 'Nữ', 'Khác']);
        $name = $this->faker->name(); // Tên tiếng Việt
        $emailBase = Str::slug($name, '');

        return [
            'contract_id' => null, // Sẽ được gán trong seeder
            'name' => $name,
            'phone' => '+84' . $this->faker->unique()->numerify('9########'),
            'email' => $emailBase . rand(100, 999) . '@gmail.com',
            'gender' => $gender,
            'birthdate' => $this->faker->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'address' => $this->faker->address(), // Địa chỉ tiếng Việt
            'identity_document' => 'images/tenants/tenant-1-1755696946-0.webp.enc|images/tenants/tenant-1-1755696946-1.webp.enc',
            'relation_with_primary' => $this->faker->randomElement(['Vợ/Chồng', 'Con', 'Anh/Chị/Em', 'Bạn bè', 'Khác']),
            'status' => 'Đang ở',
            'rejection_reason' => null,
        ];
    }

    public function forContract(Contract $contract): self
    {
        $createdAt = Carbon::parse($contract->created_at);
        return $this->state([
            'contract_id' => $contract->id,
            'created_at' => $createdAt->addHours(2)->toDateTimeString(),
            'updated_at' => $createdAt->addHours(3)->toDateTimeString(), // Sửa lại để updated_at sau created_at
        ]);
    }
}
