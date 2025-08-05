@extends('layouts.app')

@section('title', 'Tạo bài viết')

@section('content')
<div class="container py-5">
    <div class="article-detail">
        <h1 class="article-title">{{ $blogs->title }}</h1>
        <p class="article-date"><i class="fas fa-clock me-1"></i> {{ $blogs->created_at->format('d/m/Y H:i') }}</p>

        <div class="article-content mt-4">
            {!! $blogs->content !!}
        </div>

        <div class="d-flex justify-content-end mt-4 gap-2">
            <a href="{{ route('blogs.index') }}" class="btn btn-secondary shadow-sm btn-hover">Quay lại</a>
            <a href="{{ route('blogs.edit', $blogs->id) }}" class="btn btn-success shadow-sm btn-hover">
                <i class="fas fa-edit me-1"></i>Sửa
            </a>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Vùng hiển thị bài viết */
    .article-detail {
        max-width: 900px;
        margin: 40px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        font-family: 'Roboto', sans-serif;
    }

    /* Tiêu đề bài viết */
    .article-detail h1 {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.3;
        color: #1e293b;
        margin-bottom: 20px;
    }

    /* Thông tin mô tả ngắn */
    .article-detail .excerpt {
        font-size: 16px;
        line-height: 1.6;
        color: #64748b;
        margin-bottom: 25px;
    }

    /* Hình ảnh bài viết */
    .article-detail img {
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin: 25px 0;
    }

    /* Nội dung bài viết */
    .article-detail p {
        font-size: 17px;
        line-height: 1.8;
        color: #334155;
        margin-bottom: 18px;
        text-align: justify;
    }

    /* Link trong nội dung */
    .article-detail a {
        color: #4f46e5;
        text-decoration: none;
        border-bottom: 1px solid #c7d2fe;
        transition: 0.2s;
    }

    .article-detail a:hover {
        color: #3730a3;
        border-bottom-color: #6366f1;
    }

    /* Responsive cho mobile */
    @media (max-width: 768px) {
        .article-detail {
            padding: 20px;
            margin: 20px;
        }

        .article-detail h1 {
            font-size: 24px;
        }
    }
</style>
@endsection
