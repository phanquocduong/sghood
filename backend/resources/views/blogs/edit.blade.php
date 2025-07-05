@extends('layouts.app')

@section('title', 'Sửa bài viết')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid py-5 px-4">
    <!-- Display session flash messages -->
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Sửa bài viết') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" value="{{ old('title', $blog->title) }}" name="title" required>
                    @error('title')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Ảnh bìa bài viết</label>
                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" value="{{ old('thumbnail') }}" name="thumbnail" accept="image/*">
                    <img src="{{ $blog->thumbnail }}" alt="" style="width:100px;height:100px">
                    @error('thumbnail')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung</label>
                    <textarea class="form-control @error('content') is-invalid @enderror content" id="content"  name="content" rows="5" required>{{ old('content', $blog->content) }}</textarea>
                     @error('content')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Người đăng</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                    <input type="hidden" name="author_id" value="{{ Auth::id() }}">
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" {{ $blog->status ? 'selected' : ''}}>Nháp</option>
                        <option value="published" {{ $blog->status ? 'selected' : ''}}>Đã xuất bản</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('blogs.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <input type="submit" class="btn btn-primary shadow-sm" value="Cập nhật bài viết" style="transition: all 0.3s;"/>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('styles')
<link rel="stylesheet" href="{{ asset('css/create-blog.css') }}">
@endsection
