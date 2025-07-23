<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            // Thêm cột canceled_at
            $table->timestamp('canceled_at')->nullable()->after('note');

            // Bỏ cột qr_code_path
            $table->dropColumn('qr_code_path');

            // Thay đổi enum inventory_status: loại bỏ 'Huỷ bỏ'
            $table->enum('inventory_status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Kiểm kê lại'])
                ->default('Chờ kiểm kê')
                ->change();

            // Thay đổi enum refund_status: loại bỏ 'Huỷ bỏ'
            $table->enum('refund_status', ['Chờ xử lý', 'Đã xử lý'])
                ->default('Chờ xử lý')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            // Khôi phục cột qr_code_path
            $table->string('qr_code_path')->nullable()->after('bank_info');

            // Xóa cột canceled_at
            $table->dropColumn('canceled_at');

            // Khôi phục enum inventory_status với giá trị 'Huỷ bỏ'
            $table->enum('inventory_status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Kiểm kê lại', 'Huỷ bỏ'])
                ->default('Chờ kiểm kê')
                ->change();

            // Khôi phục enum refund_status với giá trị 'Huỷ bỏ'
            $table->enum('refund_status', ['Chờ xử lý', 'Đã xử lý', 'Huỷ bỏ'])
                ->default('Chờ xử lý')
                ->change();
        });
    }
};
