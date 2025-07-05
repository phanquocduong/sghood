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
        Schema::table('viewing_schedules', function (Blueprint $table) {
            // Xóa khóa ngoại và cột room_id
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');

            // Thêm cột motel_id và khóa ngoại liên kết với bảng motels
            $table->unsignedBigInteger('motel_id')->after('user_id');
            $table->foreign('motel_id')->references('id')->on('motels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewing_schedules', function (Blueprint $table) {
            // Xóa khóa ngoại và cột motel_id
            $table->dropForeign(['motel_id']);
            $table->dropColumn('motel_id');

            // Thêm lại cột room_id và khóa ngoại liên kết với bảng rooms
            $table->unsignedBigInteger('room_id')->after('user_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
        });
    }
};
