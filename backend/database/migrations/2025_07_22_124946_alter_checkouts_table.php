<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('checkouts');
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->date('check_out_date');
            $table->json('inventory_details')->nullable();
            $table->integer('deduction_amount')->default(0);
            $table->integer('final_refunded_amount')->nullable();
            $table->enum('inventory_status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Kiểm kê lại', 'Huỷ bỏ'])->default('Chờ kiểm kê');
            $table->enum('user_confirmation_status', ['Chưa xác nhận', 'Đồng ý', 'Từ chối'])->default('Chưa xác nhận');
            $table->string('user_rejection_reason', 1000)->nullable();
            $table->boolean('has_left')->default(false);
            $table->json('images')->nullable();
            $table->string('note')->nullable();
            $table->json('bank_info')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('refund_status', ['Chờ xử lý', 'Đã xử lý', 'Huỷ bỏ'])->default('Chờ xử lý');
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
