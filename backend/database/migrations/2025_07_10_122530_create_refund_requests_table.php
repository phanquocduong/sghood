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
            $table->foreignId('checkout_id')->constrained()->onDelete('cascade')->unique();
            $table->integer('deposit_amount');
            $table->integer('deduction_amount')->default(0);
            $table->integer('final_amount')->nullable();
            $table->json('bank_info')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['Chờ xử lý', 'Đã xử lý', 'Huỷ bỏ'])->default('Chờ xử lý');
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
