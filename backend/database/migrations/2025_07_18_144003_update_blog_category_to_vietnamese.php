<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // B1: Đổi ENUM tạm thời (cho phép cả tiếng Anh & tiếng Việt)
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN category ENUM(
                'news', 'guide', 'promotion', 'law', 'experience',
                'Tin tức', 'Hướng dẫn', 'Khuyến mãi', 'Pháp luật', 'Kinh nghiệm'
            )
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news'
        ");

        // B2: Cập nhật dữ liệu sang tiếng Việt
        DB::table('blogs')->where('category', 'news')->update(['category' => 'Tin tức']);
        DB::table('blogs')->where('category', 'guide')->update(['category' => 'Hướng dẫn']);
        DB::table('blogs')->where('category', 'promotion')->update(['category' => 'Khuyến mãi']);
        DB::table('blogs')->where('category', 'law')->update(['category' => 'Pháp luật']);
        DB::table('blogs')->where('category', 'experience')->update(['category' => 'Kinh nghiệm']);

        // B3: Thu hẹp ENUM chỉ còn tiếng Việt
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN category ENUM(
                'Tin tức', 'Hướng dẫn', 'Khuyến mãi', 'Pháp luật', 'Kinh nghiệm'
            )
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tin tức'
        ");
    }

    public function down(): void
    {
        // B1: Đổi ENUM tạm thời (cho phép cả tiếng Việt & tiếng Anh)
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN category ENUM(
                'Tin tức', 'Hướng dẫn', 'Khuyến mãi', 'Pháp luật', 'Kinh nghiệm',
                'news', 'guide', 'promotion', 'law', 'experience'
            )
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tin tức'
        ");

        // B2: Cập nhật dữ liệu về tiếng Anh
        DB::table('blogs')->where('category', 'Tin tức')->update(['category' => 'news']);
        DB::table('blogs')->where('category', 'Hướng dẫn')->update(['category' => 'guide']);
        DB::table('blogs')->where('category', 'Khuyến mãi')->update(['category' => 'promotion']);
        DB::table('blogs')->where('category', 'Pháp luật')->update(['category' => 'law']);
        DB::table('blogs')->where('category', 'Kinh nghiệm')->update(['category' => 'experience']);

        // B3: Thu hẹp ENUM chỉ còn tiếng Anh
        DB::statement("
            ALTER TABLE blogs
            MODIFY COLUMN category ENUM(
                'news', 'guide', 'promotion', 'law', 'experience'
            )
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news'
        ");
    }
};
