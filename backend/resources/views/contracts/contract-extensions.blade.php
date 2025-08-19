@extends('layouts.app')

@section('title', 'Quản lý gia hạn hợp đồng')

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

        <div class="card shadow-lg border-0 rounded-4" style="overflow: hidden;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4"
                style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
                <div class="d-flex align-items-center">
                    <a href="{{ route('contracts.index') }}" class="btn btn-light btn-sm me-3 shadow-sm action-icon"
                        style="transition: all 0.3s; border-radius: 8px;" title="Quay lại danh sách hợp đồng">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span class="d-none d-sm-inline ms-1">Quay lại</span>
                    </a>
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2"></i>Quản lý gia hạn hợp đồng
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $contractExtensions->total() }} gia hạn</span>
                    </h5>
                </div>
            </div>

            <div class="card-body p-4 bg-light">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb bg-white shadow-sm rounded-3 p-3">
                        <li class="breadcrumb-item">
                            <a href="{{ route('contracts.index') }}" class="text-decoration-none text-primary">
                                <i class="fas fa-file-contract me-1"></i>Quản lý hợp đồng
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-history me-1"></i>Quản lý gia hạn hợp đồng
                        </li>
                    </ol>
                </nav>

                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('contracts.contract-extensions') }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" class="form-control border-0" name="querySearch"
                                    placeholder="Tìm kiếm theo tên phòng..." value="{{ $querySearch }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm rounded-3" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ duyệt" {{ $status == 'Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="Hoạt động" {{ $status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="Từ chối" {{ $status == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                                <option value="Huỷ bỏ" {{ $status == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm rounded-3" name="sort">
                                <option value="desc" {{ $sort == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="asc" {{ $sort == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary shadow-sm w-100 rounded-3">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contract Extensions Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden"
                        style="text-align: center; background: #fff;">
                        <thead class="table-dark" style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
                            <tr>
                                <th scope="col" style="width: 5%;" class="text-center">STT</th>
                                <th scope="col" style="width: 15%;">Mã HD</th>
                                <th scope="col" style="width: 15%;">Ngày kết thúc mới</th>
                                <th scope="col" style="width: 15%;">Giá thuê mới</th>
                                <th scope="col" style="width: 15%;">Chi tiết</th>
                                <th scope="col" style="width: 20%;">Lý do từ chối</th>
                                <th scope="col" style="width: 15%;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contractExtensions as $extension)
                                <tr class="table-row">
                                    <td class="text-center">{{ $contractExtensions->firstItem() + $loop->index }}</td>
                                    <td>
                                        <a href="{{ route('contracts.show', $extension->contract->id) }}"
                                            target="_blank"
                                            class="contract-id-clickable text-primary text-decoration-none fw-bold"
                                            title="Xem chi tiết hợp đồng">
                                            {{ 'HD' . $extension->contract->id }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($extension->new_end_date)->format('d/m/Y') }}</td>
                                    <td>{{ number_format($extension->new_rental_price, 0, ',', '.') }} VNĐ</td>
                                    <td>
                                        <a href="#" class="content-popup text-primary text-decoration-underline fw-bold"
                                            data-bs-toggle="modal" data-bs-target="#contentModal{{ $extension->id }}">
                                            Xem chi tiết
                                        </a>
                                    </td>
                                    <td>{{ $extension->rejection_reason ?? 'Không có lí do từ chối' }}</td>
                                    <td>
                                        @if ($extension->status == 'Chờ duyệt')
                                            <form
                                                action="{{ route('contracts.contract_extensions.update_status', $extension->id) }}"
                                                method="POST" class="status-form">
                                                @csrf
                                                <select class="form-select form-select-sm status-select shadow-sm rounded-3" name="status"
                                                    onchange="handleStatusChange(this, {{ $extension->id }})">
                                                    <option value="Chờ duyệt" selected>Chờ duyệt</option>
                                                    <option value="Hoạt động">Chấp nhận</option>
                                                    <option value="Từ chối">Từ chối</option>
                                                </select>
                                            </form>
                                        @else
                                            @php
                                                $badgeClass = match ($extension->status) {
                                                    'Hoạt động' => 'success',
                                                    'Từ chối' => 'danger',
                                                    'Chờ duyệt' => 'warning',
                                                    'Huỷ bỏ' => 'danger',
                                                    default => 'light',
                                                };
                                                $statusText = match ($extension->status) {
                                                    'Hoạt động' => 'Hoạt động',
                                                    'Từ chối' => 'Từ chối',
                                                    'Chờ duyệt' => 'Chờ duyệt',
                                                    'Huỷ bỏ' => 'Huỷ bỏ',
                                                    default => $extension->status,
                                                };
                                            @endphp
                                            <span
                                                class="badge bg-{{ $badgeClass }} py-2 px-3 rounded-pill">{{ $statusText }}</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Content Modal -->
                                <div class="modal fade" id="contentModal{{ $extension->id }}" tabindex="-1"
                                    aria-labelledby="contentModalLabel{{ $extension->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content rounded-4 shadow-lg">
                                            <div class="modal-header bg-primary text-white rounded-top-4">
                                                <h5 class="modal-title" id="contentModalLabel{{ $extension->id }}">Chi
                                                    tiết nội dung gia hạn</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                {!! $extension->content !!}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary rounded-3"
                                                    data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rejection Reason Modal -->
                                <div class="modal fade" id="rejectionModal{{ $extension->id }}" tabindex="-1"
                                    aria-labelledby="rejectionModalLabel{{ $extension->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 shadow-lg">
                                            <div class="modal-header bg-danger text-white rounded-top-4">
                                                <h5 class="modal-title text-white"
                                                    id="rejectionModalLabel{{ $extension->id }}">Lý do từ chối</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form
                                                action="{{ route('contracts.contract_extensions.update_status', $extension->id) }}"
                                                method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="status" value="Từ chối">
                                                    <div class="mb-3">
                                                        <label for="rejection_reason_{{ $extension->id }}"
                                                            class="form-label fw-bold">Hãy nhập lý do từ chối</label>
                                                        <textarea class="form-control shadow-sm rounded-3" id="rejection_reason_{{ $extension->id }}" name="rejection_reason" rows="4"
                                                            required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary rounded-3"
                                                        data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-3">Xác nhận từ
                                                        chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                                        <br>
                                        <span class="fs-5">Không có gia hạn hợp đồng nào.</span>
                                        <br>
                                        <small>Hãy thử thay đổi bộ lọc tìm kiếm.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($contractExtensions->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $contractExtensions->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .breadcrumb {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        .table td,
        .table th {
            vertical-align: middle;
            transition: background-color 0.3s ease;
        }

        .badge {
            font-size: 0.9rem;
            border-radius: 20px;
            font-weight: 500;
            padding: 8px 16px;
        }

        .form-select:focus,
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
        }

        .form-select-sm {
            width: 140px;
            margin: 0 auto;
            border-radius: 8px;
        }

        .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .btn-primary {
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            border: none;
        }

        @media (max-width: 576px) {
            .action-icon {
                padding: 8px;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .action-icon i {
                margin: 0 !important;
                font-size: 16px;
            }

            .card-header .btn {
                font-size: 14px;
                padding: 8px;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .table th,
            .table td {
                font-size: 0.9rem;
            }

            .form-select-sm {
                width: 100%;
            }
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

            // Add animation to modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('show.bs.modal', function () {
                    this.querySelector('.modal-content').classList.add('animate__animated', 'animate__zoomIn');
                });
                modal.addEventListener('hide.bs.modal', function () {
                    this.querySelector('.modal-content').classList.remove('animate__zoomIn');
                    this.querySelector('.modal-content').classList.add('animate__zoomOut');
                });
            });
        });

        function handleStatusChange(select, extensionId) {
            const status = select.value;

            if (status === 'Từ chối') {
                select.value = 'Chờ duyệt';
                const modal = new bootstrap.Modal(document.getElementById('rejectionModal' + extensionId));
                modal.show();
            } else if (status === 'Hoạt động') {
                if (confirm('Bạn có chắc chắn muốn chấp nhận yêu cầu gia hạn này?')) {
                    select.closest('form').submit();
                } else {
                    select.value = 'Chờ duyệt';
                }
            } else {
                select.closest('form').submit();
            }
        }
    </script>
@endsection
