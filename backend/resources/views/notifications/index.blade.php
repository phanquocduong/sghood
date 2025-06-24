@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="container-fluid pt-4 px-4">
        {{-- Alert --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card bg-glass shadow-lg rounded-3 border-0">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center p-3"
                style="background: linear-gradient(135deg, #00c6ff, #0072ff);">
                <h5 class="mb-0 fw-bold text-uppercase">Thông báo</h5>
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

                {{-- Bảng danh sách --}}
                <div class="table-responsive">
                    <table class="table table-modern w-100">
                        <thead class="bg-light text-dark">
                            <tr class="text-uppercase">
                                <th>STT</th>
                                <th>Tiêu đề</th>
                                <th>Nội dung</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @if($notifications->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Không có thông báo nào</td>
                                </tr>
                            @endif
                            @foreach($notifications as $notification)
                                <tr class="align-middle notification-row {{ $notification->status == 'Chưa đọc' ? 'unread' : 'read' }}"
                                    data-id="{{ $notification->id }}" data-title="{{ $notification->title }}"
                                    data-content="{{ $notification->content }}" data-status="{{ $notification->status }}">
                                    <td>{{ $i++ }}</td>
                                    <td class="fw-semibold text-primary">{{ $notification->title }}</td>
                                    <td class="content-short text-muted">{{ Str::limit($notification->content, 50) }}</td>
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
                @if(method_exists($notifications, 'links'))
                    <div class="d-flex justify-content-center mt-4">
                        {{ $notifications->links('vendor.pagination.custom') }}
                    </div>
                @endif
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
    </style>


    {{-- JavaScript --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rows = document.querySelectorAll('.notification-row');

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
                                    this.querySelector('.status-badge').classList.remove('bg-danger');
                                    this.querySelector('.status-badge').classList.add('bg-secondary');
                                    this.querySelector('.status-badge').innerText = 'Đã đọc';
                                    this.querySelector('.action-cell').innerHTML = '<span class="text-muted">Đã xử lý</span>';
                                    this.dataset.status = 'Đã đọc';
                                }
                            });
                        }
                    });
                });
            });
        </script>

@endsection