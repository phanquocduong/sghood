<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('user_admins', 'message_relationships');
    }

    public function down(): void
    {
        Schema::rename('message_relationships', 'user_admins');
    }
};

