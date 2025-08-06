@extends('layouts.app')

@section('title', 'Quản lý đặt phòng')

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
                    <i class="fas fa-calendar-alt me-2"></i>Quản lý đặt phòng
                    <span class="badge bg-light text-primary ms-2">{{ $booking->total() ?? 0 }} đặt phòng</span>
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
                        <i class="fas fa-calendar-alt me-1"></i>Quản lý đặt phòng
                    </li>
                </ol>
            </nav>

            <!-- Filter Form -->
            <div class="mb-4">
                <form action="{{ route('bookings.index') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="query"
                                placeholder="Tìm kiếm theo tên người thuê hoặc phòng..."
                                value="{{ $querySearch }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Chờ xác nhận" {{ $status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="Chấp nhận" {{ $status == 'Chấp nhận' ? 'selected' : '' }}>Chấp nhận</option>
                            <option value="Từ chối" {{ $status == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                            <option value="Hủy" {{ $status == 'Hủy' ? 'selected' : '' }}>Hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="sortOption">
                            <option value="">Sắp xếp theo</option>
                            <option value="created_at_desc" {{ $sortOption == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="created_at_asc" {{ $sortOption == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary shadow-sm w-100">
                            <i class="fas fa-search me-1"></i>Lọc
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary shadow-sm w-100" title="Làm mới">
                            <i class="fas fa-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden" style="text-align: center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;" class="text-center">STT</th>
                            <th scope="col" style="width: 12%;">Tên phòng</th>
                            <th scope="col" style="width: 20%;">Người thuê</th>
                            <th scope="col" style="width: 12%;" class="text-center">Thời gian</th>
                            <th scope="col" style="width: 20%;">Ghi chú / Lý do từ chối</th>
                            <th scope="col" style="width: 12%;" class="text-center">Trạng thái</th>
                            <th scope="col" style="width: 15%;" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($booking as $bookingItem)
                            <tr class="table-row">
                                <td class="text-center">{{ $booking->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="room-name-clickable"
                                          data-room-id="{{ $bookingItem->room->id ?? '' }}"
                                          title="Nhấn để xem chi tiết phòng">
                                        {{ $bookingItem->room->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="user-name-clickable"
                                          data-user-id="{{ $bookingItem->user->id ?? '' }}"
                                          data-user-name="{{ $bookingItem->user->name ?? 'N/A' }}"
                                          data-user-email="{{ $bookingItem->user->email ?? 'N/A' }}"
                                          data-user-phone="{{ $bookingItem->user->phone ?? 'N/A' }}"
                                          data-user-address="{{ $bookingItem->user->address ?? 'N/A' }}"
                                          data-user-cccd="{{ $bookingItem->user->cccd ?? 'N/A' }}"
                                          data-user-created="{{ $bookingItem->user->created_at ?? 'N/A' }}"
                                          data-user-gender="{{ $bookingItem->user->gender ?? 'N/A' }}"
                                          data-user-birthdate="{{ $bookingItem->user->birthdate ?? 'N/A' }}"
                                          data-user-avatar="{{ $bookingItem->user->avatar ? asset($bookingItem->user->avatar) : asset('img/user.jpg') }}"
                                          title="Nhấn để xem thông tin chi tiết">
                                        {{ $bookingItem->user->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="date-range">
                                        <div class="fw-semibold text-success" style="font-size: 0.85rem;">
                                            <i class="fas fa-calendar-plus me-1" style="font-size: 0.8rem;"></i>
                                            {{ $bookingItem->start_date ? \Carbon\Carbon::parse($bookingItem->start_date)->format('d/m/Y') : 'N/A' }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <i class="fas fa-arrow-down" style="font-size: 0.6rem;"></i>
                                        </div>
                                        <div class="fw-semibold text-danger" style="font-size: 0.85rem;">
                                            <i class="fas fa-calendar-minus me-1" style="font-size: 0.8rem;"></i>
                                            {{ $bookingItem->end_date ? \Carbon\Carbon::parse($bookingItem->end_date)->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($bookingItem->status === 'Từ chối')
                                        <span class="text-danger fst-italic">
                                            {{ $bookingItem->rejection_reason ?? 'Không có lý do từ chối' }}
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">
                                            {{ $bookingItem->note ?? 'Không có ghi chú' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = match ($bookingItem->status) {
                                            'Chấp nhận' => 'success',
                                            'Từ chối' => 'danger',
                                            'Hủy' => 'secondary',
                                            'Chờ xác nhận' => 'warning',
                                            default => 'info'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} py-2 px-3">
                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                        {{ $bookingItem->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(in_array($bookingItem->status, ['Chờ xác nhận']))
                                        <form action="{{ route('bookings.updateStatus', $bookingItem->id) }}" method="POST" class="d-inline status-form" data-booking-id="{{ $bookingItem->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="note" class="note-input">
                                            <select name="status" class="form-select form-select-sm shadow-sm"
                                                    onchange="confirmStatusChange(this, {{ $bookingItem->id }})"
                                                    style="min-width: 140px;">
                                                <option value="Chờ xác nhận" selected>Chờ xác nhận</option>
                                                <option value="Chấp nhận">✓ Chấp nhận</option>
                                                <option value="Từ chối">✗ Từ chối</option>
                                            </select>
                                        </form>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-lock me-1"></i>
                                            @switch($bookingItem->status)
                                                @case('Từ chối')
                                                    Đã từ chối
                                                    @break
                                                @case('Chấp nhận')
                                                    Đã chấp nhận
                                                    @break
                                                @case('Hủy')
                                                    Đã hủy
                                                    @break
                                                @default
                                                    Không thể thay đổi
                                            @endswitch
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                                    <br>
                                    <span class="fs-5">Không có đặt phòng nào.</span>
                                    <br>
                                    <small>Hãy thử thay đổi bộ lọc tìm kiếm.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($booking->hasPages())
                <div class="mt-4">
                    {{ $booking->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Rejection Note -->
    <div class="modal fade" id="rejectNoteModal" tabindex="-1" aria-labelledby="rejectNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectNoteModalLabel">Lý do từ chối</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-envelope me-2"></i>
                        <strong>Lưu ý:</strong> Sau khi từ chối, hệ thống sẽ tự động gửi email thông báo cho khách hàng.
                    </div>
                    <div class="mb-3">
                        <label for="rejectionNote" class="form-label">Vui lòng nhập lý do từ chối:</label>
                        <textarea class="form-control" id="rejectionNote" rows="4" placeholder="Nhập lý do từ chối..." required></textarea>
                        <div class="invalid-feedback">
                            Vui lòng nhập lý do từ chối.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="submitRejectNote">
                        <i class="fas fa-times me-1"></i>Từ chối và gửi email
                    </button>
                </div>
            </div>
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

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
}

.user-name-clickable,
.room-name-clickable {
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

.user-name-clickable::before {
    content: '\f007';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-right: 6px;
    font-size: 0.9rem;
    color: #007bff;
}

.room-name-clickable::before {
    content: '\f015';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-right: 6px;
    font-size: 0.9rem;
    color: #007bff;
}

.user-name-clickable:hover,
.room-name-clickable:hover {
    color: #0056b3;
    background-color: #e7f1ff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.user-name-clickable,
.room-name-clickable {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table td {
    padding: 12px 8px;
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
            const userCreated = this.getAttribute('data-user-created');
            const userGender = this.getAttribute('data-user-gender');
            const userBirthdate = this.getAttribute('data-user-birthdate');
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

    // Handle room name click to open in new tab
    const roomNameElements = document.querySelectorAll('.room-name-clickable');
    roomNameElements.forEach(element => {
        element.addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');
            if (roomId && roomId !== '') {
                window.open(`{{ url('rooms') }}/${roomId}`, '_blank');
            }
        });
    });
});

function confirmStatusChange(selectElement, bookingId) {
    const newStatus = selectElement.value;

    if (newStatus === 'Từ chối') {
        // Hiển thị modal để nhập lý do từ chối
        const modal = new bootstrap.Modal(document.getElementById('rejectNoteModal'));

        // Store form reference
        window.currentForm = selectElement.closest('form');

        // Clear input và show modal
        document.getElementById('rejectionNote').value = '';
        modal.show();

        // Reset select về trạng thái ban đầu
        selectElement.selectedIndex = 0;

    } else if (newStatus === 'Chấp nhận') {
        if (confirm('Bạn có chắc muốn chấp nhận đặt phòng này?')) {
            selectElement.closest('form').submit();
        } else {
            selectElement.selectedIndex = 0;
        }
    }
}

// Handle submit modal
document.getElementById('submitRejectNote').onclick = function() {
    const note = document.getElementById('rejectionNote').value.trim();

    if (!note) {
        alert('Vui lòng nhập lý do từ chối.');
        return;
    }

    // Cập nhật form và submit
    window.currentForm.querySelector('.note-input').value = note;
    window.currentForm.querySelector('select[name="status"]').value = 'Từ chối';
    window.currentForm.submit();
};
</script>
@endsection
