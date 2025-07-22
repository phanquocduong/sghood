@extends('layouts.app')

@section('title', 'Yêu cầu hoàn tiền')

@section('content')
    <div class="container-fluid py-5 px-4">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4"
                style="background: linear-gradient(90deg, #007bff, #00c6ff);">
                <div class="d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-3 shadow-sm"
                        style="transition: all 0.3s;" title="Quay lại dashboard">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-money-bill-wave me-2"></i>Yêu cầu hoàn tiền
                        <span class="badge bg-light text-primary ms-2">{{ $refunds->total() ?? 0 }} yêu cầu</span>
                    </h5>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-money-bill-wave me-1"></i>Yêu cầu hoàn tiền
                        </li>
                    </ol>
                </nav>

                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('refunds.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control shadow-sm" name="querySearch"
                                    placeholder="Tìm kiếm theo tên người dùng hoặc phòng..." value="{{ $querySearch }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ xử lý" {{ $status == 'Chờ xử lý' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="Đã xử lý" {{ $status == 'Đã xử lý' ? 'selected' : '' }}>Đã xử lý</option>
                                <option value="Hủy bỏ" {{ $status == 'Hủy bỏ' ? 'selected' : '' }}>Hủy bỏ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="sort">
                                <option value="desc" {{ ($sort ?? 'desc') == 'desc' ? 'selected' : '' }}>Mới nhất
                                </option>
                                <option value="asc" {{ ($sort ?? 'desc') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary shadow-sm w-100">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Refunds Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden"
                        style="text-align: center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;" class="text-center">STT</th>
                                <th scope="col" style="width: 15%;">Tên phòng</th>
                                <th scope="col" style="width: 20%;">Tên người dùng</th>
                                <th scope="col" style="width: 15%;">Số tiền</th>
                                <th scope="col" style="width: 15%;">Trạng thái</th>
                                <th scope="col" style="width: 23%;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($refunds as $refund)
                                <tr class="table-row">
                                    <td class="text-center">{{ $refunds->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-door-open text-primary me-2"></i>
                                            <span
                                                class="fw-medium">{{ $refund->checkout->contract->room->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span
                                                class="fw-medium">{{ $refund->bank_info['account_holder'] ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">
                                            {{ number_format($refund->checkout->final_refunded_amount ?? 0, 0, ',', '.') }}
                                            VNĐ
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $status = $refund->status;
                                            $badgeClass = match ($status) {
                                                'Chờ xử lý' => 'warning',
                                                'Đã xử lý' => 'success',
                                                'Hủy bỏ' => 'danger',
                                                default => 'light',
                                            };
                                            $iconClass = match ($status) {
                                                'Chờ xử lý' => 'fas fa-clock',
                                                'Đã xử lý' => 'fas fa-check-circle',
                                                'Hủy bỏ' => 'fas fa-times-circle',
                                                default => 'fas fa-info-circle',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }} py-2 px-3">
                                            <i class="{{ $iconClass }} me-1" style="font-size: 8px;"></i>
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <!-- Thay thế phần nút xác nhận trong table -->
                                    <td class="text-center">
                                        @if ($refund->checkout->user_confirmation_status === 'Đồng ý')
                                            <button type="button" class="btn btn-info btn-sm shadow-sm me-2"
                                                data-bs-toggle="modal" data-bs-target="#qrModal{{ $refund->id }}"
                                                title="Xem chi tiết">
                                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                                            </button>
                                            @if ($refund->status === 'Chờ xử lý')
                                                <button type="button" class="btn btn-success btn-sm shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmRefundModal{{ $refund->id }}"
                                                    title="Xác nhận đã xử lý">
                                                    <i class="fas fa-check me-1"></i>Xác nhận
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge bg-warning text-white py-2 px-3">
                                                <i class="fas fa-hourglass-half me-1"></i>Chờ xác nhận bởi người dùng
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Modal để nhập mã tham chiếu -->
                                    <div class="modal fade" id="confirmRefundModal{{ $refund->id }}" tabindex="-1"
                                        aria-labelledby="confirmRefundModalLabel{{ $refund->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="confirmRefundModalLabel{{ $refund->id }}">Xác nhận hoàn tiền
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('refunds.confirm', $refund->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <p>Bạn có chắc chắn muốn xác nhận đã xử lý yêu cầu hoàn tiền này?
                                                            <span class="fst-italic">Vui lòng truy cập: <a class="text-danger" href="https://my.sepay.vn/transactions">Vào đây</a> để lấy mã tham chiếu!</span>
                                                        </p>
                                                        <div class="mb-3">
                                                            <label for="reference_code{{ $refund->id }}"
                                                                class="form-label">Mã tham chiếu</label>
                                                            <input type="text" class="form-control"
                                                                id="reference_code{{ $refund->id }}"
                                                                name="reference_code" required
                                                                placeholder="Nhập mã tham chiếu">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Hủy</button>
                                                        <button type="submit" class="btn btn-success">Xác nhận</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </tr>

                                <!-- Modal for QR Code -->
                                <div class="modal fade" id="qrModal{{ $refund->id }}" tabindex="-1"
                                    aria-labelledby="qrModalLabel{{ $refund->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="qrModalLabel{{ $refund->id }}">Thông tin
                                                    hoàn tiền</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="https://qr.sepay.vn/img?acc={{ $refund->bank_info['account_number'] ?? '31214717' }}&bank={{ $refund->bank_info['bank_name'] ?? 'ACB' }}&amount={{ $refund->checkout->final_refunded_amount ?? 0 }}&des=Hoan tien phong {{ urlencode($refund->checkout->contract->room->name ?? '') }}&template=compact"
                                                    alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                                                <div class="text-start">
                                                    <p><strong>Ngân hàng:</strong>
                                                        {{ $refund->bank_info['bank_name'] ?? 'ACB' }}</p>
                                                    <p><strong>Chủ tài khoản:</strong>
                                                        {{ $refund->bank_info['account_holder'] ?? 'PHAN QUOC DUONG' }}</p>
                                                    <p><strong>Số tài khoản:</strong>
                                                        {{ $refund->bank_info['account_number'] ?? '31214717' }}</p>
                                                </div>
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
                                        <i class="fas fa-money-bill-wave fa-3x mb-3 opacity-50"></i>
                                        <br>
                                        <span class="fs-5">Không có yêu cầu hoàn tiền nào.</span>
                                        <br>
                                        <small>Hãy thử thay đổi bộ lọc tìm kiếm.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($refunds->hasPages())
                    <div class="mt-4">
                        {{ $refunds->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .breadcrumb {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.85rem;
            border-radius: 15px;
            font-weight: 500;
        }

        .form-select:focus,
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table-row:hover {
            background-color: #f8f9fa;
        }

        .modal-content {
            border-radius: 10px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to table rows
            const tableRows = document.querySelectorAll('.table-row');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                row.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });
    </script>
@endsection
