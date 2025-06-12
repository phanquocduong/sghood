@extends('layouts.app')

@section('title', 'Hợp đồng thuê phòng')

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

        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-white bg-dark d-flex justify-content-center rounded-top-4">
                <h4 class="mb-0" style="color: #ffffff">HỢP ĐỒNG THUÊ PHÒNG TRỌ</h4>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <p class="fw-bold">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</p>
                    <p class="fw-bold">Độc lập - Tự do - Hạnh phúc</p>
                    <p class="mb-0">
                    <small>
                        <span class="text-primary">
                            TP.HCM, ngày {{ $contract->created_at ? $contract->created_at->format('d \t\h\á\n\g m \n\ă\m Y, H:i') : date('d \t\h\á\n\g m \n\ă\m Y, H:i') }}
                        </span>
                    </small>
                    </p>
                </div>

                <h5 class="mt-4">BÊN THUÊ PHÒNG TRỌ (BÊN A)</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ và tên:</label>
                        <p class="border p-2 rounded">Nguyễn Văn A</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CMND/CCCD:</label>
                        <p class="border p-2 rounded">123456789</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh:</label>
                        <p class="border p-2 rounded">01/01/1990</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Địa chỉ thường trú:</label>
                        <p class="border p-2 rounded">TP Hồ Chí Minh</p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại:</label>
                        <p class="border p-2 rounded">0901234567</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email:</label>
                        <p class="border p-2 rounded">a@example.com</p>
                    </div>
                </div>

                <h5 class="mt-4">BÊN CHO THUÊ PHÒNG TRỌ (BÊN B)</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ và tên:</label>
                        <p class="border p-2 rounded">{{  $contract->user->name ?? '' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CMND/CCCD:</label>
                        <p class="border p-2 rounded">{{  $contract->user->identity_document ?? '' }}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh:</label>
                        <p class="border p-2 rounded">{{ $contract->user->birthdate ? \Carbon\Carbon::parse($contract->user->birth_date)->format('d/m/Y') : '15/03/1985' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Địa chỉ thường trú:</label>
                        <p class="border p-2 rounded">{{  $contract->user->address ?? '' }}</p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại:</label>
                        <p class="border p-2 rounded">{{  $contract->user->phone ?? '' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email:</label>
                        <p class="border p-2 rounded">{{  $contract->user->email ?? '' }}</p>
                    </div>
                </div>

                <h5 class="mt-4">NỘI DUNG HỢP ĐỒNG</h5>
                <ol class="list-group list-group-numbered mb-4">
                    <li class="list-group-item">
                        <strong>Nội dung thuê phòng trọ:</strong>
                        Bên A thuê của bên B phòng trọ số <span class="text-primary">{{  $contract->room->name ?? '' }}</span>
                        tại địa chỉ <span class="text-primary">{{  $contract->room->motel->name ?? '' }}</span>.
                        Diện tích: <span class="text-primary">{{  $contract->room->area ?? '' }}</span> m².
                    </li>
                    <li class="list-group-item">
                        <strong>Trách nhiệm bên A:</strong>
                        <ul class="list-group list-group-flush">
                            <li>Đảm bảo thanh toán đầy đủ tiền thuê phòng trọ đúng hạn.</li>
                            <li>Giữ gìn vệ sinh, không làm hư hỏng tài sản của bên B.</li>
                            <li>Chấp hành các quy định về an ninh, trật tự tại khu trọ.</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <strong>Trách nhiệm bên B:</strong>
                        <ul class="list-group list-group-flush">
                            <li>Cung cấp phòng trọ sạch sẽ, đầy đủ tiện nghi theo thỏa thuận.</li>
                            <li>Hỗ trợ bên A trong trường hợp có sự cố liên quan đến phòng trọ (nếu có).</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <strong>Điều khoản thanh toán:</strong>
                        <ul class="list-group list-group-flush">
                            <li>Đặt cọc: <span class="text-primary">{{  number_format($contract->room->price) ?? '' }}</span> VNĐ
                            <li>Tiền thuê phòng: <span class="text-primary">{{  number_format($contract->room->price) ?? '' }}</span> VNĐ/tháng
                                thanh toán vào ngày <span class="text-primary">1-10</span> hàng tháng.</li>
                            <li>Thời hạn hợp đồng: Từ ngày <span class="text-primary">{{ $contract->booking->start_date ? date('d/m/Y', strtotime($contract->booking->start_date)) : '' }}</span>
                                đến ngày <span class="text-primary">{{ $contract->booking->end_date ? date('d/m/Y', strtotime($contract->booking->end_date)) : '' }}</span>.</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <strong>Các phí khác:</strong>
                        <ul class="list-group list-group-flush">
                            <li>Tiền điện: <span class="text-primary">{{  number_format($contract->room->motel->electricity_fee) ?? '' }}</span>VNĐ/Kg</li>
                            <li>Tiền nước: <span class="text-primary">{{  number_format($contract->room->motel->water_fee) ?? '' }}</span>VNĐ/Khối</li>
                            <li>Tiền gửi xe: <span class="text-primary">{{  number_format($contract->room->motel->parking_fee) ?? '' }}</span> VNĐ/tháng</li>
                            <li>Tiền rác: <span class="text-primary">{{  number_format($contract->room->motel->junk_fee) ?? '' }}</span> VNĐ/tháng</li>
                            <li>Tiền internet: <span class="text-primary">{{  number_format($contract->room->motel->internet_fee) ?? '' }}</span> VNĐ/tháng</li>
                            <li>Phí dịch vụ khác (nếu có): <span class="text-primary">{{  number_format($contract->room->motel->service_fee) ?? '' }}</span> VNĐ/tháng</li>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <strong>Điều khoản khác:</strong>
                        Hai bên có thể thỏa thuận thêm (nếu có):
                        <p class="border p-2 rounded">Không có</p>
                    </li>
                </ol>

                <h5 class="mt-4">CHỮ KÝ XÁC NHẬN</h5>
                <div class="d-flex justify-content-between mb-4">
                    <div class="text-center">
                        <p class="fw-bold">BÊN A</p>
                        <p>(Ký, ghi rõ họ tên)</p>
                        <p class="border p-2 rounded">Nguyễn Văn A</p>
                    </div>
                    <div class="text-center">
                        <p class="fw-bold">BÊN B</p>
                        <p>(Ký, ghi rõ họ tên)</p>
                        <p class="border p-2 rounded">{{  $contract->user->name ?? '' }}</p>
                    </div>
                </div>

                <!-- Contract Status Management Section -->
                <hr class="my-5">
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
                                        $currentStatus = $contract->status ?? ''; // This should come from $contract->status
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
                                        <span class="text-primary">31/12/2025</span>
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
                            <form action="{{ route('contracts.updateStatus', $contract->id) }}" method="POST"  onsubmit="return confirmStatusChange()">
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
                                                <option value="Chờ chỉnh sửa">- Chờ chỉnh sửa</option>
                                                <option value="Chờ ký">- Chờ ký</option>
                                                <option value="Hoạt động">✓ Đã ký</option>
                                                <option value="Hủy bỏ">✗ Hủy bỏ</option>
                                            @elseif($currentStatus === 'Hoạt động')
                                                <option value="Kết thúc">✗ Kết thúc hợp đồng</option>
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
    </div>
{{-- @endsection --}}

<style>
    .list-group-item {
        border: none;
        padding: 0.5rem 1rem;
    }
    .list-group-item strong {
        font-size: 1.1rem;
    }
    .list-group-item li {
        margin-left: 30px
    }
    .border {
        min-height: 40px;
    }
    .form-select:focus,
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
    }
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .card {
        transition: all 0.3s ease;
    }
    .badge {
        font-size: 0.85rem;
        border-radius: 15px;
        font-weight: 500;
    }
</style>

<script>
function confirmStatusChange() {
    const status = document.getElementById('status').value;
    const note = document.getElementById('note').value;

    if (!status) {
        alert('Vui lòng chọn trạng thái mới!');
        return false;
    }

    let message = `Bạn có chắc muốn thay đổi trạng thái hợp đồng thành "${status}"?`;
    if (note.trim()) {
        message += `\n\nGhi chú: ${note}`;
    }

    return confirm(message);
}
</script>
@endsection
