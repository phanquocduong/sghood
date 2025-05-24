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
            $table->string('name');
            $table->unsignedInteger('price');
            $table->decimal('area', 5, 2);
            $table->string('description', 1000)->nullable();
            $table->enum('status', ['Trống', 'Đã thuê', 'Sửa chữa', 'Ẩn'])->default('Trống');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->string('image_url');
            $table->boolean('is_main')->default(false);
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
