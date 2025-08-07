@extends('layouts.app')

@section('title', 'Quản lý lịch xem phòng')

@section('content')
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

    <style>
    .table td,
    .table th {
        vertical-align: middle;
    }

    .badge {
        padding: 6px 12px;
        font-size: 0.9rem;
        border-radius: 20px;
    }

    .form-select:focus,
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .user-info-link,
    .motel-info-link {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .user-info-link:hover,
    .motel-info-link:hover {
        text-decoration: underline !important;
        transform: scale(1.05);
    }

    .motel-info-link:hover .fa-external-link-alt {
        color: #0d6efd !important;
        transform: translateX(2px);
    }

    .user-avatar-container {
        position: relative;
    }

    .user-details .row {
        border-bottom: 1px solid #f0f0f0;
        padding: 8px 0;
    }

    .user-details .row:last-child {
        border-bottom: none;
    }

    #userInfoModal .modal-body {
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    }

    #userInfoModal .user-details i {
        color: #6c757d;
        width: 16px;
    }
</style>

    <div class="container-fluid py-5 px-4">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4">
                <h5 class="mb-0">Quản lý lịch xem phòng</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('schedules.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control rounded-3" name="querySearch"
                            placeholder="Tìm kiếm theo tên người dùng, nội dung, tên dãy trọ..."
                            value="{{ request('querySearch') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select rounded-3" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Chờ xác nhận" {{ request('status') == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác
                                nhận</option>
                            <option value="Đã xác nhận" {{ request('status') == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác
                                nhận</option>
                            <option value="Từ chối" {{ request('status') == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                            <option value="Hoàn thành" {{ request('status') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select rounded-3" name="sort_by">
                            <option value="">Sắp xếp theo</option>
                            <option value="created_at_desc" {{ request('sort_by') == 'created_at_desc' ? 'selected' : '' }}>
                                Mới nhất</option>
                            <option value="created_at_asc" {{ request('sort_by') == 'created_at_asc' ? 'selected' : '' }}>Cũ
                                nhất</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">
                            <span class="m-2"><i class="fas fa-search"></i></span>
                            Tìm</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center shadow-sm rounded-3 overflow-hidden">
                        <thead class="table-dark">
                            <tr>
                                <th>STT</th>
                                <th>Người dùng</th>
                                <th>Dãy trọ</th>
                                <th>Ngày xem phòng</th>
                                <th>Lời nhắn của người dùng</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedules as $schedule)
                                <tr>
                                    <td>{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $loop->iteration }}</td>
                                    <td>{{ $schedule->user->name ?? 'N/A' }}</td>
                                    <td>{{ $schedule->motel->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $schedule->scheduled_at
                                            ? \Carbon\Carbon::parse($schedule->scheduled_at)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')
                                            : 'N/A' }}
                                    </td>
                                    <td>{{ $schedule->message ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($schedule->status) {
                                                'Đã xác nhận' => 'warning',
                                                'Hoàn thành' => 'success',
                                                'Từ chối' => 'dark',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">
                                            {{ $schedule->status }}
                                        </span> <br>
                                        @if ($schedule->status == 'Từ chối' && $schedule->rejection_reason)
                                            Lí do: <strong>{{ $schedule->rejection_reason }}</strong>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('schedules.updateStatus', $schedule->id) }}" method="POST"
                                            class="status-form" id="status-form-{{ $schedule->id }}">
                                            @csrf
                                            @method('PATCH')
                                            @if($schedule->status == 'Từ chối' || $schedule->status == 'Hoàn thành')
                                            <select name="status" class="form-select form-select-sm status-select" data-schedule-id="{{ $schedule->id }}" disabled>
                                                @switch($schedule->status)
                                                    @case('Chờ xác nhận')
                                                        <option value="Chờ xác nhận" selected>Chờ xác nhận</option>
                                                        <option value="Đã xác nhận">Đã xác nhận</option>
                                                        <option value="Từ chối">Từ chối</option>
                                                    @break
                                                    @case('Đã xác nhận')
                                                        <option value="Đã xác nhận" selected>Đã xác nhận</option>
                                                        <option value="Hoàn thành">Hoàn thành</option>
                                                    @break
                                                    @case('Từ chối')
                                                        <option value="Từ chối" selected>Từ chối</option>
                                                    @break
                                                    @case('Hoàn thành')
                                                        <option value="Hoàn thành" selected>Hoàn thành</option>
                                                    @break
                                                    @default
                                                        <option value="Chờ xác nhận" {{ $schedule->status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                                                        <option value="Đã xác nhận" {{ $schedule->status == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                                                        <option value="Từ chối" {{ $schedule->status == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                                                        <option value="Hoàn thành" {{ $schedule->status == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                                                @endswitch
                                            </select>
                                            @else
                                                <select name="status" class="form-select form-select-sm status-select"
                                                    data-schedule-id="{{ $schedule->id }}">
                                                    @switch($schedule->status)
                                                        @case('Chờ xác nhận')
                                                            <option value="Chờ xác nhận" selected>Chờ xác nhận</option>
                                                            <option value="Đã xác nhận">Đã xác nhận</option>
                                                            <option value="Từ chối">Từ chối</option>
                                                        @break

                                                        @case('Đã xác nhận')
                                                            <option value="Đã xác nhận" selected>Đã xác nhận</option>
                                                            <option value="Hoàn thành">Hoàn thành</option>
                                                        @break

                                                        @case('Từ chối')
                                                            <option value="Từ chối" selected>Từ chối</option>
                                                        @break

                                                        @case('Hoàn thành')
                                                            <option value="Hoàn thành" selected>Hoàn thành</option>
                                                        @break

                                                        @default
                                                            <option value="Chờ xác nhận"
                                                                {{ $schedule->status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác
                                                                nhận</option>
                                                            <option value="Đã xác nhận"
                                                                {{ $schedule->status == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác
                                                                nhận</option>
                                                            <option value="Từ chối"
                                                                {{ $schedule->status == 'Từ chối' ? 'selected' : '' }}>Từ chối
                                                            </option>
                                                            <option value="Hoàn thành"
                                                                {{ $schedule->status == 'Hoàn thành' ? 'selected' : '' }}>Hoàn
                                                                thành</option>
                                                    @endswitch
                                                </select>
                                            @endif
                                            <input type="hidden" name="cancel_reason" class="cancel-reason-input">
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Không có lịch xem phòng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4 pagination">
                        {{ $schedules->onEachSide(0)->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>

    <!-- Cancel Reason Modal -->
    <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelReasonModalLabel">Lý do hủy/từ chối lịch xem phòng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Vui lòng nhập lý do:</label>
                        <textarea class="form-control" id="cancelReason" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" id="confirmCancel">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>

        <style>
            .table td,
            .table th {
                vertical-align: middle;
            }

            .badge {
                padding: 6px 12px;
                font-size: 0.9rem;
                border-radius: 20px;
            }

            .form-select:focus,
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
            }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script src="{{ asset('js/schedule.js') }}"></script>
@endsection
