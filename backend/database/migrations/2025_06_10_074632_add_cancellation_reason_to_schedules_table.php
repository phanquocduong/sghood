<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationReasonToSchedulesTable extends Migration
{
    public function up()
    {
        Schema::table('viewing_schedules', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('viewing_schedules', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
    }
}