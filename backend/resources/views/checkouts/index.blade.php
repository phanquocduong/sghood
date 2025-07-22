@extends('layouts.app')

@section('title', 'Quản lý Checkout')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
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
                                <option value="Kiểm kê lại"
                                    {{ request('inventory_status') == 'Kiểm kê lại' ? 'selected' : '' }}>Kiểm kê lại
                                </option>
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
                                <th scope="col" style="width: 5%;" class="text-center">STT</th>
                                <th scope="col" style="width: 15%;">Tên phòng</th>
                                <th scope="col" style="width: 15%;" class="text-center">Ngày checkout</th>
                                <th scope="col" style="width: 15%;" class="text-center">Rời đi</th>
                                <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                <th scope="col" style="width: 20%;" class="text-center">Trạng thái người dùng</th>
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
                                        <span
                                            class="badge bg-{{ $checkout->user_confirmation_status == 'Đồng ý' ? 'success' : ($checkout->user_confirmation_status == 'Từ chối' ? 'danger' : 'warning') }} py-2 px-3">
                                            <i class="{{ $checkout->user_confirmation_status == 'Đồng ý' ? 'fas fa-check-circle' : ($checkout->user_confirmation_status == 'Từ chối' ? 'fas fa-times-circle' : 'fas fa-clock') }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->user_confirmation_status ?? 'Chờ xác nhận' }}
                                        </span>
                                        @if ($checkout->user_confirmation_status === 'Từ chối' && !empty($checkout->user_rejection_reason))
                                            <div class="mt-1">
                                                <small class="text-danger">
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-{{ $checkout->user_confirmation_status === 'Từ chối' && !empty($checkout->user_rejection_reason) ? 'danger' : 'info' }} btn-sm shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $checkout->id }}">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </button>
                                        @if ($checkout->inventory_status !== 'Đã kiểm kê')
                                            <button type="button" class="btn btn-warning btn-sm shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $checkout->id }}">
                                                <i class="fas fa-edit me-1"></i>Sửa
                                            </button>
                                        @else
                                            @if ($checkout->user_confirmation_status === 'Từ chối')
                                                <button type="button" class="btn btn-danger btn-sm shadow-sm"
                                                    onclick="changeToReInventory({{ $checkout->id }})">
                                                    <i class="fas fa-redo me-1"></i>Kiểm kê lại
                                                </button>
                                            @else
                                                <span class="text-muted small">Đã hoàn thành</span>
                                            @endif
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
                                                <div class="row">
                                                    <!-- Left Column - Basic Info -->
                                                    <div class="col-md-6">
                                                        <p><strong>Tên phòng:</strong>
                                                            {{ $checkout->contract->room->name }}</p>
                                                        <p><strong>Ngày checkout:</strong>
                                                            {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}
                                                        </p>
                                                        <p><strong>Rời đi:</strong>
                                                            {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}
                                                        </p>
                                                        <p><strong>Trạng thái:</strong>
                                                            {{ $checkout->inventory_status ?? 'N/A' }}
                                                        </p>
                                                        <p><strong>Trạng thái người dùng:</strong>
                                                            <span
                                                                class="badge bg-{{ $checkout->user_confirmation_status == 'Đồng ý' ? 'success' : ($checkout->user_confirmation_status == 'Từ chối' ? 'danger' : 'warning') }}">
                                                                {{ $checkout->user_confirmation_status ?? 'Chờ xác nhận' }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Số tiền khấu trừ:</strong>
                                                            {{ $checkout->deduction_amount ? number_format($checkout->deduction_amount, 0, ',', '.') : 'N/A' }}
                                                            VNĐ</p>
                                                        <p><strong>Tiền cọc:</strong>
                                                            {{ $checkout->contract->deposit_amount ? number_format($checkout->contract->deposit_amount, 0, ',', '.') : 'N/A' }}
                                                            VNĐ</p>
                                                        <p><strong>Số tiền hoàn lại:</strong>
                                                            {{ $checkout->final_refunded_amount ? number_format($checkout->final_refunded_amount, 0, ',', '.') : 'N/A' }}
                                                            VNĐ</p>
                                                    </div>

                                                    <!-- Right Column - Rejection Reason or Additional Info -->
                                                    <div class="col-md-6">
                                                        @if ($checkout->user_confirmation_status === 'Từ chối' && !empty($checkout->user_rejection_reason))
                                                            <div class="alert alert-danger">
                                                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Lý do
                                                                    từ chối:</h6>
                                                                <p class="mb-0">{{ $checkout->user_rejection_reason }}
                                                                </p>
                                                            </div>
                                                        @else
                                                            <div class="text-muted">
                                                                <i class="fas fa-info-circle me-2"></i>
                                                                <small>Không có lý do từ chối</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <hr>

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
                                                                            {{ $item['item_cost'] ? number_format($item['item_cost'], 0, ',', '.') : '0' }}
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
                                                            @if ($checkout->inventory_status == 'Chờ kiểm kê')
                                                                {{-- <option value="Chờ kiểm kê" selected>Chờ kiểm kê</option> --}}
                                                                <option value="Đã kiểm kê">Đã kiểm kê</option>
                                                            @else
                                                                {{-- <option value="Kiểm kê lại"
                                                                    {{ $checkout->inventory_status == 'Kiểm kê lại' ? 'selected' : '' }}>
                                                                    Kiểm kê lại
                                                                </option> --}}
                                                                <option value="Đã kiểm kê"
                                                                    {{ $checkout->inventory_status == 'Đã kiểm kê' ? 'selected' : '' }}>
                                                                    Đã kiểm kê
                                                                </option>
                                                            @endif
                                                        </select>
                                                        @error('inventory_status')
                                                            <div class="text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <p><strong>Tiền cọc:</strong>
                                                        {{ $checkout->contract->deposit_amount ? number_format($checkout->contract->deposit_amount, 0, ',', '.') : 'N/A' }}
                                                        VNĐ</p>

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
                                                            <div class="col-md-6">
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

                                                            <!-- Final Refunded Amount (Readonly) -->
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="final_refunded_amount_{{ $checkout->id }}"
                                                                        class="form-label">Số tiền hoàn trả cuối cùng
                                                                        (VNĐ)
                                                                    </label>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm"
                                                                        id="final_refunded_amount_{{ $checkout->id }}"
                                                                        readonly
                                                                        style="background-color: #e8f5e8; font-weight: bold;">
                                                                    <input type="hidden"
                                                                        id="deposit_amount_{{ $checkout->id }}"
                                                                        value="{{ $checkout->contract->deposit_amount ?? 0 }}">
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
    <script>
        // Truyền dữ liệu inventory từ server xuống JavaScript
        window.existingInventoryData = {
            @foreach ($checkouts as $checkout)
                '{{ $checkout->id }}': {!! json_encode($checkout->inventory_details ?? []) !!},
            @endforeach
        };

        console.log('Existing inventory data loaded:', window.existingInventoryData);
    </script>
@endsection
<script src="{{ asset('js/checkout.js') }}"></script>
