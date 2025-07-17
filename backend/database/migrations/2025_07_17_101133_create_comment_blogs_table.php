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
        Schema::create('comment_blogs', function (Blueprint $table) {
            $table->id(); // ID của comment
            $table->unsignedBigInteger('blog_id'); // Liên kết với bài viết
            $table->unsignedBigInteger('user_id'); // Người bình luận
            $table->text('content'); // Nội dung bình luận
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0); // Cho phép trả lời (comment con)
            $table->timestamp('created_at')->useCurrent(); // Ngày tạo
            $table->timestamp('updated_at')->nullable();

            // Foreign keys (tùy chọn, nếu bạn có bảng blogs và users)
            $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('comment_blogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_blogs');
    }
};
