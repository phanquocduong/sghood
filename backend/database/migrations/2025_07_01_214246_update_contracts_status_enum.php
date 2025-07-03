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
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status
            ENUM(
                'Chờ xác nhận',
                'Chờ duyệt',
                'Chờ chỉnh sửa',
                'Chờ ký',
                'Chờ thanh toán tiền cọc',
                'Hoạt động',
                'Kết thúc',
                'Huỷ bỏ'
            ) DEFAULT 'Chờ xác nhận'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback to previous ENUM definition (without 'Chờ thanh toán tiền cọc')
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status
            ENUM(
                'Chờ xác nhận',
                'Chờ duyệt',
                'Chờ chỉnh sửa',
                'Chờ ký',
                'Hoạt động',
                'Kết thúc',
                'Huỷ bỏ'
            ) DEFAULT 'Chờ xác nhận'");
    }
};
