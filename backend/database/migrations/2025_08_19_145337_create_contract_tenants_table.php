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
        Schema::create('contract_tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->date('birthdate')->nullable();
            $table->string('address')->nullable();
            $table->string('identity_document');
            $table->string('relation_with_primary')->nullable();
            $table->enum('status', ['Chờ duyệt', 'Đã duyệt', 'Từ chối', 'Huỷ bỏ', 'Đang ở', 'Đã rời đi'])->default('Chờ duyệt');
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_tenants');
    }
};
