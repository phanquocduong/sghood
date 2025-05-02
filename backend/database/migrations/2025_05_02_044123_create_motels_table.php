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
        Schema::create('motels', function (Blueprint $table) {
            $table->id();
            $table->string('address', 100);
            $table->unsignedBigInteger('district_id');
            $table->string('map_embed_url', 1000);
            $table->text('description')->nullable();
            $table->enum('status', ['Hoạt động', 'Không hoạt động'])->default('Hoạt động');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });

        Schema::create('motel_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motel_id');
            $table->string('image_url', 255);
            $table->timestamps();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });

        Schema::create('motel_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motel_id');
            $table->enum('fee_type', ['Điện', 'Nước', 'Giữ xe', 'Rác', 'Internet', 'Dịch vụ']);
            $table->integer('fee_amount');
            $table->timestamps();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motel_fees');
        Schema::dropIfExists('motel_images');
        Schema::dropIfExists('motels');
    }
};
