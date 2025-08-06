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
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Thêm cấu hình') }}</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('configs.store') }}" method="POST" id="configCreateForm" enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="config_key" class="form-label fw-bold text-primary">Khóa<span style="color:red;">*</span></label>
                            <input type="text" class="form-control shadow-sm {{ $errors->has('config_key') ? 'is-invalid' : '' }}" 
                                id="config_key" name="config_key" value="{{ old('config_key') }}" required>
                            @if ($errors->has('config_key'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_key') }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-12">
                            <label for="config_type" class="form-label fw-bold text-primary">Loại<span style="color:red;">*</span></label>
                            <select class="form-select shadow-sm {{ $errors->has('config_type') ? 'is-invalid' : '' }}" 
                                id="config_type" name="config_type" required onchange="toggleConfigValue()">
                                <option value="TEXT" {{ old('config_type') == 'TEXT' ? 'selected' : '' }}>TEXT</option>
                                <option value="URL" {{ old('config_type') == 'URL' ? 'selected' : '' }}>URL</option>
                                <option value="HTML" {{ old('config_type') == 'HTML' ? 'selected' : '' }}>HTML</option>
                                <option value="JSON" {{ old('config_type') == 'JSON' ? 'selected' : '' }}>OPTION</option>
                                <option value="BANK" {{ old('config_type') == 'BANK' ? 'selected' : '' }}>BANK</option>
                                <option value="IMAGE" {{ old('config_type') == 'IMAGE' ? 'selected' : '' }}>IMAGE</option>
                            </select>
                            @if ($errors->has('config_type'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('config_type') }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-12">
                            <label for="config_value" class="form-label fw-bold text-primary">Nội dung<span style="color:red;">*</span></label>
                            
                            <!-- Text/HTML/URL Input -->
                            <textarea class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}"
                                id="config_value" name="config_value" rows="3">{{ old('config_value') }}</textarea>
                            
                            <!-- Image Input -->
                            <input type="file"
                                class="form-control shadow-sm {{ $errors->has('config_image') ? 'is-invalid' : '' }}"
                                id="config_image" name="config_image" accept="image/jpeg,image/png,image/gif"
                                style="display: none;">
                            
                            <!-- BANK Options Container (Đẹp) -->
                            <div id="bank_options_container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="fas fa-university"></i> Danh sách ngân hàng
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addBankOption()">
                                        <i class="fas fa-plus"></i> Thêm ngân hàng
                                    </button>
                                </div>
                                
                                <div id="bank_options_list">
                                    @if (old('config_json'))
                                        @foreach (old('config_json', []) as $index => $jsonValue)
                                            <div class="bank-option-item mb-3">
                                                <div class="card border-light shadow-sm">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <div class="row g-2">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label text-muted small fw-semibold">Mã ngân hàng</label>
                                                                        <input type="text" class="form-control form-control-sm" 
                                                                            name="config_json[{{ $index }}][value]" 
                                                                            value="{{ is_array($jsonValue) ? ($jsonValue['value'] ?? '') : '' }}" 
                                                                            placeholder="VD: ACB" required>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <label class="form-label text-muted small fw-semibold">Tên ngân hàng</label>
                                                                        <input type="text" class="form-control form-control-sm" 
                                                                            name="config_json[{{ $index }}][label]" 
                                                                            value="{{ is_array($jsonValue) ? ($jsonValue['label'] ?? '') : '' }}" 
                                                                            placeholder="VD: ACB - Ngân hàng TMCP Á Châu" required>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label class="form-label text-muted small fw-semibold">Logo URL</label>
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="url" class="form-control" 
                                                                                name="config_json[{{ $index }}][logo]" 
                                                                                value="{{ is_array($jsonValue) ? ($jsonValue['logo'] ?? '') : '' }}" 
                                                                                placeholder="https://..." 
                                                                                onchange="previewLogo(this)">
                                                                            <span class="input-group-text p-1 logo-preview" style="display: none;">
                                                                                <img src="" alt="Logo" style="width: 24px; height: 24px; object-fit: contain;" class="rounded border">
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-outline-danger btn-sm ms-2" 
                                                                onclick="removeBankOption(this)" title="Xóa ngân hàng">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- JSON Options Container (Đơn giản) -->
                            <div id="json_options_container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="fas fa-list"></i> Danh sách lựa chọn
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addJsonOption()">
                                        <i class="fas fa-plus"></i> Thêm lựa chọn
                                    </button>
                                </div>
                                
                                <div id="json_options_list">
                                    @if (old('config_json'))
                                        @foreach (old('config_json', []) as $jsonValue)
                                            <div class="json-option-item mb-2">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="config_json[]"
                                                        value="{{ is_array($jsonValue) ? json_encode($jsonValue) : e($jsonValue) }}" 
                                                        placeholder="Nhập lựa chọn" required>
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeJsonOption(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Image preview -->
                            <div id="image_preview_container" style="display: none; margin-top: 10px;">
                                <img id="image_preview" style="max-width: 200px; max-height: 200px; object-fit: contain;" alt="Xem trước ảnh">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                            <input type="text" class="form-control shadow-sm" id="description" name="description"
                                value="{{ old('description') }}">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('configs.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                        <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm cấu hình</button>
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

        .bank-option-item {
            transition: all 0.3s ease;
        }
        
        .bank-option-item:hover {
            transform: translateY(-2px);
        }

        .json-option-item {
            transition: all 0.3s ease;
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

        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    </style>

    <script>
        let bankOptionIndex = 0;

        function toggleConfigValue() {
            const configType = document.getElementById('config_type').value;
            const configValue = document.getElementById('config_value');
            const configImage = document.getElementById('config_image');
            const bankOptionsContainer = document.getElementById('bank_options_container');
            const jsonOptionsContainer = document.getElementById('json_options_container');
            const imagePreviewContainer = document.getElementById('image_preview_container');

            // Hide all containers first
            configValue.style.display = 'none';
            configImage.style.display = 'none';
            bankOptionsContainer.style.display = 'none';
            jsonOptionsContainer.style.display = 'none';
            imagePreviewContainer.style.display = 'none';

            // Remove all required attributes
            configValue.removeAttribute('required');
            configImage.removeAttribute('required');

            if (configType === 'IMAGE') {
                configImage.style.display = 'block';
                imagePreviewContainer.style.display = 'block';
            } else if (configType === 'BANK') {
                bankOptionsContainer.style.display = 'block';
            } else if (configType === 'JSON') {
                jsonOptionsContainer.style.display = 'block';
            } else {
                configValue.style.display = 'block';
                configValue.setAttribute('required', 'required');
            }
        }

        // Add Bank Option (Đẹp)
        function addBankOption() {
            const container = document.getElementById('bank_options_list');
            const newOptionHTML = `
                <div class="bank-option-item mb-3">
                    <div class="card border-light shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small fw-semibold">Mã ngân hàng</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                name="config_json[${bankOptionIndex}][value]" 
                                                placeholder="VD: ACB" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label text-muted small fw-semibold">Tên ngân hàng</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                name="config_json[${bankOptionIndex}][label]" 
                                                placeholder="VD: ACB - Ngân hàng TMCP Á Châu" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-semibold">Logo URL</label>
                                            <div class="input-group input-group-sm">
                                                <input type="url" class="form-control" 
                                                    name="config_json[${bankOptionIndex}][logo]" 
                                                    placeholder="https://..." 
                                                    onchange="previewLogo(this)">
                                                <span class="input-group-text p-1 logo-preview" style="display: none;">
                                                    <img src="" alt="Logo" style="width: 24px; height: 24px; object-fit: contain;" class="rounded border">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm ms-2" 
                                    onclick="removeBankOption(this)" title="Xóa ngân hàng">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', newOptionHTML);
            bankOptionIndex++;
        }

        // Add JSON Option (Đơn giản)
        function addJsonOption() {
            const container = document.getElementById('json_options_list');
            const newOptionHTML = `
                <div class="json-option-item mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="config_json[]"
                            placeholder="Nhập lựa chọn" required>
                        <button type="button" class="btn btn-outline-danger" onclick="removeJsonOption(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', newOptionHTML);
        }

        function removeBankOption(button) {
            const optionItem = button.closest('.bank-option-item');
            optionItem.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                optionItem.remove();
            }, 300);
        }

        function removeJsonOption(button) {
            const optionItem = button.closest('.json-option-item');
            optionItem.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                optionItem.remove();
            }, 300);
        }

        function previewLogo(input) {
            const logoPreview = input.parentElement.querySelector('.logo-preview');
            const img = logoPreview?.querySelector('img');
            
            if (input.value && logoPreview && img) {
                img.src = input.value;
                logoPreview.style.display = 'block';
                img.onerror = function() {
                    logoPreview.style.display = 'none';
                };
            } else if (logoPreview) {
                logoPreview.style.display = 'none';
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
                imagePreview.src = '';
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            toggleConfigValue();
        });

        // Form submission validation
        document.getElementById('configCreateForm').addEventListener('submit', function (e) {
            const configType = document.getElementById('config_type').value;
            const configImage = document.getElementById('config_image').files;
            const jsonInputs = document.getElementsByName('config_json[]');
            const bankInputs = document.querySelectorAll('input[name^="config_json["][name$="][value]"]');

            if (configType === 'IMAGE' && configImage.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn một file ảnh!');
            } else if (configType === 'JSON' && jsonInputs.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một lựa chọn!');
            } else if (configType === 'BANK' && bankInputs.length === 0) {
                e.preventDefault();
                alert('Vui lòng thêm ít nhất một ngân hàng!');
            }
        });
    </script>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    @endsection
@endsection