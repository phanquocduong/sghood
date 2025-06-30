<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('transaction_date');
            $table->string('account_number');
            $table->string('sub_account')->nullable();
            $table->decimal('amount_in', 10, 2)->default(0);
            $table->decimal('amount_out', 10, 2)->default(0);
            $table->decimal('accumulated', 10, 2);
            $table->string('code');
            $table->string('transaction_content');
            $table->string('reference_number');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_transactions');
    }
};
