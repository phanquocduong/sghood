@extends('layouts.app')

@section('title', 'Thêm cấu hình')

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
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Thêm cấu hình') }}</h6>
            </div>
            <div class="card-body p-4">

                <form action="{{ route('configs.store') }}" method="POST" id="configCreateForm"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="config_key" class="form-label fw-bold text-primary">Khóa<span
                                    style="color:red;">*</span></label>
                            <input type="text"
                                class="form-control shadow-sm {{ $errors->has('config_key') ? 'is-invalid' : '' }}"
                                id="config_key" name="config_key" value="{{ old('config_key') }}" required>
                            @if ($errors->has('config_key'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_key') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label for="config_type" class="form-label fw-bold text-primary">Loại<span
                                    style="color:red;">*</span></label>
                            <select class="form-select shadow-sm {{ $errors->has('config_type') ? 'is-invalid' : '' }}"
                                id="config_type" name="config_type" required onchange="toggleConfigValue()">
                                <option value="TEXT" {{ old('config_type') == 'TEXT' ? 'selected' : '' }}>TEXT</option>
                                <option value="URL" {{ old('config_type') == 'URL' ? 'selected' : '' }}>URL</option>
                                <option value="HTML" {{ old('config_type') == 'HTML' ? 'selected' : '' }}>HTML</option>
                                <option value="JSON" {{ old('config_type') == 'JSON' ? 'selected' : '' }}>JSON</option>
                                <option value="IMAGE" {{ old('config_type') == 'IMAGE' ? 'selected' : '' }}>IMAGE</option>
                            </select>
                            @if ($errors->has('config_type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_type') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label for="config_value" class="form-label fw-bold text-primary">Nội dung<span
                                    style="color:red;">*</span></label>
                            <textarea class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}"
                                id="config_value" name="config_value" rows="3" required>{{ old('config_value') }}</textarea>
                            <input type="file"
                                class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}"
                                id="config_image" name="config_image" accept="image/*" style="display: none;">
                            @if ($errors->has('config_value'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_value') }}
                                </div>
                            @endif
                        </div>

                        <script>
                            function toggleConfigValue() {
                                const configType = document.getElementById('config_type').value;
                                const configValue = document.getElementById('config_value');
                                const configImage = document.getElementById('config_image');

                                if (configType === 'IMAGE') {
                                    configValue.style.display = 'none';
                                    configImage.style.display = 'block';
                                    configValue.removeAttribute('required');
                                    configImage.setAttribute('required', 'required');
                                } else {
                                    configValue.style.display = 'block';
                                    configImage.style.display = 'none';
                                    configImage.removeAttribute('required');
                                    configValue.setAttribute('required', 'required');
                                }
                            }

                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function () {
                                toggleConfigValue();
                            });

                            document.getElementById('configCreateForm').addEventListener('submit', function (e) {
                                const configType = document.getElementById('config_type').value;
                                const configImage = document.getElementById('config_image').files;

                                if (configType === 'IMAGE' && configImage.length === 0) {
                                    e.preventDefault();
                                    alert('Vui lòng chọn một file ảnh!');
                                }
                            });
                        </script>
                        <div class="col-12">
                            <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                            <input type="text" class="form-control shadow-sm" id="description" name="description"
                                value="{{ old('description') }}">
                        </div>

                    </div>
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('configs.index') }}" class="btn btn-secondary shadow-sm"
                            style="transition: all 0.3s;">Hủy</a>
                        <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm cấu
                            hình</button>
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

        .alert-success,
        .alert-danger {
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }
    </style>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @endsection
@endsection