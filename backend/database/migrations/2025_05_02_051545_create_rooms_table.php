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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motel_id');
            $table->string('name', 50);
            $table->integer('price');
            $table->decimal('area', 5, 2);
            $table->enum('status', ['Còn trống', 'Đã thuê', 'Đang sửa', 'Ẩn'])->default('Còn trống');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->string('image_url');
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('rooms');
    }
};
