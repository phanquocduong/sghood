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
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Sửa cấu hình') }}</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('configs.update', $config->id) }}" method="POST" id="configEditForm"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="config_key" class="form-label fw-bold text-primary">Khóa<span style="color:red;">*</span></label>
                            <input type="text" class="form-control shadow-sm {{ $errors->has('config_key') ? 'is-invalid' : '' }}" 
                                id="config_key" name="config_key" value="{{ old('config_key', $config->config_key) }}" required>
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
                                <option value="TEXT" {{ old('config_type', $config->config_type) == 'TEXT' ? 'selected' : '' }}>TEXT</option>
                                <option value="URL" {{ old('config_type', $config->config_type) == 'URL' ? 'selected' : '' }}>URL</option>
                                <option value="HTML" {{ old('config_type', $config->config_type) == 'HTML' ? 'selected' : '' }}>HTML</option>
                                <option value="JSON" {{ old('config_type', $config->config_type) == 'JSON' ? 'selected' : '' }}>OPTION</option>
                                <option value="OBJECT" {{ old('config_type', $config->config_type) == 'OBJECT' ? 'selected' : '' }}>OBJECT</option>
                                <option value="BANK" {{ old('config_type', $config->config_type) == 'BANK' ? 'selected' : '' }}>BANK</option>
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

                            <!-- Text/HTML/URL Input -->
                            <textarea class="form-control shadow-sm {{ $errors->has('config_value') ? 'is-invalid' : '' }}"
                                id="config_value" name="config_value" rows="3">{{ old('config_value', in_array($config->config_type, ['JSON', 'BANK', 'OBJECT']) ? '' : $config->config_value) }}</textarea>

                            <!-- Image Input -->
                            <input type="file" class="form-control shadow-sm {{ $errors->has('config_image') ? 'is-invalid' : '' }}"
                                id="config_image" name="config_image" accept="image/jpeg,image/png,image/gif" style="display: none;">

                            <!-- OBJECT Options Container -->
                            <div id="object_options_container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="fas fa-cube"></i> Cấu hình đối tượng JSON
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addObjectGroup()">
                                        <i class="fas fa-plus"></i> Thêm nhóm đối tượng
                                    </button>
                                </div>
                                
                                <div id="object_groups_container">
                                    {{-- Hiển thị dữ liệu OBJECT hiện có --}}
                                    @if ($config->config_type == 'OBJECT' && $config->config_value)
                                        @php
                                            $objectData = json_decode($config->config_value, true);
                                            if (!is_array($objectData)) $objectData = [];
                                        @endphp
                                        @foreach ($objectData as $groupIndex => $group)
                                            <div class="object-group-item mb-4" id="object_group_{{ $groupIndex }}">
                                                <div class="card border-primary">
                                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0 text-primary">
                                                            <i class="fas fa-layer-group"></i> Nhóm đối tượng #{{ $groupIndex + 1 }}
                                                        </h6>
                                                        <div>
                                                            <button type="button" class="btn btn-sm btn-outline-success me-2" 
                                                                onclick="addObjectKeyValue('object_group_{{ $groupIndex }}')">
                                                                <i class="fas fa-plus"></i> Thêm thuộc tính
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="removeObjectGroup('object_group_{{ $groupIndex }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="object-keys-container" id="object_group_{{ $groupIndex }}_keys">
                                                            @foreach ($group as $key => $value)
                                                                @php
                                                                    $keyIndex = $loop->index;
                                                                    $values = is_array($value) ? $value : [$value];
                                                                @endphp
                                                                <div class="object-key-value-pair mb-3" id="key_{{ $groupIndex }}_{{ $keyIndex }}">
                                                                    <div class="row g-2 align-items-start">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label text-muted small fw-semibold">Tên thuộc tính (key)</label>
                                                                            <input type="text" class="form-control form-control-sm" 
                                                                                name="object_data[object_group_{{ $groupIndex }}][key_{{ $groupIndex }}_{{ $keyIndex }}][key]" 
                                                                                value="{{ $key }}" required>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label text-muted small fw-semibold">Giá trị</label>
                                                                            <div class="value-container" id="key_{{ $groupIndex }}_{{ $keyIndex }}_values">
                                                                                @foreach ($values as $valueIndex => $val)
                                                                                    <div class="value-item d-flex align-items-center {{ $valueIndex > 0 ? 'mt-2' : '' }}">
                                                                                        <input type="text" class="form-control form-control-sm me-2" 
                                                                                            name="object_data[object_group_{{ $groupIndex }}][key_{{ $groupIndex }}_{{ $keyIndex }}][values][]" 
                                                                                            value="{{ $val }}" required>
                                                                                        @if ($valueIndex == 0)
                                                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                                                onclick="addValueToKey('key_{{ $groupIndex }}_{{ $keyIndex }}', 'object_group_{{ $groupIndex }}')" title="Thêm giá trị khác">
                                                                                                <i class="fas fa-plus"></i>
                                                                                            </button>
                                                                                        @else
                                                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                                                onclick="removeValueFromKey(this)" title="Xóa giá trị này">
                                                                                                <i class="fas fa-minus"></i>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-2 d-flex align-items-end">
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                                onclick="removeObjectKeyValue('key_{{ $groupIndex }}_{{ $keyIndex }}')" title="Xóa thuộc tính">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- BANK Options Container -->
                            <div id="bank_options_container" style="display: none;">
                                {{-- Giữ nguyên code BANK hiện có --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="fas fa-university"></i> Danh sách ngân hàng
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addBankOption()">
                                        <i class="fas fa-plus"></i> Thêm ngân hàng
                                    </button>
                                </div>
                                <div id="bank_options_list">
                                    {{-- Code BANK hiện có... --}}
                                </div>
                            </div>

                            <!-- JSON Options Container -->
                            <div id="json_options_container" style="display: none;">
                                {{-- Giữ nguyên code JSON hiện có --}}
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">
                                        <i class="fas fa-list"></i> Danh sách lựa chọn
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addJsonOption()">
                                        <i class="fas fa-plus"></i> Thêm lựa chọn
                                    </button>
                                </div>
                                <div id="json_options_list">
                                    {{-- Code JSON hiện có... --}}
                                </div>
                            </div>

                            <!-- Image preview -->
                            <div id="image_preview_container" style="display: none; margin-top: 10px;">
                                @if ($config->config_type == 'IMAGE' && $config->config_value)
                                    <img id="image_preview" src="{{ asset($config->config_value) }}"
                                        style="max-width: 200px; max-height: 200px; object-fit: contain;" alt="Xem trước ảnh">
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
                        <a href="{{ route('configs.index') }}" class="btn btn-secondary shadow-sm">Hủy</a>
                        <button type="submit" class="btn btn-primary shadow-sm">Cập nhật cấu hình</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Giữ nguyên CSS hiện có --}}
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
        .object-group-item,
        .bank-option-item,
        .json-option-item {
            transition: all 0.3s ease;
        }
        .object-group-item:hover,
        .bank-option-item:hover {
            transform: translateY(-2px);
        }
        .object-key-value-pair {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .value-item {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 5px;
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    </style>

    <script>
        let bankOptionIndex = {{ in_array($config->config_type, ['BANK', 'JSON']) && $config->config_value ? count(json_decode($config->config_value, true) ?? []) : 0 }};
        let objectGroupIndex = {{ $config->config_type == 'OBJECT' && $config->config_value ? count(json_decode($config->config_value, true) ?? []) : 0 }};
        let objectKeyIndex = 0;
        function toggleConfigValue() {
            const configType = document.getElementById('config_type').value;
            const configValue = document.getElementById('config_value');
            const configImage = document.getElementById('config_image');
            const bankOptionsContainer = document.getElementById('bank_options_container');
            const jsonOptionsContainer = document.getElementById('json_options_container');
            const objectOptionsContainer = document.getElementById('object_options_container');
            const imagePreviewContainer = document.getElementById('image_preview_container');

            // Hide all containers first
            configValue.style.display = 'none';
            configImage.style.display = 'none';
            bankOptionsContainer.style.display = 'none';
            jsonOptionsContainer.style.display = 'none';
            objectOptionsContainer.style.display = 'none';
            imagePreviewContainer.style.display = 'none';

            // Remove all required attributes
            configValue.removeAttribute('required');
            configImage.removeAttribute('required');

            if (configType === 'IMAGE') {
                configImage.style.display = 'block';
                imagePreviewContainer.style.display = 'block';
            } else if (configType === 'BANK') {
                bankOptionsContainer.style.display = 'block';
                convertJsonToBank();
            } else if (configType === 'JSON') {
                jsonOptionsContainer.style.display = 'block';
                convertBankToJson();
            } else if (configType === 'OBJECT') {
                objectOptionsContainer.style.display = 'block';
            } else {
                configValue.style.display = 'block';
                configValue.setAttribute('required', 'required');
            }
        }

        // Add Object Group
        function addObjectGroup() {
            const container = document.getElementById('object_groups_container');
            const groupId = `object_group_${objectGroupIndex}`;

            const newGroupHTML = `
                <div class="object-group-item mb-4" id="${groupId}">
                    <div class="card border-primary">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-layer-group"></i> Nhóm đối tượng #${objectGroupIndex + 1}
                            </h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success me-2" 
                                    onclick="addObjectKeyValue('${groupId}')">
                                    <i class="fas fa-plus"></i> Thêm thuộc tính
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeObjectGroup('${groupId}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="object-keys-container" id="${groupId}_keys">
                                <!-- Key-value pairs sẽ được thêm vào đây -->
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newGroupHTML);
            objectGroupIndex++;
        }

        // Add Key-Value Pair
        function addObjectKeyValue(groupId) {
            const container = document.getElementById(`${groupId}_keys`);
            const keyId = `key_${objectKeyIndex}`;

            const newKeyValueHTML = `
                <div class="object-key-value-pair mb-3" id="${keyId}">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Tên thuộc tính (key)</label>
                            <input type="text" class="form-control form-control-sm" 
                                name="object_data[${groupId}][${keyId}][key]" 
                                placeholder="VD: name, age, settings..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Giá trị</label>
                            <div class="value-container" id="${keyId}_values">
                                <div class="value-item d-flex align-items-center">
                                    <input type="text" class="form-control form-control-sm me-2" 
                                        name="object_data[${groupId}][${keyId}][values][]" 
                                        placeholder="Nhập giá trị..." required>
                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="addValueToKey('${keyId}', '${groupId}')" title="Thêm giá trị khác">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeObjectKeyValue('${keyId}')" title="Xóa thuộc tính">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newKeyValueHTML);
            objectKeyIndex++;
        }

        // Add Value to existing Key
        function addValueToKey(keyId, groupId) {
            const container = document.getElementById(`${keyId}_values`);

            const newValueHTML = `
                <div class="value-item d-flex align-items-center mt-2">
                    <input type="text" class="form-control form-control-sm me-2" 
                        name="object_data[${groupId}][${keyId}][values][]" 
                        placeholder="Nhập giá trị khác..." required>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="removeValueFromKey(this)" title="Xóa giá trị này">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newValueHTML);
        }

        // Remove functions
        function removeObjectGroup(groupId) {
            const group = document.getElementById(groupId);
            group.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                group.remove();
            }, 300);
        }

        function removeObjectKeyValue(keyId) {
            const keyValue = document.getElementById(keyId);
            keyValue.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                keyValue.remove();
            }, 300);
        }

        function removeValueFromKey(button) {
            const valueItem = button.closest('.value-item');
            valueItem.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                valueItem.remove();
            }, 300);
        }

        function convertBankToJson() {
            const bankInputs = document.querySelectorAll('input[name^="config_json["][name$="][value]"]');
            const jsonContainer = document.getElementById('json_options_list');

            // Chỉ convert nếu có dữ liệu BANK và container JSON đang trống
            if (bankInputs.length > 0 && jsonContainer.children.length === 0) {
                // Chuyển đổi từ BANK sang JSON
                bankInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value) {
                        addJsonOptionWithValue(value);
                    }
                });
            }

            // Thêm ít nhất một option trống nếu không có data
            if (jsonContainer.children.length === 0) {
                addJsonOption();
            }
        }

        function convertJsonToBank() {
            const jsonInputs = document.querySelectorAll('#json_options_list input[name="config_json[]"]');
            const bankContainer = document.getElementById('bank_options_list');

            // Chỉ convert nếu có dữ liệu JSON và container BANK đang trống
            if (jsonInputs.length > 0 && bankContainer.children.length === 0) {
                jsonInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value) {
                        addBankOptionWithValues(value, value); // value làm cả code và name
                    }
                });
            }

            // Thêm ít nhất một bank trống nếu không có data
            if (bankContainer.children.length === 0) {
                addBankOption();
            }
        }

        function addJsonOptionWithValue(value = '') {
            const container = document.getElementById('json_options_list');
            const newOptionHTML = `
                <div class="json-option-item mb-2">
                    <div class="input-group">
                        <input type="text" class="form-control" name="config_json[]"
                            value="${escapeHtml(value)}" placeholder="Nhập lựa chọn" required>
                        <button type="button" class="btn btn-outline-danger" onclick="removeJsonOption(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newOptionHTML);
        }

        function addBankOptionWithValues(value = '', label = '', logo = '') {
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
                                                value="${escapeHtml(value)}"
                                                placeholder="VD: ACB" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label text-muted small fw-semibold">Tên ngân hàng</label>
                                            <input type="text" class="form-control form-control-sm" 
                                                name="config_json[${bankOptionIndex}][label]" 
                                                value="${escapeHtml(label)}"
                                                placeholder="VD: ACB - Ngân hàng TMCP Á Châu" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-semibold">Logo URL</label>
                                            <div class="input-group input-group-sm">
                                                <input type="url" class="form-control" 
                                                    name="config_json[${bankOptionIndex}][logo]" 
                                                    value="${escapeHtml(logo)}"
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

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Add Bank Option (Đẹp)
        function addBankOption() {
            addBankOptionWithValues('', '', '');
        }

        // Add JSON Option (Đơn giản)
        function addJsonOption() {
            addJsonOptionWithValue('');
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
                img.onerror = function () {
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
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            toggleConfigValue();
        });
    </script>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    @endsection
@endsection