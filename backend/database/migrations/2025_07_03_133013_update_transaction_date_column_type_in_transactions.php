<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cần đổi kiểu thủ công vì Laravel không hỗ trợ trực tiếp `ON UPDATE CURRENT_TIMESTAMP`
        DB::statement("
            ALTER TABLE `transactions`
            MODIFY `transaction_date` TIMESTAMP
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trả lại kiểu cũ (string) nếu cần rollback
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_date')->change();
        });
    }
};
