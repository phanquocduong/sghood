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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('checkout_id')->constrained()->onDelete('cascade');
            $table->integer('deposit_amount'); // Số tiền cọc yêu cầu hoàn
            $table->integer('deduction_amount')->default(0); // Số tiền khấu trừ
            $table->integer('final_amount')->nullable(); // Số tiền hoàn thực tế
            $table->json('bank_info')->nullable(); // Thông tin tài khoản ngân hàng (mã hóa)
            $table->enum('status', ['Chờ xử lý', 'Đã duyệt', 'Từ chối'])->default('Chờ xử lý'); // Chờ xử lý, Đã duyệt, Từ chối
            $table->text('rejection_reason')->nullable();
            $table->string('transaction_id')->nullable(); // ID giao dịch khi hoàn tiền
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
