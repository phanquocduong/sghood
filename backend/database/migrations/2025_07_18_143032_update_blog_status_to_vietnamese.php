<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateBlogStatusToVietnamese extends Migration
{
    public function up(): void
    {
        // B1: Đổi ENUM cột status: chấp nhận cả tiếng Anh & tiếng Việt tạm thời
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN status ENUM('draft', 'published', 'Nháp', 'Đã xuất bản')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft'
        ");

        // B2: Cập nhật dữ liệu cũ sang tiếng Việt
        DB::table('blogs')
            ->where('status', 'draft')
            ->update(['status' => 'Nháp']);

        DB::table('blogs')
            ->where('status', 'published')
            ->update(['status' => 'Đã xuất bản']);

        // B3: Xoá tiếng Anh khỏi ENUM
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN status ENUM('Nháp', 'Đã xuất bản')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nháp'
        ");
    }

    public function down(): void
    {
        // B1: Đổi ENUM cột status: chấp nhận cả tiếng Anh & tiếng Việt tạm thời
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN status ENUM('draft', 'published', 'Nháp', 'Đã xuất bản')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Nháp'
        ");

        // B2: Cập nhật dữ liệu về tiếng Anh
        DB::table('blogs')
            ->where('status', 'Nháp')
            ->update(['status' => 'draft']);

        DB::table('blogs')
            ->where('status', 'Đã xuất bản')
            ->update(['status' => 'published']);

        // B3: Xoá tiếng Việt khỏi ENUM
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN status ENUM('draft', 'published')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft'
        ");
    }
}
