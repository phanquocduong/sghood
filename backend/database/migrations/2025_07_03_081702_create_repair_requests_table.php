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
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('title');
            $table->string('description', 1000);
            $table->string('images', 500);
            $table->enum('status', ['Chờ xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Huỷ bỏ'])->default('Chờ xác nhận');
            $table->string('cancellation_reason')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('repaired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
