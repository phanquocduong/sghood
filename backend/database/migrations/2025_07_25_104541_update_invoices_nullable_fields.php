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
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('month')->nullable()->change();
            $table->integer('year')->nullable()->change();
            $table->integer('electricity_fee')->nullable()->change();
            $table->integer('water_fee')->nullable()->change();
            $table->integer('parking_fee')->nullable()->change();
            $table->integer('junk_fee')->nullable()->change();
            $table->integer('internet_fee')->nullable()->change();
            $table->integer('service_fee')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->tinyInteger('month')->nullable(false)->change();
            $table->integer('year')->nullable(false)->change();
            $table->integer('electricity_fee')->nullable(false)->change();
            $table->integer('water_fee')->nullable(false)->change();
            $table->integer('parking_fee')->nullable(false)->change();
            $table->integer('junk_fee')->nullable(false)->change();
            $table->integer('internet_fee')->nullable(false)->change();
            $table->integer('service_fee')->nullable(false)->change();
        });
    }
};
