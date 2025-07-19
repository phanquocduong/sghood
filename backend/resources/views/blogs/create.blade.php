@extends('layouts.app')

@section('title', 'Tạo bài viết')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container-fluid py-5 px-4">
        <!-- Display session flash messages -->
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Thêm bài viết') }}</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('blogs.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                            value="{{ old('title') }}" name="title" required>
                        @error('title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Ảnh bìa bài viết</label>
                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail"
                            name="thumbnail" accept="image/*">
                        @error('thumbnail')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- preview image --}}
                        <div class="mt-3">
                            <img id="preview-thumbnail" src="" alt="Preview"
                                style="max-width: 200px; display: none; border: 1px solid #ddd; padding: 4px; border-radius: 4px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung</label>
                        <textarea class="form-control @error('content') is-invalid @enderror content" id="content" name="content"
                            rows="5" required>{{ old('content') }}</textarea>
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
                        <label class="form-label">Thể loại</label>
                        <select class="form-select" id="category" name="category">
                            <option value="Tin tức">Tin tức</option>
                            <option value="Hướng dẫn">Hướng dẫn</option>
                            <option value="Khuyến mãi">Khuyến mãi</option>
                            <option value="Pháp luật">Pháp luật</option>
                            <option value="Kinh nghiệm">Kinh nghiệm</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Nháp">Nháp</option>
                            <option value="Đã xuất bản">Đã xuất bản</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('blogs.index') }}" class="btn btn-secondary shadow-sm"
                            style="transition: all 0.3s;">Hủy</a>
                        {{-- <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm bài viết</button> --}}
                        <input type="submit" class="btn btn-primary shadow-sm" value="Thêm bài viết"
                            style="transition: all 0.3s;" />
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/create-blog.css') }}">
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('thumbnail');
    const preview = document.getElementById('preview-thumbnail');

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });
});
</script>
@endsection

