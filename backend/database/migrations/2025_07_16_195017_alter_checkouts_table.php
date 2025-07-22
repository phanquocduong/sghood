<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            // Thêm các trường mới
            $table->integer('final_refunded_amount')->nullable()->after('deduction_amount');

            $table->enum('inventory_status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Kiểm kê lại', 'Huỷ bỏ'])->default('Chờ kiểm kê')->after('deduction_amount');

            $table->enum('user_confirmation_status', ['Chưa xác nhận', 'Đồng ý', 'Từ chối'])->default('Chưa xác nhận')->after('inventory_status');
            $table->string('user_rejection_reason', 500)->nullable()->after('user_confirmation_status');

            // Xóa trường deposit_refunded
            $table->dropColumn('deposit_refunded');
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            // Khôi phục trường deposit_refunded
            $table->boolean('deposit_refunded')->default(false);

            // Xóa các trường đã thêm
            $table->dropColumn([
                'final_refunded_amount',
                'inventory_status',
                'user_confirmation_status',
                'user_rejection_reason'
            ]);

            // Khôi phục trạng thái status cũ
            $table->enum('status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Huỷ bỏ'])->default('Chờ kiểm kê')->after('deduction_amount');
        });
    }
};
