<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class RepairRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $imageString = '/storage/images/repair_requests/repair_68835a0761fec.webp|/storage/images/repair_requests/repair_68835a083eaa2.webp|/storage/images/repair_requests/repair_68835a084db27.webp|/storage/images/repair_requests/repair_68835a085798d.webp';
        $statuses = ['Đang thực hiện', 'Hoàn thành', 'Hoàn thành', 'Hoàn thành', 'Chờ xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Hoàn thành', 'Hoàn thành', 'Huỷ bỏ', 'Đang thực hiện', 'Hoàn thành', 'Hoàn thành', 'Hoàn thành'];
        $oldStatuses = ['Hoàn thành', 'Hoàn thành', 'Hoàn thành', 'Hoàn thành', 'Huỷ bỏ'];

        // Danh sách tiêu đề tiếng Việt tùy chỉnh
        $titles = [
            'Sửa chữa ống nước bị rò rỉ',
            'Khắc phục sự cố điện trong phòng',
            'Thay thế bóng đèn hỏng',
            'Sửa cửa phòng bị kẹt',
            'Bảo trì điều hòa không mát',
            'Sửa chữa tường bị nứt',
            'Xử lý vấn đề cống nghẹt',
            'Lắp đặt thêm ổ cắm điện',
            'Sửa khóa cửa bị hỏng',
            'Kiểm tra và sửa quạt trần',
        ];

        // Danh sách mô tả tiếng Việt tùy chỉnh
        $descriptions = [
            'Ống nước trong phòng tắm bị rò rỉ, gây ướt sàn nhà.',
            'Hệ thống điện trong phòng bị chập, đèn không sáng.',
            'Bóng đèn trần đã hỏng, cần thay mới gấp.',
            'Cửa phòng chính bị kẹt, khó mở và đóng.',
            'Điều hòa chạy nhưng không mát, cần kiểm tra gas.',
            'Tường phòng ngủ có vết nứt lớn, cần sửa chữa.',
            'Cống thoát nước bị tắc, gây mùi khó chịu.',
            'Cần lắp thêm ổ cắm điện cho thiết bị mới.',
            'Khóa cửa bị hỏng, không thể khóa từ bên ngoài.',
            'Quạt trần chạy yếu, cần kiểm tra và sửa chữa.',
        ];

        // Danh sách ghi chú tiếng Việt tùy chỉnh
        $notes = [
            'Vui lòng sửa chữa trong giờ hành chính.',
            'Cần hoàn thành trước cuối tuần này.',
            'Kiểm tra kỹ trước khi sửa để tránh tái phát.',
            'Sử dụng vật liệu chất lượng cao.',
            'Liên hệ trước khi đến sửa chữa.',
            null, // Cho phép ghi chú có thể là null
        ];

        for ($i = 0; $i < 50; $i++) { // Tạo 50 bản ghi
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');
            $isOld = $createdAt < Carbon::now()->subMonths(3);
            $status = $isOld ? $faker->randomElement($oldStatuses) : $faker->randomElement($statuses);

            $repairedAt = null;
            if ($status === 'Hoàn thành') {
                $repairedAt = Carbon::instance($createdAt)->addSeconds($faker->numberBetween(3600, 604800)); // +1 giờ đến 1 tuần
            }

            RepairRequest::create([
                'contract_id' => $faker->numberBetween(1, 44),
                'title' => $faker->randomElement($titles),
                'description' => $faker->randomElement($descriptions),
                'images' => $imageString,
                'status' => $status,
                'note' => $faker->randomElement($notes),
                'repaired_at' => $repairedAt,
                'created_at' => $createdAt,
                'updated_at' => $repairedAt ?? $createdAt,
            ]);
        }
    }
}
