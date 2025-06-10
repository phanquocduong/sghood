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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 255)->unique();
            $table->text('config_value');
            $table->string('description', 255)->nullable();
            $table->enum('config_type', ['TEXT', 'URL', 'HTML', 'JSON', 'IMAGE'])->default('TEXT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
