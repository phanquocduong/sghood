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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->foreignId('meter_reading_id')->nullable()->constrained('meter_readings')->onDelete('set null');

            $table->enum('type', ['Đặt cọc', 'Hàng tháng']);
            $table->tinyInteger('month');
            $table->integer('year');

            $table->integer('electricity_fee');
            $table->integer('water_fee');
            $table->integer('parking_fee');
            $table->integer('junk_fee');
            $table->integer('internet_fee');
            $table->integer('service_fee');
            $table->integer('total_amount');

            $table->enum('status', ['Chưa trả', 'Đã trả', 'Đã hoàn tiền'])->default('Chưa trả');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
