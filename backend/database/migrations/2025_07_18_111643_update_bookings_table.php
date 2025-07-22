<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->renameColumn('cancellation_reason', 'rejection_reason');
            } else {
                $table->string('rejection_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'rejection_reason')) {
                if (Schema::hasColumn('bookings', 'cancellation_reason')) {
                    $table->dropColumn('rejection_reason');
                } else {
                    $table->renameColumn('rejection_reason', 'cancellation_reason');
                }
            }
        });
    }
};
