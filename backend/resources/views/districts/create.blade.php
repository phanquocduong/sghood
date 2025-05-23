@extends('layouts.app')

@section('title', 'Thêm khu vực')

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
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Thêm khu vực') }}</h6>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('districts.store') }}" method="POST" enctype="multipart/form-data" id="districtForm">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên khu vực</label>
                        <input type="text" class="form-control shadow-sm" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold text-primary">Ảnh khu vực</label>
                        <input type="file" class="form-control shadow-sm" id="images" name="image" accept="image/*">
                        <div id="image-preview" class="row g-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('districts.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Quay lại</a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 15px;
    }

    .card-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    #image-preview img {
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 10px;
        transition: transform 0.3s;
    }

    #image-preview img:hover {
        transform: scale(1.1);
    }

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }
</style>

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
@endsection