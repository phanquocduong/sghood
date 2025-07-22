<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUsersSuperAdminRole extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Xoá cột is_super_admin
            $table->dropColumn('is_super_admin');
        });

        // Thêm giá trị mới vào ENUM role
        DB::statement("ALTER TABLE `users` CHANGE `role` `role` ENUM('Người đăng ký', 'Người thuê', 'Quản trị viên', 'Super admin') NOT NULL DEFAULT 'Người đăng ký'");
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm lại cột is_super_admin
            $table->boolean('is_super_admin')->default(0);
        });

        // Khôi phục ENUM role cũ
        DB::statement("ALTER TABLE `users` CHANGE `role` `role` ENUM('Người đăng ký', 'Người thuê', 'Quản trị viên') NOT NULL DEFAULT 'Người đăng ký'");
    }
}

