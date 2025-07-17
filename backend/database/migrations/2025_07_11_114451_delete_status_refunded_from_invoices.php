<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DeleteStatusRefundedFromInvoices extends Migration
{
    public function up(): void
    {
        // Trước khi thay đổi ENUM, cập nhật lại các dòng đang có giá trị 'Đã hoàn tiền'
        DB::table('invoices')
            ->where('status', 'Đã hoàn tiền')
            ->update(['status' => 'Đã trả']); // hoặc 'Chưa trả' tùy nghiệp vụ

        // Thay đổi ENUM: bỏ 'Đã hoàn tiền'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Chưa trả', 'Đã trả') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa trả'");
    }

    public function down(): void
    {
        // Khôi phục ENUM ban đầu
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('Chưa trả', 'Đã trả', 'Đã hoàn tiền') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa trả'");
    }
}
