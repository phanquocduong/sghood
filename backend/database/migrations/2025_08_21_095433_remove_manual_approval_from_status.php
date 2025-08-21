<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xoá 'Chờ duyệt thủ công' khỏi ENUM của status
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status
            ENUM(
                'Chờ xác nhận',
                'Chờ duyệt',
                'Chờ ký',
                'Chờ thanh toán tiền cọc',
                'Hoạt động',
                'Kết thúc',
                'Kết thúc sớm',
                'Huỷ bỏ'
            ) DEFAULT 'Chờ xác nhận'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Thêm lại 'Chờ duyệt thủ công' vào ENUM của status nếu rollback
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status
            ENUM(
                'Chờ xác nhận',
                'Chờ duyệt',
                'Chờ duyệt thủ công',
                'Chờ ký',
                'Chờ thanh toán tiền cọc',
                'Hoạt động',
                'Kết thúc',
                'Kết thúc sớm',
                'Huỷ bỏ'
            ) DEFAULT 'Chờ xác nhận'");
    }
};
