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
        Schema::table('transactions', function (Blueprint $table) {
            // Thêm cột refund_request_id là khóa ngoại, nullable
            $table->foreignId('refund_request_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            // Chỉnh sửa invoice_id thành nullable
            $table->foreignId('invoice_id')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Xóa cột refund_request_id
            $table->dropForeign(['refund_request_id']);
            $table->dropColumn('refund_request_id');

            // Khôi phục invoice_id về không nullable (trạng thái ban đầu)
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->onDelete('cascade')
                ->change();
        });
    }
};
