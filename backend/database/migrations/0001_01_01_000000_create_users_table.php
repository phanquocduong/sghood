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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 15)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->date('birthdate');
            $table->string('avatar')->nullable();
            $table->string('front_id_card_image', 255)->nullable();
            $table->string('back_id_card_image', 255)->nullable();
            $table->enum('role', ['Người đăng ký', 'Người thuê', 'Quản trị viên'])->default('Người đăng ký');
            $table->enum('status', ['Hoạt động', 'Khoá'])->default('Hoạt động');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
