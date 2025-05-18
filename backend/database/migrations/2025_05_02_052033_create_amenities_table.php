<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['Nhà trọ', 'Phòng trọ']);
            $table->integer('order')->nullable();
            $table->enum('status', ['Hoạt động', 'Không hoạt động'])->default('Hoạt động');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('motel_amenities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motel_id');
            $table->unsignedBigInteger('amenity_id');
            $table->timestamps();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade');
        });

        Schema::create('room_amenities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('amenity_id');
            $table->timestamps();

            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_amenities');
        Schema::dropIfExists('motel_amenities');
        Schema::dropIfExists('amenities');
    }
};
