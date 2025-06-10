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

<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Sửa cấu hình') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('configs.update', $config->id) }}" method="POST" id="configEditForm" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="config_key" class="form-label fw-bold text-primary">Khóa<span style="color:red;">*</span></label>
                        <input type="text" class="form-control shadow-sm {{ $errors->has('config_key') ? 'is-invalid' : '' }}" id="config_key" name="config_key" value="{{ old('config_key', $config->config_key) }}" required>
                        @if ($errors->has('config_key'))
                            <div class="invalid-feedback">
                                {{ $errors->first('config_key') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="config_type" class="form-label fw-bold text-primary">Loại<span style="color:red;">*</span></label>
                        <select class="form-select shadow-sm {{ $errors->has('config_type') ? 'is-invalid' : '' }}" id="config_type" name="config_type" required onchange="toggleConfigValue()">
                            <option value="TEXT" {{ old('config_type', $config->config_type) == 'TEXT' ? 'selected' : '' }}>TEXT</option>
                            <option value="URL" {{ old('config_type', $config->config_type) == 'URL' ? 'selected' : '' }}>URL</option>
                            <option value="HTML" {{ old('config_type', $config->config_type) == 'HTML' ? 'selected' : '' }}>HTML</option>
                            <option value="JSON" {{ old('config_type', $config->config_type) == 'JSON' ? 'selected' : '' }}>JSON</option>
                            <option value="IMAGE" {{ old('config_type', $config->config_type) == 'IMAGE' ? 'selected' : '' }}>IMAGE</option>
                        </select>
                        @if ($errors->has('config_type'))
                            <div class="invalid-feedback">
                                {{ $errors->first('config_type') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="config_value" class="form-label fw-bold text-primary">Nội dung<span style="color:red;">*</span></label>
                        <div id="config_value_container">
                            @if(old('config_type', $config->config_type) == 'IMAGE')
                                <img src="{{ asset($config->config_value) }}" alt="Config Image" style="max-width: 300px; max-height: 300px; margin-bottom: 10px;" id="config_image_preview">
                                <input type="file" class="form-control shadow-sm {{ $errors->has('config_image') ? 'is-invalid' : '' }}" id="config_image" name="config_image" accept="image/*">
                            @else
                                <textarea class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}" id="config_value" name="config_value" rows="3" required>{{ old('config_value', $config->config_value) }}</textarea>
                            @endif
                        </div>
                        @if ($errors->has('config_value') || $errors->has('config_image'))
                            <div class="invalid-feedback d-block">
                                {{ $errors->first('config_value') ?: $errors->first('config_image') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <input type="text" class="form-control shadow-sm" id="description" name="description" value="{{ old('description', $config->description) }}">
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

<script>
    function toggleConfigValue() {
        const configType = document.getElementById('config_type').value;
        const configValueContainer = document.getElementById('config_value_container');
        const currentImage = "{{ old('config_type', $config->config_type) == 'IMAGE' ? asset($config->config_value) : '' }}";

        if (configType === 'IMAGE') {
            configValueContainer.innerHTML = `
                ${currentImage ? `<img src="${currentImage}" alt="Config Image" style="max-width: 300px; max-height: 300px; margin-bottom: 10px;" id="config_image_preview">` : ''}
                <input type="file" class="form-control shadow-sm" id="config_image" name="config_image" accept="image/*">
            `;
            const configImage = document.getElementById('config_image');
            configImage.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('config_image_preview');
                        if (preview) {
                            preview.src = e.target.result;
                        } else {
                            const img = document.createElement('img');
                            img.id = 'config_image_preview';
                            img.src = e.target.result;
                            img.style.maxWidth = '300px';
                            img.style.maxHeight = '300px';
                            img.style.marginBottom = '10px';
                            configValueContainer.insertBefore(img, configImage);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            configValueContainer.innerHTML = `
                <textarea class="form-control shadow-sm" id="config_value" name="config_value" rows="3" required>${document.getElementById('config_value') ? document.getElementById('config_value').value : '{{ old('config_value', $config->config_value) }}'}</textarea>
            `;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
        toggleConfigValue();
    });
</script>

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
@endsection