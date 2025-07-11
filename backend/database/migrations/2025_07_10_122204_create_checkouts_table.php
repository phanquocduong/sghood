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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade')->unique();
            $table->date('check_out_date')->nullable();
            $table->json('inventory_details')->nullable();
            $table->integer('deduction_amount')->nullable();
            $table->enum('status', ['Chờ kiểm kê', 'Đã kiểm kê', 'Huỷ bỏ'])->default('Chờ kiểm kê');
            $table->boolean('deposit_refunded')->default(false);
            $table->boolean('has_left')->default(false);
            $table->json('images')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
