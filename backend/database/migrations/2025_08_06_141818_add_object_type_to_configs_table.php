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
             $table->enum('config_type', ['TEXT', 'URL', 'HTML', 'JSON', 'IMAGE', 'BANK', 'OBJECT'])
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
            // Rollback về enum cũ (không có 'OBJECT')
            $table->enum('config_type', ['TEXT', 'URL', 'HTML', 'JSON', 'IMAGE', 'BANK'])
                  ->default('TEXT')
                  ->change();
        });
    }
};
