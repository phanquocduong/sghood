@extends('layouts.app')

@section('title', 'Hợp đồng thuê phòng')

@section('content')
    <div class="container-fluid py-5 px-4">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- DIV CHA 1: PHẦN HỢP ĐỒNG -->
        {!! $contract->content ?? '' !!}
        <!-- DIV CHA 2: PHẦN QUẢN LÝ TRẠNG THÁI HỢP ĐỒNG -->
        <div class="contract-management-wrapper">
            <div class="card border-0 bg-light rounded-3 p-4">
                <h5 class="text-dark mb-4">
                    <i class="fas fa-edit me-2"></i>QUẢN LÝ TRẠNG THÁI HỢP ĐỒNG
                </h5>

                <!-- Current Status Display -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-white shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Trạng thái hiện tại
                                </h6>
                                @php
                                    $currentStatus = $contract->status ?? '';
                                    $badgeClass = match ($currentStatus) {
                                        'Chờ xác nhận' => 'warning',
                                        'Đã ký' => 'success',
                                        'Đã hủy' => 'danger',
                                        'Hết hạn' => 'secondary',
                                        default => 'info'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} py-2 px-3 fs-6">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    {{ $currentStatus }}
                                </span>
                                <p class="text-muted mt-2 mb-0">
                                    <small>
                                        <i class="fas fa-clock me-1"></i>
                                        Cập nhật lần cuối: {{ $contract->updated_at ? $contract->updated_at->format('d/m/Y H:i') : 'Chưa cập nhật' }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-white shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-calendar-check me-2"></i>Thông tin thời hạn
                                </h6>
                                <p class="mb-1">
                                    <strong>Ngày ký:</strong>
                                    <span class="text-muted">Chưa ký</span>
                                </p>
                                <p class="mb-0">
                                    <strong>Ngày hết hạn:</strong>
                                    <span class="text-primary">{{ $contract->booking->end_date ? \Carbon\Carbon::parse($contract->booking->end_date)->format('d/m/Y H:i') : 'Chưa cập nhật' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Update Form -->
                @if($currentStatus !== 'Đã hủy' && $currentStatus !== 'Hết hạn')
                <div class="card border-0 bg-white shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-sync-alt me-2"></i>Cập nhật trạng thái
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('contracts.updateStatus', $contract->id) }}" method="POST" onsubmit="return confirmStatusChange()">
                            @csrf
                            @method('PATCH')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-tasks me-1"></i>Trạng thái mới
                                    </label>
                                    <select class="form-select shadow-sm" name="status" id="status" required>
                                        <option value="">-- Chọn trạng thái --</option>
                                        @if($currentStatus === 'Chờ duyệt')
                                            <option value="Chờ chỉnh sửa">Chờ chỉnh sửa</option>
                                            <option value="Chờ ký">Chờ ký</option>
                                            <option value="Huỷ bỏ">Huỷ bỏ</option>
                                        @elseif($currentStatus === 'Chờ ký')
                                            <option value="Hoạt động">Đã ký</option>
                                            <option value="Huỷ bỏ">Huỷ bỏ</option>
                                        @elseif($currentStatus === 'Hoạt động')
                                            <option value="Kết thúc">Kết thúc hợp đồng</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                                </a>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-save me-1"></i>Cập nhật trạng thái
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Hợp đồng này không thể thay đổi trạng thái</strong>
                    <br>
                    <small>Hợp đồng đã {{ strtolower($currentStatus) }} và không thể cập nhật thêm.</small>
                </div>
                <div class="text-center">
                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

<style>
    input{
        pointer-events: none;
        background-color: #f8f9fa;
        user-select: none;
    }
    .form-control{
        background-color: rgb(243, 246, 249);
    }

</style>

<script>
function confirmStatusChange() {
    const status = document.getElementById('status').value;
    const note = document.getElementById('note')?.value || '';

    if (!status) {
        alert('Vui lòng chọn trạng thái mới!');
        return false;
    }

    let message = `Bạn có chắc muốn thay đổi trạng thái hợp đồng thành "${status}"?`;

    return confirm(message);
}
</script>
@endsection