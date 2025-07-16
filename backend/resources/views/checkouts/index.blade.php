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
                            <select name="status" id="status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ kiểm kê" {{ request('status') == 'Chờ kiểm kê' ? 'selected' : '' }}>Chờ kiểm kê</option>
                                <option value="Đã kiểm kê" {{ request('status') == 'Đã kiểm kê' ? 'selected' : '' }}>Đã kiểm kê</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="sort_order" id="sort_order" class="form-select">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
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
                                        <span class="badge bg-{{ $checkout->has_left == 0 ? 'warning' : 'success' }} py-2 px-3">
                                            <i class="{{ $checkout->has_left == 0 ? 'fas fa-clock' : 'fas fa-check-circle' }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $checkout->status == 'Chờ kiểm kê' ? 'warning' : ($checkout->status == 'Đã kiểm kê' ? 'success' : 'dark') }} py-2 px-3">
                                            <i class="{{ $checkout->status == 'Chờ kiểm kê' ? 'fas fa-clock' : ($checkout->status == 'Đã kiểm kê' ? 'fas fa-check-circle' : 'fas fa-times-circle') }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-sm shadow-sm" data-bs-toggle="modal"
                                            data-bs-target="#checkoutModal{{ $checkout->id }}">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </button>
                                        @if ($checkout->status !== 'Đã kiểm kê')
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
                                                <h5 class="modal-title" id="checkoutModalLabel{{ $checkout->id }}">Chi tiết Checkout</h5>
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
                                                <p><strong>Trạng thái:</strong> {{ $checkout->status ?? 'N/A' }}</p>
                                                <p><strong>Số tiền khấu trừ:</strong>
                                                    {{ $checkout->deduction_amount ? number_format($checkout->deduction_amount, 0, ',', '.') : 'N/A' }} VNĐ</p>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Kiểm kê:</strong></p>
                                                        @if ($checkout->inventory_details && is_array($checkout->inventory_details))
                                                            <div class="bg-light p-3 rounded">
                                                                @foreach ($checkout->inventory_details as $item)
                                                                    <p class="mb-1">
                                                                        <strong>Tên:</strong> {{ $item['item_name'] ?? 'N/A' }}<br>
                                                                        <strong>Tình trạng:</strong> {{ $item['item_condition'] ?? 'N/A' }}<br>
                                                                        <strong>Số lượng:</strong> {{ $item['item_quantity'] ?? 0 }}<br>
                                                                        <strong>Chi phí:</strong> {{ $item['item_cost'] ? number_format($item['item_cost'], 0, ',', '.') : 'N/A' }} VNĐ
                                                                    </p>
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
                                                                @foreach ($checkout->images as $index => $image)
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
                                                <h5 class="modal-title" id="editModalLabel{{ $checkout->id }}">Sửa Checkout</h5>
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
                                                        <label for="has_left{{ $checkout->id }}" class="form-label">Rời đi <span
                                                                style="color: red;">*</span></label>
                                                        <select class="form-select" id="has_left{{ $checkout->id }}"
                                                            name="has_left" required>
                                                            <option value="0" {{ $checkout->has_left == 0 ? 'selected' : '' }}>Chưa rời đi</option>
                                                            <option value="1" {{ $checkout->has_left == 1 ? 'selected' : '' }}>Đã rời đi</option>
                                                        </select>
                                                        @error('has_left')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="status{{ $checkout->id }}" class="form-label">Trạng thái <span
                                                                style="color: red;">*</span></label>
                                                        <select class="form-select" id="status{{ $checkout->id }}"
                                                            name="status" required>
                                                            <option value="Chờ kiểm kê" {{ $checkout->status == 'Chờ kiểm kê' ? 'selected' : '' }}>Chờ kiểm kê</option>
                                                            <option value="Đã kiểm kê" {{ $checkout->status == 'Đã kiểm kê' ? 'selected' : '' }}>Đã kiểm kê</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <!-- Inventory Details Section -->
                                                    <div class="mb-3">
                                                        <label class="form-label">Kiểm kê</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <h6>Thông tin kiểm kê</h6>
                                                                <button type="button" class="btn btn-sm btn-success mb-2 add-inventory-item"
                                                                    data-checkout-id="{{ $checkout->id }}">
                                                                    <i class="fas fa-plus"></i> Thêm mục
                                                                </button>
                                                                <div id="inventory_items_container_{{ $checkout->id }}">
                                                                    @if ($checkout->inventory_details && is_array($checkout->inventory_details))
                                                                        @foreach ($checkout->inventory_details as $index => $item)
                                                                            <div class="inventory-item mb-2" data-item-id="{{ $index }}">
                                                                                <div class="row g-2">
                                                                                    <div class="col-md-3">
                                                                                        <input type="text"
                                                                                            class="form-control form-control-sm"
                                                                                            name="item_name[]"
                                                                                            value="{{ $item['item_name'] ?? '' }}"
                                                                                            placeholder="Tên mục" required>
                                                                                        @error("item_name.$index")
                                                                                            <div class="text-danger small">{{ $message }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <input type="text"
                                                                                            class="form-control form-control-sm"
                                                                                            name="item_condition[]"
                                                                                            value="{{ $item['item_condition'] ?? '' }}"
                                                                                            placeholder="Tình trạng">
                                                                                        @error("item_condition.$index")
                                                                                            <div class="text-danger small">{{ $message }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-2">
                                                                                        <input type="number"
                                                                                            class="form-control form-control-sm"
                                                                                            name="item_quantity[]"
                                                                                            value="{{ $item['item_quantity'] ?? 0 }}"
                                                                                            placeholder="Số lượng"
                                                                                            min="0">
                                                                                        @error("item_quantity.$index")
                                                                                            <div class="text-danger small">{{ $message }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-3">
                                                                                        <input type="number"
                                                                                            class="form-control form-control-sm item-cost"
                                                                                            name="item_cost[]"
                                                                                            value="{{ $item['item_cost'] ?? '' }}"
                                                                                            placeholder="Chi phí (VNĐ)"
                                                                                            step="0.01" min="0">
                                                                                        @error("item_cost.$index")
                                                                                            <div class="text-danger small">{{ $message }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="col-md-1">
                                                                                        <button type="button"
                                                                                            class="btn btn-sm btn-danger remove-inventory-item">
                                                                                            <i class="fas fa-trash"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="inventory-item mb-2" data-item-id="0">
                                                                            <div class="row g-2">
                                                                                <div class="col-md-3">
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        name="item_name[]"
                                                                                        placeholder="Tên mục" required>
                                                                                    @error('item_name.0')
                                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        name="item_condition[]"
                                                                                        placeholder="Tình trạng">
                                                                                    @error('item_condition.0')
                                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <input type="number"
                                                                                        class="form-control form-control-sm"
                                                                                        name="item_quantity[]"
                                                                                        placeholder="Số lượng" min="0" value="1">
                                                                                    @error('item_quantity.0')
                                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <input type="number"
                                                                                        class="form-control form-control-sm item-cost"
                                                                                        name="item_cost[]"
                                                                                        placeholder="Chi phí (VNĐ)"
                                                                                        step="0.01" min="0">
                                                                                    @error('item_cost.0')
                                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="col-md-1">
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-danger remove-inventory-item">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <h6>Hình ảnh</h6>
                                                                <div class="mb-2">
                                                                    <input type="file"
                                                                        class="form-control form-control-sm"
                                                                        name="images[]" multiple accept="image/*"
                                                                        onchange="previewImages(this, {{ $checkout->id }})">
                                                                    <small class="text-muted">Có thể chọn nhiều hình ảnh</small>
                                                                    @error('images.*')
                                                                        <div class="text-danger small">{{ $message }}</div>
                                                                    @endif
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
                                                                    <label for="deduction_amount_total_{{ $checkout->id }}"
                                                                        class="form-label">Tổng số tiền khấu trừ (VNĐ)</label>
                                                                    <input type="number" class="form-control form-control-sm"
                                                                        id="deduction_amount_total_{{ $checkout->id }}"
                                                                        name="deduction_amount_total" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

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
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
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
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Khởi tạo sự kiện cho tất cả các form checkout
            document.querySelectorAll('form[id^="checkoutForm"]').forEach(form => {
                if (!form) {
                    console.error('Form not found');
                    return;
                }

                // Hàm thêm mục kiểm kê mới
                function addInventoryItem(checkoutId) {
                    const container = document.getElementById(`inventory_items_container_${checkoutId}`);
                    if (!container) {
                        console.error(`Container with ID inventory_items_container_${checkoutId} not found`);
                        return;
                    }

                    const itemId = container.querySelectorAll('.inventory-item').length;
                    const div = document.createElement('div');
                    div.className = 'inventory-item mb-2';
                    div.setAttribute('data-item-id', itemId);
                    div.innerHTML = `
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" name="item_name[]" placeholder="Tên mục" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" name="item_condition[]" placeholder="Tình trạng">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control form-control-sm" name="item_quantity[]" placeholder="Số lượng" min="0" value="1">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control form-control-sm item-cost" name="item_cost[]" placeholder="Chi phí (VNĐ)" step="0.01" min="0">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger remove-inventory-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    container.appendChild(div);

                    // Gắn sự kiện xóa và cập nhật tổng
                    div.querySelector('.remove-inventory-item').addEventListener('click', function () {
                        removeInventoryItem(this);
                        updateDeductionTotal(form);
                    });
                    div.querySelector('.item-cost').addEventListener('input', function () {
                        updateDeductionTotal(form);
                    });

                    console.log(`Added new inventory item with ID ${itemId} for checkout ${checkoutId}`);
                    updateDeductionTotal(form);
                }

                // Hàm xóa mục kiểm kê
                function removeInventoryItem(button) {
                    const item = button.closest('.inventory-item');
                    if (item) {
                        item.remove();
                        console.log('Removed inventory item');
                    } else {
                        console.error('Could not find inventory item to remove');
                    }
                }

                // Hàm cập nhật tổng chi phí khấu trừ
                function updateDeductionTotal(form) {
                    const checkoutId = form.id.replace('checkoutForm', '');
                    const totalInput = document.getElementById(`deduction_amount_total_${checkoutId}`);
                    if (!totalInput) {
                        console.error(`Total input with ID deduction_amount_total_${checkoutId} not found`);
                        return;
                    }
                    let total = 0;
                    form.querySelectorAll('input[name="item_cost[]"]').forEach(input => {
                        total += parseFloat(input.value) || 0;
                    });
                    totalInput.value = total.toFixed(2);
                }

                // Hàm xem trước hình ảnh
                function previewImages(input, checkoutId) {
                    const previewContainer = document.getElementById(`image_preview_${checkoutId}`);
                    if (!previewContainer) {
                        console.error(`Preview container with ID image_preview_${checkoutId} not found`);
                        return;
                    }
                    previewContainer.innerHTML = '';

                    if (input.files) {
                        Array.from(input.files).forEach((file, index) => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'col-4 mb-2';
                                div.innerHTML = `
                                    <div class="image-preview-item">
                                        <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 80px; object-fit: cover;" alt="Preview">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removePreviewImage(this, ${index})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                `;
                                previewContainer.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                    }
                }

                // Hàm xóa hình ảnh xem trước
                function removePreviewImage(button, index) {
                    const container = button.closest('.col-4');
                    if (container) {
                        container.remove();
                        const fileInput = form.querySelector('input[type="file"]');
                        if (fileInput) {
                            const dt = new DataTransfer();
                            const { files } = fileInput;
                            for (let i = 0; i < files.length; i++) {
                                if (i !== index) dt.items.add(files[i]);
                            }
                            fileInput.files = dt.files;
                        }
                        console.log(`Removed preview image at index ${index}`);
                    } else {
                        console.error('Could not find preview image to remove');
                    }
                }

                // Hàm xóa hình ảnh hiện có
                function removeExistingImage(button, imagePath, checkoutId) {
                    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
                        const container = button.closest('.col-4');
                        if (container) {
                            container.remove();
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'images_to_delete[]';
                            hiddenInput.value = imagePath;
                            form.appendChild(hiddenInput);
                            console.log(`Marked image ${imagePath} for deletion`);
                        } else {
                            console.error('Could not find image container to remove');
                        }
                    }
                }

                // Gắn sự kiện cho nút "Thêm mục"
                const addButtons = form.querySelectorAll('.add-inventory-item');
                if (addButtons.length > 0) {
                    addButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            console.log('Add button clicked for checkout ID:', this.getAttribute('data-checkout-id'));
                            const checkoutId = this.getAttribute('data-checkout-id');
                            addInventoryItem(checkoutId);
                        });
                    });
                } else {
                    console.error('No .add-inventory-item buttons found in form');
                }

                // Gắn sự kiện cho nút "Xóa"
                form.querySelectorAll('.remove-inventory-item').forEach(button => {
                    button.addEventListener('click', function () {
                        removeInventoryItem(this);
                        updateDeductionTotal(form);
                    });
                });

                // Gắn sự kiện cho input hình ảnh
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.addEventListener('change', function () {
                        previewImages(this, form.id.replace('checkoutForm', ''));
                    });
                }

                // Cập nhật tổng chi phí khi load trang
                updateDeductionTotal(form);

                // Gắn sự kiện cho input chi phí
                form.querySelectorAll('input[name="item_cost[]"]').forEach(input => {
                    input.addEventListener('input', function () {
                        updateDeductionTotal(form);
                    });
                });

                // Gắn sự kiện submit form để debug
                form.addEventListener('submit', function(e) {
                    const formData = new FormData(this);
                    const items = [];
                    const itemNames = formData.getAll('item_name[]');
                    const itemConditions = formData.getAll('item_condition[]');
                    const itemQuantities = formData.getAll('item_quantity[]');
                    const itemCosts = formData.getAll('item_cost[]');

                    for (let i = 0; i < itemNames.length; i++) {
                        items.push({
                            item_name: itemNames[i],
                            item_condition: itemConditions[i] || '',
                            item_quantity: itemQuantities[i] || '0',
                            item_cost: itemCosts[i] || '0'
                        });
                    }
                    console.log('Form data to be submitted:', {
                        item_name: formData.getAll('item_name[]'),
                        item_condition: formData.getAll('item_condition[]'),
                        item_quantity: formData.getAll('item_quantity[]'),
                        item_cost: formData.getAll('item_cost[]'),
                        all_items: items
                    });
                });
            });

            // Đưa các hàm ra phạm vi toàn cục để có thể gọi từ HTML
            window.addInventoryItem = addInventoryItem;
            window.removeInventoryItem = removeInventoryItem;
            window.previewImages = previewImages;
            window.removePreviewImage = removePreviewImage;
            window.removeExistingImage = removeExistingImage;
        });
    </script>
@endsection
