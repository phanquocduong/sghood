<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motels', function (Blueprint $table) {
            $table->integer('electricity_fee')->default(0)->after('description');
            $table->integer('water_fee')->default(0)->after('electricity_fee');
            $table->integer('parking_fee')->default(0)->after('water_fee');
            $table->integer('junk_fee')->default(0)->after('parking_fee');
            $table->integer('internet_fee')->default(0)->after('junk_fee');
            $table->integer('service_fee')->default(0)->after('internet_fee');
        });
    }

    public function down(): void
    {
        Schema::table('motels', function (Blueprint $table) {
            $table->dropColumn([
                'electricity_fee',
                'water_fee',
                'parking_fee',
                'junk_fee',
                'internet_fee',
                'service_fee'
            ]);
        });
    }
};
