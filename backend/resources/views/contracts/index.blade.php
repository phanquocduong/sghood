@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Quản lý hợp đồng')

@section('content')
<div class="container-fluid py-5 px-4">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4" style="background: linear-gradient(90deg, #007bff, #00c6ff);">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-3 shadow-sm" style="transition: all 0.3s;" title="Quay lại dashboard">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-file-contract me-2"></i>Quản lý hợp đồng
                    <span class="badge bg-light text-primary ms-2">{{ $contracts->total() ?? 0 }} hợp đồng</span>
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
                        <i class="fas fa-file-contract me-1"></i>Quản lý hợp đồng
                    </li>
                </ol>
            </nav>

            <!-- Filter Form -->
            <div class="mb-4">
                <form action="{{ route('contracts.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="querySearch"
                                placeholder="Tìm kiếm theo tên người thuê hoặc phòng..."
                                value="{{ $querySearch }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shadow-sm" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Chờ xác nhận" {{ $status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="Chờ duyệt" {{ $status == 'Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="Chờ chỉnh sửa" {{ $status == 'Chờ chỉnh sửa' ? 'selected' : '' }}>Chờ chỉnh sửa</option>
                            <option value="Chờ ký" {{ $status == 'Chờ ký' ? 'selected' : '' }}>Chờ ký</option>
                            <option value="Hoạt động" {{ $status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Kết thúc" {{ $status == 'Kết thúc' ? 'selected' : '' }}>Kết thúc</option>
                            <option value="Huỷ bỏ" {{ $status == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="perPage">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10/trang</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25/trang</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50/trang</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary shadow-sm w-100">
                            <i class="fas fa-search me-1"></i>Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contracts Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden" style="text-align: center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;" class="text-center">STT</th>
                            <th scope="col" style="width: 15%;">Bên A (Chủ nhà)</th>
                            <th scope="col" style="width: 15%;">Bên B (Người thuê)</th>
                            <th scope="col" style="width: 15%;">Tên phòng</th>
                            <th scope="col" style="width: 12%;" class="text-center">Ngày bắt đầu</th>
                            <th scope="col" style="width: 12%;" class="text-center">Ngày kết thúc</th>
                            <th scope="col" style="width: 10%;" class="text-center">Giá thuê</th>
                            <th scope="col" style="width: 12%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 14%;" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contractItem)
                            <tr class="table-row">
                                <td class="text-center">{{ $contracts->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="avatar-circle bg-primary text-white me-2" style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                            SG
                                        </div>
                                        <span class="fw-medium text-primary">SGHood</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="fw-medium">{{ $contractItem->user->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-door-open text-primary me-2"></i>
                                        <span class="fw-medium">{{ $contractItem->room->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $contractItem->start_date
                                            ? \Carbon\Carbon::parse($contractItem->start_date)->format('d/m/Y')
                                            : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $contractItem->end_date
                                            ? \Carbon\Carbon::parse($contractItem->end_date)->format('d/m/Y')
                                            : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-success">
                                        {{ number_format($contractItem->rental_price ?? 0, 0, ',', '.') }} VNĐ
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $calculatedStatus = $contractItem->calculated_status ?? $contractItem->status;
                                        $badgeClass = match ($calculatedStatus) {
                                            'Chờ xác nhận' => 'primary',
                                            'Chờ duyệt' => 'warning',
                                            'Chờ chỉnh sửa' => 'danger',
                                            'Chờ ký' => 'info',
                                            'Hoạt động' => 'success',
                                            'Kết thúc' => 'secondary',
                                            'Huỷ bỏ' => 'dark',
                                            default => 'light'
                                        };
                                        $iconClass = match ($calculatedStatus) {
                                            'Chờ xác nhận' => 'fas fa-clock',
                                            'Chờ duyệt' => 'fas fa-eye',
                                            'Chờ chỉnh sửa' => 'fas fa-edit',
                                            'Chờ ký' => 'fas fa-pen',
                                            'Hoạt động' => 'fas fa-check-circle',
                                            'Kết thúc' => 'fas fa-flag-checkered',
                                            'Huỷ bỏ' => 'fas fa-times-circle',
                                            default => 'fas fa-info-circle'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} py-2 px-3">
                                        <i class="{{ $iconClass }} me-1" style="font-size: 8px;"></i>
                                        {{ $calculatedStatus }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('contracts.show', $contractItem->id) }}"
                                           class="btn btn-info btn-sm shadow-sm"
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </a>
                                        @if($contractItem->file && Storage::disk('private')->exists($contractItem->file))
                                            <a href="{{ route('contracts.download', $contractItem->id) }}"
                                            class="btn btn-outline-primary btn-sm shadow-sm"
                                            title="Tải xuống PDF">
                                                <i class="fas fa-download me-1"></i>PDF
                                            </a>
                                        @else
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm shadow-sm"
                                                    disabled
                                                    title="Không có file">
                                                <i class="fas fa-file-times me-1"></i>N/A
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-file-contract fa-3x mb-3 opacity-50"></i>
                                    <br>
                                    <span class="fs-5">Không có hợp đồng nào.</span>
                                    <br>
                                    <small>Hãy thử thay đổi bộ lọc tìm kiếm hoặc tạo hợp đồng mới.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($contracts->hasPages())
                <div class="mt-4">
                    {{ $contracts->appends(request()->query())->links('pagination::bootstrap-5') }}
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

.breadcrumb-item + .breadcrumb-item::before {
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

.avatar-circle {
    font-size: 14px;
}

.btn-group .btn {
    margin: 0 1px;
}
</style>

<script>
// Add smooth scroll effect when clicking navigation links
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
