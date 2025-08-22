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
        Schema::table('contract_tenants', function (Blueprint $table) {
            // Make the identity_document column nullable
            $table->string('identity_document')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_tenants', function (Blueprint $table) {
            // Revert the identity_document column to not nullable
            $table->string('identity_document')->nullable(false)->change();
        });
    }
};
