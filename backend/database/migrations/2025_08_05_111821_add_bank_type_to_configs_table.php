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
        Schema::table('configs', function (Blueprint $table) {
            // Thay đổi enum để thêm 'BANK'
            $table->enum('config_type', ['TEXT', 'URL', 'HTML', 'JSON', 'IMAGE', 'BANK'])
                  ->default('TEXT')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            // Rollback về enum cũ (không có 'BANK')
            $table->enum('config_type', ['TEXT', 'URL', 'HTML', 'JSON', 'IMAGE'])
                  ->default('TEXT')
                  ->change();
        });
    }
};