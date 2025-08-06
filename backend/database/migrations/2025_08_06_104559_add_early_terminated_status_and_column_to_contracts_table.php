<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm option 'Kết thúc sớm' vào ENUM của status
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status
            ENUM(
                'Chờ xác nhận',
                'Chờ duyệt',
                'Chờ duyệt thủ công',
                'Chờ chỉnh sửa',
                'Chờ ký',
                'Chờ thanh toán tiền cọc',
                'Hoạt động',
                'Kết thúc',
                'Kết thúc sớm',
                'Huỷ bỏ'
            ) DEFAULT 'Chờ xác nhận'");

        // Thêm cột early_terminated_at
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp('early_terminated_at')->nullable()->after('signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback ENUM về trạng thái cũ (bỏ 'Kết thúc sớm')
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

        // Xoá cột early_terminated_at
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('early_terminated_at');
        });
    }
};
