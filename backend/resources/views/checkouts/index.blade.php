@extends('layouts.app')

@section('title', 'Quản lý Checkout')

@section('content')
    <div class="container-fluid py-5 px-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4"
                style="background: linear-gradient(90deg, #007bff, #00c6ff);">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-check-circle me-2"></i>Quản lý Checkout
                </h5>
            </div>

            <div class="card-body p-4">
                <!-- Search and Filter Form -->
                <form method="GET" action="{{ route('checkouts.index') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control shadow-sm" name="querySearch"
                                    placeholder="Tìm kiếm theo phòng..." value="{{ request('querySearch') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="inventory_status" id="inventory_status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ kiểm kê"
                                    {{ request('inventory_status') == 'Chờ kiểm kê' ? 'selected' : '' }}>Chờ
                                    kiểm kê</option>
                                <option value="Đã kiểm kê"
                                    {{ request('inventory_status') == 'Đã kiểm kê' ? 'selected' : '' }}>Đã kiểm
                                    kê</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="sort_order" id="sort_order" class="form-select">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Mới nhất
                                </option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Cũ nhất
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                            <a href="{{ route('checkouts.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden"
                        style="text-align: center">
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
                                        <span
                                            class="badge bg-{{ $checkout->has_left == 0 ? 'warning' : 'success' }} py-2 px-3">
                                            <i class="{{ $checkout->has_left == 0 ? 'fas fa-clock' : 'fas fa-check-circle' }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $checkout->inventory_status == 'Chờ kiểm kê' ? 'warning' : ($checkout->inventory_status == 'Đã kiểm kê' ? 'success' : 'dark') }} py-2 px-3">
                                            <i class="{{ $checkout->inventory_status == 'Chờ kiểm kê' ? 'fas fa-clock' : ($checkout->inventory_status == 'Đã kiểm kê' ? 'fas fa-check-circle' : 'fas fa-times-circle') }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->inventory_status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-sm shadow-sm" data-bs-toggle="modal"
                                            data-bs-target="#checkoutModal{{ $checkout->id }}">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </button>
                                        @if ($checkout->inventory_status !== 'Đã kiểm kê')
                                            <button type="button" class="btn btn-warning btn-sm shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $checkout->id }}">
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
                                <div class="modal fade" id="checkoutModal{{ $checkout->id }}" tabindex="-1"
                                    aria-labelledby="checkoutModalLabel{{ $checkout->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title" id="checkoutModalLabel{{ $checkout->id }}">Chi
                                                    tiết Checkout</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Tên phòng:</strong> {{ $checkout->contract->room->name }}</p>
                                                <p><strong>Ngày checkout:</strong>
                                                    {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}
                                                </p>
                                                <p><strong>Rời đi:</strong>
                                                    {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}</p>
                                                <p><strong>Trạng thái:</strong> {{ $checkout->inventory_status ?? 'N/A' }}
                                                </p>
                                                <p><strong>Số tiền khấu trừ:</strong>
                                                    {{ $checkout->deduction_amount ? number_format($checkout->deduction_amount, 0, ',', '.') : 'N/A' }}
                                                    VNĐ</p>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Kiểm kê:</strong></p>
                                                        @if ($checkout->inventory_details && is_array($checkout->inventory_details))
                                                            <div class="bg-light p-3 rounded">
                                                                @foreach ($checkout->inventory_details as $item)
                                                                    <div class="border-bottom mb-2 pb-2">
                                                                        <p class="mb-1">
                                                                            <strong>Tên:</strong>
                                                                            {{ $item['item_name'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p class="mb-1">
                                                                            <strong>Tình trạng:</strong>
                                                                            {{ $item['item_condition'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p class="mb-1">
                                                                            <strong>Chi phí:</strong>
                                                                            {{ $item['item_cost'] ? number_format($item['item_cost'], 0, ',', '.') : 'N/A' }}
                                                                            VNĐ
                                                                        </p>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted">Chưa có dữ liệu</p>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-6">
                                                        <p><strong>Hình ảnh:</strong></p>
                                                        @if ($checkout->images && count($checkout->images) > 0)
                                                            <div class="row">
                                                                @foreach ($checkout->images as $image)
                                                                    <div class="col-6 mb-2">
                                                                        <img src="{{ asset('storage/' . $image) }}"
                                                                            class="img-fluid rounded"
                                                                            style="max-height: 100px; object-fit: cover;"
                                                                            alt="Checkout Image">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted">Chưa có hình ảnh</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal for Edit -->
                                <div class="modal fade" id="editModal{{ $checkout->id }}" tabindex="-1"
                                    aria-labelledby="editModalLabel{{ $checkout->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title" id="editModalLabel{{ $checkout->id }}">Sửa
                                                    Checkout</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('checkouts.update', $checkout->id) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    id="checkoutForm{{ $checkout->id }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="mb-3">
                                                        <label for="check_out_date{{ $checkout->id }}"
                                                            class="form-label">Ngày checkout <span
                                                                style="color: red;">*</span></label>
                                                        <input type="date" class="form-control"
                                                            id="check_out_date{{ $checkout->id }}" name="check_out_date"
                                                            value="{{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('Y-m-d') : '' }}"
                                                            required>
                                                        @error('check_out_date')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="has_left{{ $checkout->id }}" class="form-label">Rời
                                                            đi <span style="color: red;">*</span></label>
                                                        <select class="form-select" id="has_left{{ $checkout->id }}"
                                                            name="has_left" required>
                                                            <option value="0"
                                                                {{ $checkout->has_left == 0 ? 'selected' : '' }}>Chưa rời
                                                                đi</option>
                                                            <option value="1"
                                                                {{ $checkout->has_left == 1 ? 'selected' : '' }}>Đã rời đi
                                                            </option>
                                                        </select>
                                                        @error('has_left')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Change this part in your Blade template -->
                                                    <div class="mb-3">
                                                        <label for="inventory_status{{ $checkout->id }}"
                                                            class="form-label">Trạng thái <span
                                                                style="color: red;">*</span></label>
                                                        <select class="form-select"
                                                            id="inventory_status{{ $checkout->id }}" name="status"
                                                            required>
                                                            <option value="Chờ kiểm kê"
                                                                {{ $checkout->inventory_status == 'Chờ kiểm kê' ? 'selected' : '' }}>
                                                                Chờ kiểm kê
                                                            </option>
                                                            <option value="Đã kiểm kê"
                                                                {{ $checkout->inventory_status == 'Đã kiểm kê' ? 'selected' : '' }}>
                                                                Đã kiểm kê
                                                            </option>
                                                        </select>
                                                        @error('inventory_status')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Inventory Details Section -->
                                                    <!-- Trong modal Edit section, thay thế phần Inventory Details -->
                                                    <div class="mb-3">
                                                        <label class="form-label">Kiểm kê</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <h6>Thông tin kiểm kê</h6>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-success mb-2 add-inventory-item"
                                                                    data-checkout-id="{{ $checkout->id }}">
                                                                    <i class="fas fa-plus"></i> Thêm mục
                                                                </button>

                                                                <!-- Container cho các item inventory -->
                                                                <div id="inventory_items_container_{{ $checkout->id }}">
                                                                    <!-- Các item sẽ được tạo động bằng JavaScript -->
                                                                </div>

                                                                <!-- Hiển thị JSON data để debug -->
                                                                {{-- <div class="mt-3">
                                                                    <h6>JSON Data (Debug):</h6>
                                                                    <pre id="inventory_json_display_{{ $checkout->id }}"
                                                                        style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; max-height: 200px; overflow-y: auto;">
</pre>
                                                                </div> --}}
                                                            </div>

                                                            <div class="col-md-12">
                                                                <h6>Hình ảnh</h6>
                                                                <div class="mb-2">
                                                                    <input type="file"
                                                                        class="form-control form-control-sm"
                                                                        name="images[]"
                                                                        id="images_input_{{ $checkout->id }}" multiple
                                                                        accept="image/*"
                                                                        onchange="previewImages(this, {{ $checkout->id }})">
                                                                    <small class="text-muted">Có thể chọn nhiều hình
                                                                        ảnh</small>
                                                                    @error('images.*')
                                                                        <div class="text-danger small">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>

                                                                <!-- Preview existing images -->
                                                                <div id="existing_images_{{ $checkout->id }}"
                                                                    class="mb-2">
                                                                    @if ($checkout->images && count($checkout->images) > 0)
                                                                        <div class="row">
                                                                            @foreach ($checkout->images as $index => $image)
                                                                                <div class="col-4 mb-2">
                                                                                    <div class="position-relative">
                                                                                        <img src="{{ asset('storage/' . $image) }}"
                                                                                            class="img-fluid rounded"
                                                                                            style="max-height: 80px; object-fit: cover;"
                                                                                            alt="Existing Image">
                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                                                                            onclick="removeExistingImage(this, '{{ $image }}', {{ $checkout->id }})">
                                                                                            <i class="fas fa-times"></i>
                                                                                        </button>
                                                                                        <input type="hidden"
                                                                                            name="existing_images[]"
                                                                                            value="{{ $image }}">
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <!-- Preview new images -->
                                                                <div id="image_preview_{{ $checkout->id }}"
                                                                    class="row"></div>
                                                            </div>

                                                            <!-- Total Deduction Amount (Readonly) -->
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label
                                                                        for="deduction_amount_total_{{ $checkout->id }}"
                                                                        class="form-label">Tổng số tiền khấu trừ
                                                                        (VNĐ)
                                                                    </label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm"
                                                                        id="deduction_amount_total_{{ $checkout->id }}"
                                                                        readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <script>
                                                        // Truyền dữ liệu existing từ server vào JavaScript
                                                        window.existingInventoryData = window.existingInventoryData || {};
                                                        window.existingInventoryData[{{ $checkout->id }}] = @json($checkout->inventory_details ?? []);
                                                    </script>

                                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Đóng</button>
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

                @if ($checkouts->hasPages())
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
        /* Additional styles for checkout form */
        .inventory-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            margin-bottom: 10px;
        }

        .inventory-item:hover {
            background: #e9ecef;
            border-color: #ced4da;
        }

        #inventory_json_display_{{ $checkout->id }} {
            background: #f8f9fa !important;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .json-display {
            background: #f8f9fa !important;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .image-preview-item {
            position: relative;
            margin-bottom: 10px;
        }

        .image-preview-item .btn-danger {
            position: absolute;
            top: 5px;
            right: 5px;
            padding: 2px 6px;
            font-size: 12px;
        }

        .inventory-controls {
            background: #ffffff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 15px;
        }

        .inventory-summary {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .inventory-summary h6 {
            color: #1976d2;
            margin-bottom: 5px;
        }

        .btn-add-item {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-add-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .form-control-sm:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .inventory-item .btn-danger {
            transition: all 0.3s ease;
        }

        .inventory-item .btn-danger:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        /* Animation cho các item mới */
        .inventory-item.fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .inventory-item .row .col-md-1,
            .inventory-item .row .col-md-2,
            .inventory-item .row .col-md-3 {
                margin-bottom: 8px;
            }

            .json-display {
                font-size: 10px;
                max-height: 150px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Biến tạm để lưu trữ dữ liệu inventory cho mỗi checkout
        let inventoryData = {};

        // Hàm xóa hình ảnh hiện tại
        function removeExistingImage(button, imagePath, checkoutId) {
            // Xác nhận trước khi xóa
            if (!confirm('Bạn có chắc chắn muốn xóa hình ảnh này không?')) {
                return;
            }

            const imageContainer = button.closest('.col-4');
            const hiddenInput = imageContainer.querySelector('input[name="existing_images[]"]');

            // Xóa hidden input để không gửi image này lên server
            if (hiddenInput) {
                hiddenInput.remove();
            }

            // Tạo input để đánh dấu image này cần xóa
            const form = document.getElementById(`checkoutForm${checkoutId}`);
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'deleted_images[]';
            deleteInput.value = imagePath;
            form.appendChild(deleteInput);

            // Ẩn container thay vì xóa luôn (để tránh bug)
            imageContainer.style.display = 'none';
        }

        // Hàm preview hình ảnh
        function previewImages(input, checkoutId) {
            const previewContainer = document.getElementById(`image_preview_${checkoutId}`);

            if (!previewContainer) return;

            // Clear existing previews
            previewContainer.innerHTML = '';

            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-4 mb-2';

                        col.innerHTML = `
                            <div class="image-preview-item">
                                <img src="${e.target.result}"
                                     class="img-fluid rounded"
                                     style="max-height: 80px; object-fit: cover; width: 100%;"
                                     alt="Preview">
                                <button type="button"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                        onclick="removeNewImage(this, ${index}, ${checkoutId})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;

                        previewContainer.appendChild(col);
                    };

                    reader.readAsDataURL(file);
                });
            }
        }

        // Hàm xóa hình ảnh mới
        function removeNewImage(button, imageIndex, checkoutId) {
            // Xác nhận trước khi xóa
            if (!confirm('Bạn có chắc chắn muốn xóa hình ảnh này không?')) {
                return;
            }

            const imageContainer = button.closest('.col-4');
            const fileInput = document.querySelector(`#images_input_${checkoutId}`);

            // Xóa preview
            imageContainer.remove();

            // Tạo lại file input để loại bỏ file đã chọn
            if (fileInput) {
                const dt = new DataTransfer();
                const files = fileInput.files;

                for (let i = 0; i < files.length; i++) {
                    if (i !== imageIndex) {
                        dt.items.add(files[i]);
                    }
                }

                fileInput.files = dt.files;
            }
        }

        // Khởi tạo dữ liệu inventory cho một checkout
        function initializeInventoryData(checkoutId, existingData = null) {
            if (!inventoryData[checkoutId]) {
                inventoryData[checkoutId] = existingData || [];
            }
            updateInventoryDisplay(checkoutId);
            updateDeductionTotal(checkoutId);
        }

        // Thêm một item mới vào mảng tạm
        function addInventoryItem(checkoutId) {
            if (!inventoryData[checkoutId]) {
                inventoryData[checkoutId] = [];
            }

            const newItem = {
                id: Date.now() + Math.random(), // Tạo ID tạm thời
                item_name: '',
                item_condition: '',
                item_cost: 0
            };

            inventoryData[checkoutId].push(newItem);
            updateInventoryDisplay(checkoutId);
            updateDeductionTotal(checkoutId);

            console.log(`Added new item to checkout ${checkoutId}:`, newItem);
            console.log(`Current inventory data for checkout ${checkoutId}:`, inventoryData[checkoutId]);
        }

        // Xóa item khỏi mảng tạm
        function removeInventoryItem(checkoutId, itemId) {
            // Xác nhận trước khi xóa
            if (!confirm('Bạn có chắc chắn muốn xóa mục này không?')) {
                return;
            }

            if (inventoryData[checkoutId]) {
                inventoryData[checkoutId] = inventoryData[checkoutId].filter(item => item.id !== itemId);
                updateInventoryDisplay(checkoutId);
                updateDeductionTotal(checkoutId);

                console.log(`Removed item ${itemId} from checkout ${checkoutId}`);
                console.log(`Current inventory data for checkout ${checkoutId}:`, inventoryData[checkoutId]);
            }
        }

        // Cập nhật dữ liệu item trong mảng tạm
        function updateInventoryItem(checkoutId, itemId, field, value) {
            if (inventoryData[checkoutId]) {
                const item = inventoryData[checkoutId].find(item => item.id === itemId);
                if (item) {
                    item[field] = field === 'item_cost' ? parseFloat(value) || 0 : value;
                    updateDeductionTotal(checkoutId);

                    console.log(`Updated item ${itemId} in checkout ${checkoutId}:`, item);
                }
            }
        }

        // Hiển thị dữ liệu inventory từ mảng tạm
        function updateInventoryDisplay(checkoutId) {
            const container = document.getElementById(`inventory_items_container_${checkoutId}`);
            const jsonDisplay = document.getElementById(`inventory_json_display_${checkoutId}`);

            if (!container) return;

            // Clear container
            container.innerHTML = '';

            // Hiển thị JSON data
            if (jsonDisplay) {
                jsonDisplay.textContent = JSON.stringify(inventoryData[checkoutId] || [], null, 2);
            }

            // Tạo lại các input field
            if (inventoryData[checkoutId]) {
                inventoryData[checkoutId].forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'inventory-item mb-2';
                    div.dataset.itemId = item.id;

                    div.innerHTML = `
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       value="${item.item_name || ''}"
                                       placeholder="Tên mục"
                                       onchange="updateInventoryItem(${checkoutId}, ${item.id}, 'item_name', this.value)" requied>
                            </div>
                            <div class="col-md-4">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       value="${item.item_condition || ''}"
                                       placeholder="Tình trạng"
                                       onchange="updateInventoryItem(${checkoutId}, ${item.id}, 'item_condition', this.value)">
                            </div>
                            <div class="col-md-2">
                                <input type="number"
                                       class="form-control form-control-sm"
                                       value="${item.item_cost || 0}"
                                       placeholder="Chi phí (VNĐ)"
                                       step="0.01"
                                       min="0"
                                       onchange="updateInventoryItem(${checkoutId}, ${item.id}, 'item_cost', this.value)">
                            </div>
                            <div class="col-md-1">
                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        onclick="removeInventoryItem(${checkoutId}, ${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

                    container.appendChild(div);
                });
            }
        }

        // Cập nhật tổng tiền khấu trừ
        function updateDeductionTotal(checkoutId) {
            const totalInput = document.getElementById(`deduction_amount_total_${checkoutId}`);
            if (!totalInput) return;

            let total = 0;
            if (inventoryData[checkoutId]) {
                inventoryData[checkoutId].forEach(item => {
                    const cost = parseFloat(item.item_cost) || 0;
                    total += cost;
                });
            }

            totalInput.value = total.toFixed(2);
        }

        // Chuẩn bị dữ liệu để submit
        function prepareFormDataForSubmit(checkoutId) {
            const form = document.getElementById(`checkoutForm${checkoutId}`);
            if (!form || !inventoryData[checkoutId]) return;

            // Xóa các input cũ
            const oldInputs = form.querySelectorAll('input[name^="item_"]');
            oldInputs.forEach(input => input.remove());

            // Thêm các input mới từ mảng tạm
            inventoryData[checkoutId].forEach(item => {
                // Chỉ thêm những item có tên
                if (item.item_name && item.item_name.trim() !== '') {
                    const nameInput = document.createElement('input');
                    nameInput.type = 'hidden';
                    nameInput.name = 'item_name[]';
                    nameInput.value = item.item_name;
                    form.appendChild(nameInput);

                    const conditionInput = document.createElement('input');
                    conditionInput.type = 'hidden';
                    conditionInput.name = 'item_condition[]';
                    conditionInput.value = item.item_condition || '';
                    form.appendChild(conditionInput);

                    const costInput = document.createElement('input');
                    costInput.type = 'hidden';
                    costInput.name = 'item_cost[]';
                    costInput.value = item.item_cost || 0;
                    form.appendChild(costInput);
                }
            });

            // Thêm deduction amount
            const totalInput = document.getElementById(`deduction_amount_total_${checkoutId}`);
            if (totalInput) {
                const existingInput = form.querySelector('input[name="deduction_amount"]');
                if (existingInput) {
                    existingInput.remove();
                }

                const deductionInput = document.createElement('input');
                deductionInput.type = 'hidden';
                deductionInput.name = 'deduction_amount';
                deductionInput.value = totalInput.value;
                form.appendChild(deductionInput);
            }

            console.log(`Prepared form data for checkout ${checkoutId}`);
            console.log('Final inventory data:', inventoryData[checkoutId]);
        }

        // Khởi tạo khi trang được load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing checkout form with temporary storage');

            // Khởi tạo dữ liệu cho tất cả các checkout form
            document.querySelectorAll('form[id^="checkoutForm"]').forEach(form => {
                const checkoutId = form.id.replace('checkoutForm', '');

                // Lấy dữ liệu existing từ server (nếu có)
                const existingData = getExistingInventoryData(checkoutId);
                initializeInventoryData(checkoutId, existingData);

                // Xử lý submit form
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Kiểm tra xem có ít nhất một item không
                    if (!inventoryData[checkoutId] || inventoryData[checkoutId].length === 0) {
                        alert('Vui lòng thêm ít nhất một mục kiểm kê!');
                        return;
                    }

                    // Kiểm tra xem có item nào có tên không
                    const validItems = inventoryData[checkoutId].filter(item =>
                        item.item_name && item.item_name.trim() !== ''
                    );

                    if (validItems.length === 0) {
                        alert('Vui lòng nhập tên cho ít nhất một mục kiểm kê!');
                        return;
                    }

                    // Chuẩn bị dữ liệu và submit
                    prepareFormDataForSubmit(checkoutId);

                    // Submit form
                    this.submit();
                });
            });

            // Add event listeners cho các nút "Thêm mục"
            document.addEventListener('click', function(e) {
                if (e.target.closest('.add-inventory-item')) {
                    e.preventDefault();
                    const button = e.target.closest('.add-inventory-item');
                    const checkoutId = button.getAttribute('data-checkout-id');
                    addInventoryItem(checkoutId);
                }
            });

            console.log('Checkout form initialization with temporary storage completed');
        });

        // Hàm helper để lấy dữ liệu existing từ server
        function getExistingInventoryData(checkoutId) {
            // Này sẽ được gọi từ Blade template với dữ liệu từ server
            // Ví dụ: window.existingInventoryData[checkoutId]
            if (window.existingInventoryData && window.existingInventoryData[checkoutId]) {
                return window.existingInventoryData[checkoutId].map(item => ({
                    id: Date.now() + Math.random(),
                    item_name: item.item_name || '',
                    item_condition: item.item_condition || '',
                    item_cost: item.item_cost || 0
                }));
            }
            return [];
        }
    </script>
@endsection
