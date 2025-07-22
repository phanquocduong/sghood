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
        // Cập nhật cột status để thêm giá trị 'Từ chối'
        Schema::table('viewing_schedules', function (Blueprint $table) {
            // Thay đổi cột status để bao gồm giá trị mới
            $table->enum('status', ['Chờ xác nhận', 'Đã xác nhận', 'Từ chối', 'Hoàn thành', 'Huỷ bỏ'])
                  ->default('Chờ xác nhận')
                  ->change();

            // Đổi tên cột cancellation_reason thành rejection_reason nếu tồn tại
            if (Schema::hasColumn('viewing_schedules', 'cancellation_reason')) {
                $table->renameColumn('cancellation_reason', 'rejection_reason');
            } else {
                // Nếu cột cancellation_reason không tồn tại, thêm cột rejection_reason
                $table->string('rejection_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewing_schedules', function (Blueprint $table) {
            // Hoàn tác: Đặt lại cột status về danh sách cũ
            $table->enum('status', ['Chờ xác nhận', 'Đã xác nhận', 'Huỷ bỏ', 'Hoàn thành'])
                  ->default('Chờ xác nhận')
                  ->change();

            // Hoàn tác: Đổi tên rejection_reason về cancellation_reason hoặc xóa nếu nó được thêm mới
            if (Schema::hasColumn('viewing_schedules', 'rejection_reason')) {
                if (Schema::hasColumn('viewing_schedules', 'cancellation_reason')) {
                    $table->dropColumn('rejection_reason');
                } else {
                    $table->renameColumn('rejection_reason', 'cancellation_reason');
                }
            }
        });
    }
};
