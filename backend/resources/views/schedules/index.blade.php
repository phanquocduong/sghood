@extends('layouts.app')

@section('title', 'Quản lý lịch xem phòng')

@section('content')
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

    <div class="container-fluid py-5 px-4">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4">
                <h5 class="mb-0">Quản lý lịch xem phòng</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('schedules.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control rounded-3" name="querySearch" placeholder="Tìm kiếm theo lời nhắn..." value="{{ request('querySearch') }}">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select rounded-3" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Chờ xác nhận" {{ request('status') == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="Đã xác nhận" {{ request('status') == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="Huỷ bỏ" {{ request('status') == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                            <option value="Hoàn thành" {{ request('status') == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">Tìm</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center shadow-sm rounded-3 overflow-hidden">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Phòng</th>
                                <th>Thời gian</th>
                                <th>Lời nhắn</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>{{ $schedule->user->name ?? 'N/A' }}</td>
                                    <td>{{ $schedule->room->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $schedule->scheduled_at 
                                            ? \Carbon\Carbon::parse($schedule->scheduled_at)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') 
                                            : 'N/A' }}
                                    </td>
                                    <td>{{ $schedule->message ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($schedule->status) {
                                                'Đã xác nhận' => 'warning',
                                                'Huỷ bỏ' => 'danger',
                                                'Hoàn thành' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">
                                            {{ $schedule->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('schedules.updateStatus', $schedule->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="Chờ xác nhận" {{ $schedule->status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận</option>
                                                <option value="Đã xác nhận" {{ $schedule->status == 'Đã xác nhận' ? 'selected' : '' }}>Đã xác nhận</option>
                                                <option value="Huỷ bỏ" {{ $schedule->status == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                                                <option value="Hoàn thành" {{ $schedule->status == 'Hoàn thành' ? 'selected' : '' }}>Hoàn thành</option>
                                            </select>
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

                <div class="d-flex justify-content-center mt-4">
                    {{ $schedules->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
        .badge {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 20px;
        }
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
@endsection
