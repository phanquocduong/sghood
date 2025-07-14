@extends('layouts.app')

@section('title', 'Tạo bài viết')

@section('content')
    <div class="container">
        {{-- <div class="text-center"> --}}
        <div>
            <h1>{{ $blogs->title }}</h1>
            <p>{!! $blogs->content !!}</p>
            <p><small>Created at: {{ $blogs->created_at }}</small></p>

            <!-- Nút Sửa bài viết -->
            <div class="d-flex justify-content-end mt-4 gap-2">
                <a href="{{ route('blogs.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Quay lại</a>
                <a href="{{ route('blogs.edit', $blogs->id) }}" class="btn btn-success shadow-sm " style="transition: all 0.3s;"><i class="fas fa-edit me-1"></i>Sửa</a>
            </div>
        </div>
    </div>
@endsection
