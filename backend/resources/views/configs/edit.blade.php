@extends('layouts.app')

@section('title', 'Sửa cấu hình')

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
            <h6 class="mb-0 fw-bold">{{ __('Sửa cấu hình') }}</h6>
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

            <form action="{{ route('configs.update', $config->id) }}" method="POST" id="configEditForm" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="config_key" class="form-label fw-bold text-primary">Khóa</label>
                        <input type="text" class="form-control shadow-sm" id="config_key" name="config_key" value="{{ old('config_key', $config->config_key) }}" required>
                    </div>
                    <div class="col-12">
                        <label for="config_value" class="form-label fw-bold text-primary">Giá trị</label>
                        <textarea class="form-control shadow-sm" id="config_value" name="config_value" rows="3" required>{{ old('config_value', $config->config_value) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <input type="text" class="form-control shadow-sm" id="description" name="description" value="{{ old('description', $config->description) }}">
                    </div>
                    <div class="col-12">
                        <label for="config_type" class="form-label fw-bold text-primary">Loại</label>
                        <select class="form-select shadow-sm" id="config_type" name="config_type" required>
                            <option value="TEXT" {{ old('config_type', $config->config_type) == 'TEXT' ? 'selected' : '' }}>TEXT</option>
                            <option value="URL" {{ old('config_type', $config->config_type) == 'URL' ? 'selected' : '' }}>URL</option>
                            <option value="HTML" {{ old('config_type', $config->config_type) == 'HTML' ? 'selected' : '' }}>HTML</option>
                            <option value="JSON" {{ old('config_type', $config->config_type) == 'JSON' ? 'selected' : '' }}>JSON</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('configs.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Cập nhật cấu hình</button>
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