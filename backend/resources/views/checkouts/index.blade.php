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
                    <i class="fas fa-check-circle me-2"></i>Quản lý Trả phòng
                    <span class="badge bg-light text-primary ms-2">{{ $checkouts->total() }} Y/c trả phòng</span>
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
                                    placeholder="Tìm kiếm theo mã hợp đồng..." value="{{ request('querySearch') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="inventory_status" id="inventory_status" class="form-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ kiểm kê"
                                    {{ request('inventory_status') == 'Chờ kiểm kê' ? 'selected' : '' }}>Chờ kiểm kê</option>
                                <option value="Kiểm kê lại"
                                    {{ request('inventory_status') == 'Kiểm kê lại' ? 'selected' : '' }}>Kiểm kê lại</option>
                                <option value="Đã kiểm kê"
                                    {{ request('inventory_status') == 'Đã kiểm kê' ? 'selected' : '' }}>Đã kiểm kê</option>
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
                                <th scope="col" style="width: 7%;" class="text-center">Mã HD</th>
                                <th scope="col" style="width: 15%;">Người dùng</th>
                                <th scope="col" style="width: 13%;" class="text-center">Ngày checkout</th>
                                <th scope="col" style="width: 13%;" class="text-center">Hoàn tiền</th>
                                <th scope="col" style="width: 10%;" class="text-center">Trạng thái</th>
                                <th scope="col" style="width: 20%;" class="text-center">Trạng thái người dùng</th>
                                <th scope="col" style="width: 24%;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($checkouts as $checkout)
                                <tr class="table-row">
                                    <td>
                                        <a href="{{ route('contracts.show', $checkout->contract->id) }}"
                                        class="contract-id-clickable"
                                        title="Xem chi tiết hợp đồng">
                                            {{ 'HD'.$checkout->contract->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="user-name-clickable"
                                              data-user-id="{{ $checkout->contract->user->id ?? '' }}"
                                              data-user-name="{{ $checkout->contract->user->name ?? 'N/A' }}"
                                              data-user-email="{{ $checkout->contract->user->email ?? 'N/A' }}"
                                              data-user-phone="{{ $checkout->contract->user->phone ?? 'N/A' }}"
                                              data-user-address="{{ $checkout->contract->user->address ?? 'N/A' }}"
                                              data-user-gender="{{ $checkout->contract->user->gender ?? 'N/A' }}"
                                              data-user-birthdate="{{ $checkout->contract->user->birthdate ?? 'N/A' }}"
                                              data-user-created="{{ $checkout->contract->user->created_at ?? 'N/A' }}"
                                              data-user-avatar="{{ $checkout->contract->user->avatar ? asset($checkout->contract->user->avatar) : asset('img/user.jpg') }}"
                                              title="Nhấn để xem thông tin chi tiết">
                                            {{ $checkout->contract->user->name }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $checkout->refund_status == 'Đã xử lý' ? 'success' : ($checkout->refund_status == 'Hủy bỏ' ? 'danger' : 'warning') }} py-2 px-3">
                                            <i class="{{ $checkout->refund_status == 'Đã xử lý' ? 'fas fa-check-circle' : ($checkout->refund_status == 'Hủy bỏ' ? 'fas fa-times-circle' : 'fas fa-clock') }} me-1"
                                                style="font-size: 8px;"></i>
                                            {{ $checkout->refund_status ?? 'Chờ xử lý' }}
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
                                                    {{ $checkout->user_rejection_reason }}
                                                </small>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-{{ $checkout->user_confirmation_status === 'Từ chối' && !empty($checkout->user_rejection_reason) ? 'danger' : 'info' }} btn-sm shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#checkoutModal{{ $checkout->id }}">
                                            <i class="fas fa-eye me-1"></i>
                                        </button>
                                        @if ($checkout->inventory_status !== 'Đã kiểm kê')
                                            <button type="button" class="btn btn-warning btn-sm shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $checkout->id }}">
                                                <i class="fas fa-edit me-1"></i>Kiểm kê
                                            </button>
                                        @else
                                            @if ($checkout->user_confirmation_status === 'Từ chối')
                                                <button type="button" class="btn btn-danger btn-sm shadow-sm"
                                                    onclick="changeToReInventory({{ $checkout->id }})">
                                                    <i class="fas fa-redo me-1"></i>Kiểm kê lại
                                                </button>
                                            @elseif ($checkout->user_confirmation_status === 'Đồng ý')
                                                @if ($checkout->refund_status === 'Đã xử lý')
                                                    @if ($checkout->has_left == 0)
                                                        <form action="{{ route('checkouts.confirmLeft', $checkout->id) }}" method="POST" style="display: inline-block; margin-bottom: -10px;"
                                                            onsubmit="return confirm('Bạn có chắc chắn khách hàng đã rời đi?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-primary btn-sm shadow-sm"
                                                                title="Xác nhận khách hàng đã rời đi">
                                                                <i class="fas fa-sign-out-alt me-1"></i>Xác nhận rời đi
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="fst-italic">Đã hoàn thành</span>
                                                    @endif
                                                @endif
                                            @else
                                                @php
                                                    // Lấy giá trị từ configs với config_key là date_confirm_checkout
                                                    $dateConfirmCheckout = DB::table('configs')
                                                        ->where('config_key', 'date_confirm_checkout')
                                                        ->value('config_value'); // Mặc định là 7 nếu không tìm thấy config

                                                    $updatedAt = \Carbon\Carbon::parse($checkout->updated_at);
                                                    $daysDiff = $updatedAt->diffInDays(now());
                                                @endphp
                                                @if ($checkout->user_confirmation_status === 'Chưa xác nhận' && $daysDiff > $dateConfirmCheckout)
                                                    <form action="{{ route('checkouts.forceConfirmUser', $checkout->id) }}" method="POST" style="display: inline-block; margin-bottom: -10px;"
                                                        onsubmit="return confirm('Bạn có chắc chắn muốn xác nhận đồng ý thay cho người dùng?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-success btn-sm shadow-sm"
                                                            title="Xác nhận đồng ý thay người dùng">
                                                            <i class="fas fa-user-check me-1"></i>Xác nhận
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-warning text-white py-2 px-2">
                                                        <i class="fas fa-hourglass-half me-1"></i>Chờ xác nhận
                                                    </span>
                                                @endif
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
                                                <h5 class="modal-title" id="checkoutModalLabel{{ $checkout->id }}">Chi tiết Checkout</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <!-- Left Column - Basic Info -->
                                                    <div class="col-md-6">
                                                        <p><strong>Tên phòng:</strong> {{ $checkout->contract->room->name }}</p>
                                                        <p><strong>Ngày checkout:</strong>
                                                            {{ $checkout->check_out_date ? \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') : 'N/A' }}
                                                        </p>
                                                        <p><strong>Rời đi:</strong>
                                                            {{ $checkout->has_left == 0 ? 'Chưa rời đi' : 'Đã rời đi' }}
                                                        </p>
                                                        <p><strong>Trạng thái:</strong> {{ $checkout->inventory_status ?? 'N/A' }}</p>
                                                        <p><strong>Trạng thái người dùng:</strong>
                                                            <span class="badge bg-{{ $checkout->user_confirmation_status == 'Đồng ý' ? 'success' : ($checkout->user_confirmation_status == 'Từ chối' ? 'danger' : 'warning') }}">
                                                                {{ $checkout->user_confirmation_status ?? 'Chờ xác nhận' }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Số tiền khấu trừ:</strong>
                                                            {{ $checkout->deduction_amount ? number_format($checkout->deduction_amount, 0, ',', '.') : 'N/A' }} VNĐ
                                                        </p>
                                                        <p><strong>Tiền cọc:</strong>
                                                            {{ $checkout->contract->deposit_amount ? number_format($checkout->contract->deposit_amount, 0, ',', '.') : 'N/A' }} VNĐ
                                                        </p>
                                                        <p><strong>Số tiền hoàn lại:</strong>
                                                            {{ $checkout->final_refunded_amount ? number_format($checkout->final_refunded_amount, 0, ',', '.') : 'N/A' }} VNĐ
                                                        </p>
                                                        @if ($checkout->refund_status === 'Chờ xử lý' && $checkout->user_confirmation_status === 'Đồng ý' && isset($checkout->bank_info))
                                                            <p>
                                                                <span class="fst-italic text-danger"><i class="fas fa-info-circle me-2"></i>Vui lòng xác nhận và nhập mã tham chiếu!</span>
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <!-- Right Column - Rejection Reason or QR Code -->
                                                    <div class="col-md-6">
                                                        @if ($checkout->user_confirmation_status === 'Từ chối' && !empty($checkout->user_rejection_reason))
                                                            <div class="alert alert-danger">
                                                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Lý do từ chối:</h6>
                                                                <p class="mb-0">{{ $checkout->user_rejection_reason }}</p>
                                                            </div>
                                                        @elseif ($checkout->inventory_status === 'Đã kiểm kê' && $checkout->user_confirmation_status === 'Đồng ý')
                                                            <div class="qr-code-section text-center">
                                                                <h6><i class="fas fa-qrcode me-2"></i>Thông tin hoàn tiền</h6>
                                                                @if ($checkout->bank_info)
                                                                    <img src="https://qr.sepay.vn/img?acc={{ $checkout->bank_info['account_number'] }}&bank={{ $checkout->bank_info['bank_name'] }}&amount={{ $checkout->final_refunded_amount ?? 0 }}&des=Hoan tien phong {{ urlencode($checkout->contract->room->name ?? '') }}&template=compact"
                                                                        alt="QR Code" class="img-fluid mb-3 qr-code-img" style="max-width: 160px">
                                                                    <div class="bank-info text-start">
                                                                        <p><strong>Ngân hàng:</strong> {{ $checkout->bank_info['bank_name'] }}</p>
                                                                        <p><strong>Chủ tài khoản:</strong> {{ $checkout->bank_info['account_holder'] }}</p>
                                                                        <p><strong>Số tài khoản:</strong> {{ $checkout->bank_info['account_number'] }}</p>
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-info">
                                                                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                                                                        <p class="mb-0"><strong>Người dùng nhận tiền mặt</strong></p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            @if ($checkout->refund_status === 'Chờ xử lý' && $checkout->user_confirmation_status === 'Đồng ý')
                                                                @if ($checkout->bank_info)
                                                                    <!-- Có bank_info - cần nhập mã tham chiếu -->
                                                                    <button type="button" class="btn btn-success btn-sm shadow-sm"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#confirmCheckoutModal{{ $checkout->id }}"
                                                                        title="Xác nhận hoàn tiền">
                                                                        <i class="fas fa-check me-1"></i>Xác nhận hoàn tiền
                                                                    </button>
                                                                @else
                                                                    <!-- Không có bank_info - tiền mặt, xác nhận trực tiếp -->
                                                                    <form action="{{ route('checkouts.confirm', $checkout->id) }}" method="POST"
                                                                        style="display: inline-block;"
                                                                        onsubmit="return confirm('Bạn có chắc chắn đã hoàn tiền mặt cho khách hàng?')">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="transfer_amount" value="{{ $checkout->final_refunded_amount ?? 0 }}">
                                                                        <input type="hidden" name="reference_code" value="CASH_{{ $checkout->id }}_{{ now()->format('YmdHis') }}">
                                                                        <input type="hidden" name="payment_method" value="cash">
                                                                        <button type="submit" class="btn btn-success btn-sm shadow-sm"
                                                                                title="Xác nhận đã hoàn tiền mặt">
                                                                            <i class="fas fa-hand-holding-usd me-1"></i>Xác nhận hoàn tiền mặt
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        @else
                                                            <div class="text-center text-muted">
                                                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                                                <p>Thông tin hoàn tiền sẽ hiển thị khi đã kiểm kê và được người dùng đồng ý</p>
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
                                                                            <strong>Tên:</strong> {{ $item['item_name'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p class="mb-1">
                                                                            <strong>Tình trạng:</strong> {{ $item['item_condition'] ?? 'N/A' }}
                                                                        </p>
                                                                        <p class="mb-1">
                                                                            <strong>Chi phí:</strong>
                                                                            {{ $item['item_cost'] ? number_format($item['item_cost'], 0, ',', '.') : '0' }} VNĐ
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
                                                                        <img src="{{ asset($image) }}"
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
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
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
                                                <h5 class="modal-title" id="editModalLabel{{ $checkout->id }}">Kiểm kê</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="{{ route('checkouts.update', $checkout->id) }}"
                                                    method="POST" enctype="multipart/form-data"
                                                    id="checkoutForm{{ $checkout->id }}">
                                                    @csrf
                                                    @method('PUT')

                                                    <input type="hidden" name="status" value="Đã kiểm kê">
                                                    <p><strong>Tiền cọc:</strong>
                                                        {{ $checkout->contract->deposit_amount ? number_format($checkout->contract->deposit_amount, 0, ',', '.') : 'N/A' }}
                                                        VNĐ</p>

                                                    <!-- Inventory Details Section -->
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
                                                                    <small class="text-muted">Có thể chọn nhiều hình ảnh</small>
                                                                    @error('images.*')
                                                                        <div class="text-danger small">{{ $message }}</div>
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
                                                                                        <img src="{{ asset($image) }}"
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
                                                                        class="form-label">Tổng số tiền khấu trừ (VNĐ)</label>
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
                                                                        class="form-label">Số tiền hoàn trả cuối cùng (VNĐ)</label>
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

                                <!-- Modal for Confirm Refund -->
                                <div class="modal fade" id="confirmCheckoutModal{{ $checkout->id }}" tabindex="-1"
                                    aria-labelledby="confirmCheckoutModalLabel{{ $checkout->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="confirmCheckoutModalLabel{{ $checkout->id }}">Xác nhận hoàn tiền</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('checkouts.confirm', $checkout->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <p>Bạn có chắc chắn muốn xác nhận đã xử lý yêu cầu hoàn tiền này?
                                                        <span class="fst-italic">Vui lòng truy cập: <a class="text-danger" href="https://my.sepay.vn/transactions">Vào đây</a> để lấy mã tham chiếu!</span>
                                                    </p>
                                                    <div class="mb-3">
                                                        <label for="reference_code{{ $checkout->id }}" class="form-label">Mã tham chiếu</label>
                                                        <input type="text" class="form-control" id="reference_code{{ $checkout->id }}"
                                                            name="reference_code" required placeholder="Nhập mã tham chiếu">
                                                        <input type="hidden" name="transfer_amount" value="{{ $checkout->final_refunded_amount ?? 0 }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-success">Xác nhận</button>
                                                </div>
                                            </form>
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

        <!-- User Info Modal -->
        <div class="modal fade" id="userInfoModal" tabindex="-1" aria-labelledby="userInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="userInfoModalLabel">
                            <i class="fas fa-user-circle me-2"></i>Thông tin người thuê
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img id="modalUserAvatar" src="{{ asset('img/user.jpg') }}" alt="User Avatar" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-user me-2"></i>Họ và tên:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserName">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-envelope me-2"></i>Email:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserEmail">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-phone me-2"></i>Số điện thoại:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserPhone">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserAddress">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-venus-mars me-2"></i>Giới tính:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserGender">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-birthday-cake me-2"></i>Ngày sinh:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserBirthdate">-</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-calendar-plus me-2"></i>Ngày tạo tài khoản:
                                    </label>
                                    <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserCreated">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .contract-id-clickable,
        .user-name-clickable {
            cursor: pointer;
            color: #007bff;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .table-row:hover {
            background-color: #f8f9fa;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
        }

        .form-control-plaintext {
            font-weight: 500;
            color: #495057 !important;
        }
    </style>

    <script>
        // Truyền dữ liệu inventory từ server xuống JavaScript
        window.existingInventoryData = {
            @foreach ($checkouts as $checkout)
                '{{ $checkout->id }}': {!! json_encode($checkout->inventory_details ?? []) !!},
            @endforeach
        };

        console.log('Existing inventory data loaded:', window.existingInventoryData);

        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to table rows
            const tableRows = document.querySelectorAll('.table-row');
            tableRows.forEach((row, index) => {
                // row.style.animationDelay = `${index * 0.1}s`;
                // row.classList.add('animate__animated', 'animate__fadeInUp');
            });

            // Handle user name click to show popup
            const userNameElements = document.querySelectorAll('.user-name-clickable');
            userNameElements.forEach(element => {
                element.addEventListener('click', function() {
                    const userName = this.getAttribute('data-user-name');
                    const userEmail = this.getAttribute('data-user-email');
                    const userPhone = this.getAttribute('data-user-phone');
                    const userAddress = this.getAttribute('data-user-address');
                    const userGender = this.getAttribute('data-user-gender');
                    const userBirthdate = this.getAttribute('data-user-birthdate');
                    const userCreated = this.getAttribute('data-user-created');
                    const userAvatar = this.getAttribute('data-user-avatar');

                    // Update modal content
                    document.getElementById('modalUserName').textContent = userName || 'N/A';
                    document.getElementById('modalUserEmail').textContent = userEmail || 'N/A';
                    document.getElementById('modalUserPhone').textContent = userPhone || 'N/A';
                    document.getElementById('modalUserAddress').textContent = userAddress || 'N/A';
                    document.getElementById('modalUserGender').textContent = userGender || 'N/A';
                    document.getElementById('modalUserAvatar').src = userAvatar || '{{ asset('img/user.jpg') }}';

                    // Format birthdate if available
                    if (userBirthdate && userBirthdate !== 'N/A') {
                        const date = new Date(userBirthdate);
                        const formattedBirthdate = date.toLocaleDateString('vi-VN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        document.getElementById('modalUserBirthdate').textContent = formattedBirthdate;
                    } else {
                        document.getElementById('modalUserBirthdate').textContent = 'N/A';
                    }

                    // Format created date if available
                    if (userCreated && userCreated !== 'N/A') {
                        const date = new Date(userCreated);
                        const formattedDate = date.toLocaleDateString('vi-VN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        document.getElementById('modalUserCreated').textContent = formattedDate;
                    } else {
                        document.getElementById('modalUserCreated').textContent = 'N/A';
                    }

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('userInfoModal'));
                    modal.show();
                });
            });
        });
    </script>
    <script src="{{ asset('js/checkout.js') }}"></script>
@endsection
