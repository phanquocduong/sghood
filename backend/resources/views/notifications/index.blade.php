@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="card bg-glass shadow-lg rounded-3 border-0">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center p-3"
                style="background: linear-gradient(135deg, #00c6ff, #0072ff);">
                <h5 class="mb-0 fw-bold text-uppercase">Thông báo</h5>
                {{-- ✅ Thêm nút đánh dấu tất cả đã đọc --}}
                @php
                    $hasUnread = $notifications->where('status', 'Chưa đọc')->count() > 0;
                @endphp
                @if($hasUnread)
                    <button type="button" class="btn btn-light btn-sm rounded-pill shadow-sm mark-all-read-btn" 
                        title="Đánh dấu tất cả là đã đọc">
                        <i class="fas fa-check-double me-2"></i>Đánh dấu tất cả đã đọc
                    </button>
                @endif
            </div>

            <div class="card-body p-4">
                {{-- Bộ lọc --}}
                <form action="{{ route('notifications.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control bg-light border-0 shadow-sm rounded-pill px-4"
                            name="querySearch" placeholder="Tìm kiếm theo tiêu đề..." value="{{ request('querySearch') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select bg-light border-0 shadow-sm rounded-pill px-4" name="status">
                            <option value="">Tất cả loại</option>
                            <option value="Chưa đọc" {{ request('status') == 'Chưa đọc' ? 'selected' : '' }}>Chưa đọc</option>
                            <option value="Đã đọc" {{ request('status') == 'Đã đọc' ? 'selected' : '' }}>Đã đọc</option>
                        </select>
                    </div>
                    <!-- lọc theo cũ, mới -->
                    <div class="col-md-3">
                        <select class="form-select bg-light border-0 shadow-sm rounded-pill px-4" name="sort">
                            <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 rounded-pill shadow-sm">Lọc</button>
                    </div>
                </form>

                {{-- ✅ Thống kê thông báo --}}
                @php
                    $totalNotifications = $notifications->total();
                    $unreadCount = $notifications->where('status', 'Chưa đọc')->count();
                    $readCount = $notifications->where('status', 'Đã đọc')->count();
                @endphp
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="d-flex gap-3 flex-wrap">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <i class="fas fa-bell me-2"></i>Tổng: {{ $totalNotifications }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Bảng danh sách --}}
                <div class="table-responsive">
                    <table class="table table-modern w-100">
                        <thead class="bg-light text-dark">
                            <tr class="text-uppercase">
                                <th>STT</th>
                                <th>Tiêu đề</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = ($notifications->currentPage() - 1) * $notifications->perPage() + 1; @endphp
                            @if($notifications->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-bell-slash fa-3x mb-3 d-block text-muted"></i>
                                        Không có thông báo nào
                                    </td>
                                </tr>
                            @endif
                            @foreach($notifications as $notification)
                                <tr class="align-middle notification-row {{ $notification->status == 'Chưa đọc' ? 'unread' : 'read' }}"
                                    data-id="{{ $notification->id }}" data-title="{{ $notification->title }}"
                                    data-content="{{ $notification->content }}" data-status="{{ $notification->status }}">
                                    <td>{{ $i++ }}</td>
                                    <td class="fw-semibold {{ $notification->status == 'Chưa đọc' ? 'text-primary' : 'text-dark' }}">
                                        {{ $notification->title }}
                                        @if($notification->status == 'Chưa đọc')
                                            <i class="fas fa-circle text-danger ms-2" style="font-size: 6px;"></i>
                                        @endif
                                    </td>
                                    <td class="content-short text-muted">{{ Str::limit($notification->content, 50) }}</td>
                                    <td>
                                        <span class="badge {{ $notification->status == 'Chưa đọc' ? 'bg-danger' : 'bg-success' }} status-badge">
                                            {{ $notification->status }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($notification->created_at)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Modal hiển thị chi tiết thông báo -->
                    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="notificationModalLabel">Chi tiết thông báo</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h5 id="modalTitle"></h5>
                                    <p id="modalContent" class="mt-3"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phân trang --}}
                <div class="mt-4">
                    {{ $notifications->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

     {{-- CSS --}}
    <style>
        .table-modern {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table-modern td,
        .table-modern th {
            vertical-align: middle;
        }
        
        .table-modern tr {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        .btn-outline-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        tr.notification-row.unread {
            background-color: rgba(64, 126, 233, 0.15) !important;
            cursor: pointer;
        }

        tr.notification-row.read {
            background-color: #ffffff !important;
            cursor: pointer;
        }

        .notification-row:hover {
            background-color: #dbe7fd !important;
        }

        /* ✅ Style cho nút đánh dấu tất cả đã đọc */
        .mark-all-read-btn {
            transition: all 0.3s ease;
        }

        .mark-all-read-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .mark-all-read-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Animation cho status badge */
        .status-badge {
            transition: all 0.3s ease;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>

    {{-- JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('.notification-row');
            const markAllReadBtn = document.querySelector('.mark-all-read-btn');

            // ✅ Xử lý click vào từng notification
            rows.forEach(row => {
                row.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const title = this.dataset.title;
                    const content = this.dataset.content;
                    const status = this.dataset.status;

                    // Hiện modal
                    document.getElementById('modalTitle').innerText = title;
                    document.getElementById('modalContent').innerText = content;
                    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                    modal.show();

                    if (status === 'Chưa đọc') {
                        // Gửi PATCH AJAX
                        fetch(`/notifications/${id}/mark-as-read`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        }).then(response => {
                            if (response.ok) {
                                // Đổi class nền và trạng thái
                                this.classList.remove('unread');
                                this.classList.add('read');
                                
                                // Cập nhật badge trạng thái
                                const statusBadge = this.querySelector('.status-badge');
                                if (statusBadge) {
                                    statusBadge.classList.remove('bg-danger');
                                    statusBadge.classList.add('bg-success');
                                    statusBadge.innerText = 'Đã đọc';
                                }
                                
                                // Cập nhật tiêu đề (bỏ icon đỏ)
                                const titleCell = this.querySelector('td:nth-child(2)');
                                if (titleCell) {
                                    titleCell.classList.remove('text-primary');
                                    titleCell.classList.add('text-dark');
                                    const redDot = titleCell.querySelector('.fas.fa-circle.text-danger');
                                    if (redDot) {
                                        redDot.remove();
                                    }
                                }
                                
                                this.dataset.status = 'Đã đọc';
                                
                                // Kiểm tra xem còn thông báo chưa đọc không
                                checkMarkAllReadButton();
                            }
                        }).catch(error => {
                            console.error('Lỗi khi đánh dấu đã đọc:', error);
                        });
                    }
                });
            });

            // ✅ Xử lý nút "Đánh dấu tất cả đã đọc"
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function() {
                    // Hiển thị confirm dialog
                    if (!confirm('Bạn có chắc muốn đánh dấu tất cả thông báo là đã đọc?')) {
                        return;
                    }

                    // Hiển thị loading state
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang xử lý...';
                    this.disabled = true;

                    // Gửi AJAX request
                    fetch('/notifications/mark-all-as-read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Cập nhật tất cả các row chưa đọc
                            const unreadRows = document.querySelectorAll('.notification-row.unread');
                            unreadRows.forEach(row => {
                                row.classList.remove('unread');
                                row.classList.add('read');
                                
                                // Cập nhật badge trạng thái
                                const statusBadge = row.querySelector('.status-badge');
                                if (statusBadge) {
                                    statusBadge.classList.remove('bg-danger');
                                    statusBadge.classList.add('bg-success');
                                    statusBadge.innerText = 'Đã đọc';
                                }
                                
                                // Cập nhật tiêu đề
                                const titleCell = row.querySelector('td:nth-child(2)');
                                if (titleCell) {
                                    titleCell.classList.remove('text-primary');
                                    titleCell.classList.add('text-dark');
                                    const redDot = titleCell.querySelector('.fas.fa-circle.text-danger');
                                    if (redDot) {
                                        redDot.remove();
                                    }
                                }
                                
                                row.dataset.status = 'Đã đọc';
                            });

                            // Ẩn nút đánh dấu tất cả
                            this.style.display = 'none';

                            // ✅ Removed: Success alert display as requested
                            console.log('All notifications marked as read successfully');
                        } else {
                            // ✅ Only show error alerts
                            showAlert('error', data.message || 'Có lỗi xảy ra khi đánh dấu thông báo');
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        showAlert('error', 'Có lỗi xảy ra khi đánh dấu thông báo');
                    })
                    .finally(() => {
                        // Khôi phục trạng thái nút
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    });
                });
            }

            // ✅ Hàm kiểm tra và ẩn/hiện nút đánh dấu tất cả
            function checkMarkAllReadButton() {
                const unreadRows = document.querySelectorAll('.notification-row.unread');
                if (markAllReadBtn && unreadRows.length === 0) {
                    markAllReadBtn.style.display = 'none';
                }
            }

            // ✅ Hàm hiển thị alert (chỉ cho error)
            function showAlert(type, message) {
                if (type !== 'error') return; // ✅ Only show error alerts

                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Chèn alert vào đầu container
                const container = document.querySelector('.container-fluid');
                container.insertAdjacentHTML('afterbegin', alertHtml);

                // Tự động ẩn sau 5 giây
                setTimeout(() => {
                    const alert = container.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);

                // Scroll lên đầu
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
@endsection