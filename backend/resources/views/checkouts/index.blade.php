@extends('layouts.app')

@section('title', 'Quản lý Checkout')

@section('content')
<div class="container-fluid py-5 px-4">
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

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4" style="background: linear-gradient(90deg, #007bff, #00c6ff);">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-check-circle me-2"></i>Quản lý Checkout
            </h5>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden" style="text-align: center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 10%;" class="text-center">STT</th>
                            <th scope="col" style="width: 15%;">Tên phòng</th>
                            <th scope="col" style="width: 20%;" class="text-center">Ngày checkout</th>
                            <th scope="col" style="width: 15%;" class="text-center">Rời đi</th>
                            <th scope="col" style="width: 20%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 20%;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checkouts as $checkout)
                            <tr class="table-row">
                                <td class="text-center">{{ $checkouts->firstItem() + $loop->index }}</td>
                                <td>{{ $checkout->contract->room->name }}</td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $checkout->has_left == 0 ? 'warning' : 'success' }} py-2 px-3">
                                        <i class="{{ $checkout->has_left == 0 ? 'fas fa-clock' : 'fas fa-check-circle' }} me-1" style="font-size: 8px;"></i>
                                        {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $checkout->status == 'Chờ kiểm kê' ? 'warning' : ($checkout->status == 'Đã kiểm kê' ? 'success' : 'dark') }} py-2 px-3">
                                        <i class="{{ $checkout->status == 'Chờ kiểm kê' ? 'fas fa-clock' : ($checkout->status == 'Đã kiểm kê' ? 'fas fa-check-circle' : 'fas fa-times-circle') }} me-1" style="font-size: 8px;"></i>
                                        {{ $checkout->status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $checkout->id }}">
                                        <i class="fas fa-eye me-1"></i>Xem
                                    </button>
                                    @if($checkout->status !== 'Đã kiểm kê')
                                        <button type="button" class="btn btn-warning btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $checkout->id }}">
                                            <i class="fas fa-edit me-1"></i>Sửa
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm shadow-sm" disabled>
                                            <i class="fas fa-lock me-1"></i>Đã hoàn thành
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal for View -->
                            <div class="modal fade" id="checkoutModal{{ $checkout->id }}" tabindex="-1" aria-labelledby="checkoutModalLabel{{ $checkout->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="checkoutModalLabel{{ $checkout->id }}">Chi tiết Checkout</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Tên phòng:</strong> {{ $checkout->contract->room->name }}</p>
                                            <p><strong>Ngày checkout:</strong> {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}</p>
                                            <p><strong>Rời đi:</strong> {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}</p>
                                            <p><strong>Trạng thái:</strong> {{ $checkout->status ?? 'N/A' }}</p>
                                            <p><strong>Số tiền khấu trừ:</strong> {{ $checkout->deduction_amount ?? 'N/A' }} VNĐ</p>
                                            <p><strong>Kiểm kê:</strong>
                                                @if ($checkout->inventory_details && is_array($checkout->inventory_details))
                                                    <ul>
                                                        @foreach ($checkout->inventory_details as $item)
                                                            <li>{{ $item['type'] ?? 'N/A' }}:
                                                                @if(isset($item['value']) && $item['value'] !== '')
                                                                    @if($item['type'] == 'TEXT')
                                                                        {{ $item['value'] }}
                                                                    @else
                                                                        <img src="{{ asset('storage/' . ltrim($item['value'], '/storage/')) }}" style="max-width: 100px; max-height: 100px; object-fit: contain;" alt="Inventory Image">
                                                                    @endif
                                                                @else
                                                                    Chưa tải lên
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal for Edit -->
                            <div class="modal fade" id="editModal{{ $checkout->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $checkout->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-warning text-white">
                                            <h5 class="modal-title" id="editModalLabel{{ $checkout->id }}">Sửa Checkout</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('checkouts.update', $checkout->id) }}" method="POST" enctype="multipart/form-data" id="checkoutForm{{ $checkout->id }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label for="check_out_date{{ $checkout->id }}" class="form-label">Ngày checkout <span style="color: red;">*</span></label>
                                                    <input type="date" class="form-control" id="check_out_date{{ $checkout->id }}" name="check_out_date" value="{{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('Y-m-d') : '' }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="has_left{{ $checkout->id }}" class="form-label">Rời đi <span style="color: red;">*</span></label>
                                                    <select class="form-select" id="has_left{{ $checkout->id }}" name="has_left" required>
                                                        <option value="0" {{ $checkout->has_left == 0 ? 'selected' : '' }}>Chưa rời đi</option>
                                                        <option value="1" {{ $checkout->has_left == 1 ? 'selected' : '' }}>Đã rời đi</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="status{{ $checkout->id }}" class="form-label">Trạng thái <span style="color: red;">*</span></label>
                                                    <select class="form-select" id="status{{ $checkout->id }}" name="status" required>
                                                        <option value="Chờ kiểm kê" {{ $checkout->status == 'Chờ kiểm kê' ? 'selected' : '' }}>Chờ kiểm kê</option>
                                                        <option value="Đã kiểm kê" {{ $checkout->status == 'Đã kiểm kê' ? 'selected' : '' }}>Đã kiểm kê</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="deduction_amount{{ $checkout->id }}" class="form-label">Số tiền khấu trừ (VNĐ)</label>
                                                    <input type="number" class="form-control" id="deduction_amount{{ $checkout->id }}" name="deduction_amount" value="{{ $checkout->deduction_amount ?? '' }}" step="0.01">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kiểm kê</label>
                                                    <button type="button" class="btn btn-secondary mb-2" onclick="addInventoryItem({{ $checkout->id }})">+ Thêm mục kiểm kê</button>
                                                    <div id="inventory_items_container_{{ $checkout->id }}">
                                                        @if ($checkout->inventory_details && is_array($checkout->inventory_details))
                                                            @foreach ($checkout->inventory_details as $index => $item)
                                                                <div class="inventory-item mb-3 p-3 border rounded" data-index="{{ $index }}">
                                                                    <div class="row">
                                                                        <div class="col-4">
                                                                            <label class="form-label">Loại</label>
                                                                            <select class="form-select inventory-type" name="inventory_type[]" required onchange="toggleInventoryValue(this, {{ $checkout->id }})">
                                                                                <option value="TEXT" {{ isset($item['type']) && $item['type'] == 'TEXT' ? 'selected' : '' }}>Text</option>
                                                                                <option value="IMAGE" {{ isset($item['type']) && $item['type'] == 'IMAGE' ? 'selected' : '' }}>Image</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-7">
                                                                            <label class="form-label">Nội dung</label>
                                                                            @if (isset($item['type']) && $item['type'] == 'TEXT')
                                                                                <textarea class="form-control inventory-value-text" name="inventory_value_text[]" rows="2">{{ isset($item['value']) ? $item['value'] : '' }}</textarea>                                                            @elseif (isset($item['type']) && $item['type'] == 'IMAGE')
                                                                <input type="file" class="form-control inventory-value-image" name="inventory_value_image[]" data-index="{{ $index }}" accept="image/*" data-existing-value="{{ isset($item['value']) && $item['value'] !== '' ? $item['value'] : '' }}">
                                                                @if (isset($item['value']) && $item['value'] !== '')
                                                                    <div class="mt-2">
                                                                        <img src="{{ asset('storage/' . ltrim($item['value'], '/storage/')) }}" style="max-width: 100px; max-height: 100px; object-fit: contain;" alt="Preview">
                                                                        <input type="hidden" name="existing_image_value[]" value="{{ $item['value'] }}">
                                                                    </div>
                                                                @else
                                                                    <input type="hidden" name="existing_image_value[]" value="">
                                                                @endif
                                                                            @endif
                                                                        </div>
                                                                        <div class="col-1 d-flex align-items-end">
                                                                            <button type="button" class="btn btn-danger btn-sm remove-item" onclick="removeInventoryItem(this)">Xóa</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                    <input type="hidden" name="inventory_details" id="inventory_details_{{ $checkout->id }}">
                                                </div>
                                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-check-circle fa-3x mb-3 opacity-50"></i>
                                    <br>
                                    <span class="fs-5">Không có checkout nào.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($checkouts->hasPages())
                <div class="mt-4">
                    {{ $checkouts->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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
        .alert-success {
            border-left: 5px solid #28a745;
        }
        .alert-danger {
            border-left: 5px solid #dc3545;
        }
        .inventory-item {
            background: #f8f9fa;
        }
    </style>
@endsection

@section('scripts')
    <script>
        let itemIndex = 0;

        function addInventoryItem(checkoutId) {
            const container = document.getElementById(`inventory_items_container_${checkoutId}`);
            const div = document.createElement('div');
            div.className = 'inventory-item mb-3 p-3 border rounded';
            div.dataset.index = itemIndex;

            div.innerHTML = `
                <div class="row">
                    <div class="col-4">
                        <label class="form-label">Loại</label>
                        <select class="form-select inventory-type" name="inventory_type[]" required onchange="toggleInventoryValue(this, ${checkoutId})">
                            <option value="TEXT">Text</option>
                            <option value="IMAGE">Image</option>
                        </select>
                    </div>
                    <div class="col-7">
                        <label class="form-label">Nội dung</label>
                        <textarea class="form-control inventory-value-text" name="inventory_value_text[]" rows="2" style="display: block;"></textarea>
                        <input type="file" class="form-control inventory-value-image" name="inventory_value_image[]" data-index="${itemIndex}" accept="image/jpeg,image/png,image/gif" style="display: none;" data-existing-value="">
                        <input type="hidden" name="existing_image_value[]" value="" style="display: none;">
                        <div class="image-preview mt-2" style="display: none;">
                            <img style="max-width: 100px; max-height: 100px; object-fit: contain;" alt="Preview">
                        </div>
                    </div>
                    <div class="col-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item" onclick="removeInventoryItem(this)">Xóa</button>
                    </div>
                </div>
            `;

            container.appendChild(div);
            itemIndex++;
            updateInventoryDetails(checkoutId);
        }

        function toggleInventoryValue(select, checkoutId) {
            const container = select.closest('.inventory-item');
            const textArea = container.querySelector('.inventory-value-text');
            const fileInput = container.querySelector('.inventory-value-image');
            const imagePreview = container.querySelector('.image-preview');
            const hiddenInput = container.querySelector('input[name="existing_image_value[]"]');

            textArea.style.display = 'none';
            fileInput.style.display = 'none';
            imagePreview.style.display = 'none';
            if (hiddenInput) hiddenInput.style.display = 'none';

            if (imagePreview && imagePreview.querySelector('img')) {
                imagePreview.querySelector('img').src = '';
            }

            if (select.value === 'TEXT') {
                textArea.style.display = 'block';
            } else if (select.value === 'IMAGE') {
                fileInput.style.display = 'block';
                imagePreview.style.display = 'block';
                if (hiddenInput) hiddenInput.style.display = 'block';

                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.querySelector('img').src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            updateInventoryDetails(checkoutId);
        }

        function removeInventoryItem(button) {
            button.closest('.inventory-item').remove();
            const checkoutId = button.closest('form').id.replace('checkoutForm', '');
            updateInventoryDetails(checkoutId);
        }

        function updateInventoryDetails(checkoutId) {
            const items = document.querySelectorAll(`#inventory_items_container_${checkoutId} .inventory-item`);
            const inventoryDetails = [];
            const fileInputs = document.querySelectorAll(`#inventory_items_container_${checkoutId} .inventory-value-image`);

            items.forEach((item, index) => {
                const typeSelect = item.querySelector('.inventory-type');
                const type = typeSelect.value;
                let value = '';

                if (type === 'TEXT') {
                    value = item.querySelector('.inventory-value-text').value.trim() || '';
                } else if (type === 'IMAGE') {
                    const fileInput = item.querySelector('.inventory-value-image');
                    const existingValueInput = item.querySelector('input[name="existing_image_value[]"]');

                    // If new file is selected, mark as pending upload
                    if (fileInput && fileInput.files[0]) {
                        value = 'pending_upload'; // Backend will handle actual path
                    } else if (existingValueInput && existingValueInput.value) {
                        // Keep existing image path
                        value = existingValueInput.value;
                    } else {
                        // Try fallback to data attribute
                        value = fileInput.dataset.existingValue || '';
                    }
                }

                if (type) {
                    inventoryDetails.push({ type, value: value || '' });
                }
            });

            // Update dataset for file inputs with current index
            fileInputs.forEach((input, idx) => {
                input.dataset.index = idx;
            });

            document.getElementById(`inventory_details_${checkoutId}`).value = JSON.stringify(inventoryDetails);
        }

        // Initialize existing items and event listeners
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function(e) {
                const target = e.relatedTarget.getAttribute('data-bs-target');
                if (!target) return;

                const checkoutId = target.replace('#editModal', '');

                // Set existing values for image inputs
                document.querySelectorAll(`#editModal${checkoutId} .inventory-item`).forEach(item => {
                    const imageInput = item.querySelector('.inventory-value-image');
                    const existingImg = item.querySelector('img[alt="Preview"]');

                    if (imageInput && existingImg && existingImg.src) {
                        // Extract the path from the img src to set as dataset value
                        const imgSrc = existingImg.src;
                        const storagePath = imgSrc.replace(window.location.origin, '').replace('/storage/', '');
                        if (storagePath && storagePath !== imgSrc) {
                            imageInput.dataset.value = '/storage/' + storagePath;
                        }
                    }
                });

                // Initialize toggle states
                document.querySelectorAll(`#editModal${checkoutId} .inventory-type`).forEach(select => {
                    toggleInventoryValue(select, checkoutId);
                });

                updateInventoryDetails(checkoutId);
            });
        });

        // Form submission validation
        document.querySelectorAll('form[id^="checkoutForm"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const checkoutId = this.action.split('/').pop();
                const items = document.querySelectorAll(`#inventory_items_container_${checkoutId} .inventory-item`);

                if (items.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng thêm ít nhất một mục kiểm kê!');
                    return;
                }

                // Check for empty items and warn user
                let hasEmptyItems = false;
                items.forEach(item => {
                    const type = item.querySelector('.inventory-type').value;
                    if (type === 'TEXT') {
                        const textValue = item.querySelector('.inventory-value-text').value.trim();
                        if (!textValue) {
                            hasEmptyItems = true;
                        }
                    } else if (type === 'IMAGE') {
                        const fileInput = item.querySelector('.inventory-value-image');
                        const existingValueInput = item.querySelector('input[name="existing_image_value[]"]');
                        const hasNewFile = fileInput.files.length > 0;
                        const hasExistingValue = existingValueInput && existingValueInput.value !== '';

                        if (!hasNewFile && !hasExistingValue) {
                            hasEmptyItems = true;
                        }
                    }
                });

                if (hasEmptyItems) {
                    const confirmSubmit = confirm('Có một số mục kiểm kê chưa có nội dung. Bạn có muốn tiếp tục lưu không?');
                    if (!confirmSubmit) {
                        e.preventDefault();
                        return;
                    }
                }

                updateInventoryDetails(checkoutId);
            });
        });
    </script>
@endsection
