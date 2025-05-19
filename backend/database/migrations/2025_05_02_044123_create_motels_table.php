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
            $table->unsignedBigInteger('district_id');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('address');
            $table->string('map_embed_url', 1000);
            $table->text('description')->nullable();
            $table->unsignedInteger('electricity_fee');
            $table->unsignedInteger('water_fee');
            $table->unsignedInteger('parking_fee');
            $table->unsignedInteger('junk_fee');
            $table->unsignedInteger('internet_fee');
            $table->unsignedInteger('service_fee');
            $table->enum('status', ['Hoạt động', 'Không hoạt động'])->default('Hoạt động');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });

        Schema::create('motel_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motel_id');
            $table->string('image_url');
            $table->boolean('is_main')->default(false);
            $table->timestamps();

            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motel_images');
        Schema::dropIfExists('motels');
    }
};
