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
                                <option value="JSON" {{ old('config_type', $config->config_type) == 'JSON' ? 'selected' : '' }}>OPTION</option>
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
                            <textarea class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}"
                                id="config_value" name="config_value" rows="3">{{ old('config_value', $config->config_type != 'JSON' ? $config->config_value : '') }}</textarea>
                            <!-- input hình ảnh -->
                            <input type="file"
                                class="form-control shadow-sm {{ $errors->has('config_image') ? 'is-invalid' : '' }}"
                                id="config_image" name="config_image" accept="image/jpeg,image/png,image/gif"
                                style="display: none;">
                            <!-- input JSON -->
                            <input type="button"
                                class="btn btn-secondary shadow-sm {{ $errors->has('config_json') ? 'is-invalid' : '' }}"
                                id="config_json" name="config_json" value="+ Thêm lựa chọn"
                                style="display: none;" onclick="addOption()">
                            @if ($errors->has('config_image'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_image') }}
                                </div>
                            @endif
                            @if ($errors->has('config_json'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_json') }}
                                </div>
                            @endif
                            <!-- JSON options container -->
                            <div id="json_options_container" style="display: none;">
                                @if (old('config_json'))
                                    @foreach (old('config_json', []) as $jsonValue)
                                        <input type="text" class="form-control shadow-sm mt-2" name="config_json[]"
                                            value="{{ is_array($jsonValue) ? json_encode($jsonValue) : e($jsonValue) }}" placeholder="Nhập lựa chọn" required>
                                    @endforeach
                                @elseif ($config->config_type == 'JSON' && $config->config_value)
                                    @php
                                        $jsonData = json_decode($config->config_value, true);
                                        if (!is_array($jsonData)) {
                                            $jsonData = [];
                                        }
                                    @endphp
                                    @foreach ($jsonData as $jsonValue)
                                        <input type="text" class="form-control shadow-sm mt-2" name="config_json[]"
                                            value="{{ is_array($jsonValue) ? json_encode($jsonValue) : e($jsonValue) }}" placeholder="Nhập lựa chọn" required>
                                    @endforeach
                                @endif
                            </div>
                            <!-- xem trước ảnh -->
                            <div id="image_preview_container" style="display: none; margin-top: 10px;">
                                @if ($config->config_type == 'IMAGE' && $config->config_value)
                                    <img id="image_preview" src="{{ asset($config->config_value) }}" style="max-width: 200px; max-height: 200px; object-fit: contain;" alt="Xem trước ảnh">
                                @else
                                    <img id="image_preview" style="max-width: 200px; max-height: 200px; object-fit: contain;" alt="Xem trước ảnh">
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                            <input type="text" class="form-control shadow-sm" id="description" name="description"
                                value="{{ old('description', $config->description) }}">
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

        .alert-success,
        .alert-danger {
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }

        #image_preview_container {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
            background: #f9f9f9;
        }
    </style>

    <script>
        function toggleConfigValue() {
            const configType = document.getElementById('config_type').value;
            const configValue = document.getElementById('config_value');
            const configImage = document.getElementById('config_image');
            const configJson = document.getElementById('config_json');
            const jsonOptionsContainer = document.getElementById('json_options_container');
            const imagePreviewContainer = document.getElementById('image_preview_container');
            const imagePreview = document.getElementById('image_preview');

            if (configType === 'IMAGE') {
                configValue.style.display = 'none';
                configImage.style.display = 'block';
                configJson.style.display = 'none';
                jsonOptionsContainer.style.display = 'none';
                imagePreviewContainer.style.display = 'block';
                configValue.removeAttribute('required');
                configImage.removeAttribute('required'); // Optional for edit
            } else if (configType === 'JSON') {
                configValue.style.display = 'none';
                configImage.style.display = 'none';
                configJson.style.display = 'block';
                jsonOptionsContainer.style.display = 'block';
                imagePreviewContainer.style.display = 'none';
                configImage.removeAttribute('required');
                configValue.removeAttribute('required');
            } else {
                configValue.style.display = 'block';
                configImage.style.display = 'none';
                configJson.style.display = 'none';
                jsonOptionsContainer.style.display = 'none';
                imagePreviewContainer.style.display = 'none';
                configImage.removeAttribute('required');
                configValue.setAttribute('required', 'required');
                imagePreview.src = ''; // Clear preview if not IMAGE
                configImage.value = ''; // Clear file input
            }
        }

        // Image preview functionality
        document.getElementById('config_image').addEventListener('change', function (e) {
            const imagePreview = document.getElementById('image_preview');
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '{{ $config->config_type == "IMAGE" && $config->config_value ? asset($config->config_value) : "" }}';
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            toggleConfigValue();
        });

        // Form submission validation
        document.getElementById('configEditForm').addEventListener('submit', function (e) {
            const configType = document.getElementById('config_type').value;
            const configImage = document.getElementById('config_image').files;
            const jsonInputs = document.getElementsByName('config_json[]');

            if (configType === 'IMAGE' && configImage.length === 0 && !'{{ $config->config_type == "IMAGE" && $config->config_value }}') {
                e.preventDefault();
                alert('Vui lòng chọn một file ảnh!');
            } else if (configType === 'JSON' && jsonInputs.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một lựa chọn JSON!');
            } else if (configType === 'JSON') {
                let hasEmpty = false;
                for (let input of jsonInputs) {
                    if (!input.value.trim()) {
                        hasEmpty = true;
                        break;
                    }
                }
                if (hasEmpty) {
                    e.preventDefault();
                    alert('Vui lòng nhập giá trị cho tất cả các lựa chọn JSON!');
                }
            }
        });

        // Function to add input for JSON options
        function addOption() {
            const container = document.getElementById('json_options_container');
            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.className = 'form-control shadow-sm mt-2';
            newInput.name = 'config_json[]';
            newInput.placeholder = 'Nhập lựa chọn';
            newInput.required = true;
            container.appendChild(newInput);
        }
    </script>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @endsection
@endsection