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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');                         // Tiêu đề bài viết
            $table->string('slug')->unique();               // Slug URL
            $table->text('excerpt')->nullable();            // Tóm tắt bài viết
            $table->longText('content');                    // Nội dung HTML/markdown
            $table->string('thumbnail')->nullable();        // Link ảnh thumbnail
            $table->enum('status', ['draft', 'published'])->default('draft'); // Trạng thái
            $table->unsignedBigInteger('author_id');        // ID người viết (liên kết với users)
            $table->timestamps();
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
