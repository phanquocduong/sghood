@extends('layouts.app')

@section('title', 'Chỉnh sửa khu vực')

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
            <h6 class="mb-0 fw-bold">{{ __('Chỉnh sửa khu vực') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('districts.update', $district->id) }}" method="POST" enctype="multipart/form-data" id="districtForm" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên khu vực</label>
                        <input type="text" class="form-control shadow-sm {{ $errors->has('name') ? 'is-invalid' : '' }}" id="name" name="name" value="{{ old('name', $district->name) }}" required>
                        @if ($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="image" class="form-label fw-bold text-primary">Ảnh khu vực hiện tại</label>
                        <div class="mb-3">
                            @if (isset($district->image))
                                <img src="{{ $district->image }}" class="img-fluid rounded shadow-sm existing-image" alt="District Image" style="max-height: 150px; object-fit: cover; transition: transform 0.3s;">
                            @else
                                <p class="text-muted">Chưa có hình ảnh nào.</p>
                            @endif
                        </div>
                        <label for="image" class="form-label fw-bold text-primary">Ảnh mới</label>
                        <input type="file" class="form-control shadow-sm {{ $errors->has('image') ? 'is-invalid' : '' }}" id="images" name="image" accept="image/*">
                        @if ($errors->has('image'))
                            <div class="invalid-feedback">
                                {{ $errors->first('image') }}
                            </div>
                        @endif
                        <div id="image-preview" class="row g-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('districts.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Quay lại</a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Cập nhật</button>
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

    #image-preview img, .existing-image {
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 10px;
        transition: transform 0.3s;
    }

    #image-preview img:hover, .existing-image:hover {
        transform: scale(1.1);
    }

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }
</style>
@endsection