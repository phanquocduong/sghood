<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('motel_fees');
    }

    public function down(): void
    {
        Schema::create('motel_fees', function ($table) {
            $table->id();
            // Nếu bạn nhớ các cột, bạn có thể khai báo lại ở đây
            $table->timestamps();
        });
    }
};
