<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToConfigsTable extends Migration
{
    public function up()
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('configs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}