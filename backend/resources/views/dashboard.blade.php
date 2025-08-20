@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-dark fw-semibold mb-1">Tổng quan hệ thống</h4>
                <p class="text-muted small mb-0">Quản lý trọ - Cập nhật {{ date('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-5">
                <!-- Notes Section -->
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-sticky-note me-2"></i>
                                Ghi chú quan trọng
                            </h6>
                            <button type="button" class="btn btn-link text-white p-0 me-3" data-bs-toggle="modal"
                                data-bs-target="#addNoteModal">
                                <i class="fas fa-plus-circle"></i>
                                <span class="d-none d-sm-inline ms-1">Thêm ghi chú</span>
                            </button>
                            <form action="{{ route('notes.index') }}" method="GET">
                                <button type="submit" class="btn btn-link text-decoration-none p-0 small text-white">Xem
                                    tất cả</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @isset($notes)
                                @forelse($notes->take(4) as $note)
                                    <li class="list-group-item "py-3" data-id-user="{{ $note->user_id }}"
                                        data-note-id="{{ $note->id }}">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-1 text-dark">{{ $note->content }}</p>
                                                <small class="text-muted">
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary me-2 text-white">{{ $note->type ?? 'Không xác định' }}</span>
                                                    bởi <strong
                                                        class="text-primary">{{ $note->user->name ?? 'Người dùng không tồn tại' }}</strong>
                                                    -
                                                    {{ $note->created_at ? $note->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y, H:i') : 'Chưa có thời gian' }}
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Chưa có ghi chú nào.
                                    </li>
                                @endforelse
                            @else
                                <li class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Chưa tải được ghi chú:
                                    {{ session('error') ?? 'Lỗi không xác định' }}
                                </li>
                            @endisset
                        </ul>
                    </div>
                </div>
                <!-- Yêu cầu sửa chữa đang thực hiện -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3"
                        style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-tools text-warning me-2"></i>
                                Yêu cầu sửa chữa đang thực hiện
                            </h6>
                            <a href="{{ route('repair_requests.index') }}" class="text-decoration-none small text-white">Xem
                                tất cả</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">Phòng</th>
                                        <th class="fw-semibold">KH</th>

                                        <th class="fw-semibold">Mô tả</th>
                                        <th class="fw-semibold">Trạng thái</th>
                                        <th class="fw-semibold">HĐ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($repairRequests)
                                        @forelse($repairRequests->where('status', 'Đang thực hiện') as $request)
                                            <tr>
                                                <td class="py-3 text-dark">
                                                    <span
                                                        class="fw-semibold text-primary">{{ $request->contract->room->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="py-3 text-dark">
                                                    {{ $request->contract->user->name ?? 'N/A' }}
                                                </td>
                                                <td class="py-3">
                                                    <small
                                                        class="text-muted">{{ Str::limit($request->description ?? ($request->title ?? 'Không có mô tả'), 30) }}</small>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge bg-opacity-20 text-info">
                                                        <i class="fas fa-cog me-1"></i>Đang thực hiện
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="{{ route('repair_requests.show', $request->id) }}">
                                                        <i class="fas fa-eye me-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fas fa-info-circle me-2"></i>Không có yêu cầu sửa chữa nào đang
                                                    thực
                                                    hiện.
                                                </td>
                                            </tr>
                                        @endforelse
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-exclamation-triangle me-2"></i>Không thể tải dữ liệu yêu cầu
                                                sửa
                                                chữa.
                                            </td>
                                        </tr>
                                    @endisset
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Repair Requests -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3"
                        style="background: linear-gradient(135deg, #fbbf24 0%, #f59e42 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-tools text-warning me-2"></i>
                                Yêu cầu sửa chữa cần xử lý
                            </h6>
                            <a href="{{ route('repair_requests.index') }}"
                                class="text-decoration-none small text-white">Xem
                                tất cả</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">Phòng</th>
                                        <th class="fw-semibold">KH</th>
                                        <th class="fw-semibold">Mô tả</th>
                                        <th class="fw-semibold">Trạng thái</th>
                                        <th class="fw-semibold">HĐ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($repairRequests)
                                        @forelse($repairRequests->where('status', 'Chờ xác nhận') as $request)
                                            <tr>
                                                <td class= "py-3 text-dark">
                                                    <span
                                                        class="fw-semibold text-primary">{{ $request->contract->room->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class= "py-3 text-dark">
                                                    {{ $request->contract->user->name ?? 'N/A' }}
                                                </td>
                                                <td class= "py-3">
                                                    <small
                                                        class="text-muted">{{ Str::limit($request->description ?? ($request->title ?? 'Không có mô tả'), 30) }}</small>
                                                </td>
                                                <td class= "py-3">
                                                    @switch($request->status)
                                                        @case('Chờ xác nhận')
                                                            <span class="badge bg-opacity-20 text-warning">
                                                                <i class="fas fa-clock me-1"></i>Chờ xác nhận
                                                            </span>
                                                        @break

                                                        @case('Đang thực hiện')
                                                            <span class="badge bg-opacity-20 text-info">
                                                                <i class="fas fa-cog me-1"></i>Đang thực hiện
                                                            </span>
                                                        @break

                                                        @case('Hoàn thành')
                                                            <span class="badge bg-opacity-20 text-success">
                                                                <i class="fas fa-check me-1"></i>Hoàn thành
                                                            </span>
                                                        @break

                                                        @case('Đã hủy')
                                                            <span class="badge bg-opacity-20 text-danger">
                                                                <i class="fas fa-times me-1"></i>Đã hủy
                                                            </span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-opacity-20 text-secondary">
                                                                <i class="fas fa-question me-1"></i>{{ ucfirst($request->status) }}
                                                            </span>
                                                    @endswitch
                                                </td>
                                                <td class= "py-3">
                                                    <a class="btn btn-sm btn-outline-primary" target="_blank"
                                                        href="{{ route('repair_requests.show', $request->id) }}">
                                                        <i class="fas fa-eye me-1"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-info-circle me-2"></i>Không có yêu cầu sửa chữa nào cần xử
                                                        lý.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>Không thể tải dữ liệu yêu cầu
                                                    sửa chữa.
                                                </td>
                                            </tr>
                                        @endisset
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column -->
                <div class="col-lg-4">
                    <!-- Contract Management Section -->
                    <div class="row g-4">
                        <!-- Newly Signed Contracts -->
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"
                                    style="background: linear-gradient(135deg, #34d399 0%, #059669 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-white">
                                            <i class="fas fa-file-signature text-success me-2"></i>
                                            Hợp đồng vừa ký
                                        </h6>
                                        <a href="#" class="text-decoration-none small text-white">Xem tất cả</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($justSignedContracts as $contract)
                                            <li class="list-group-item py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-file-contract text-success text-white"
                                                                style="font-size: 12px;"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                            {{ $contract->user->name ?? 'Không xác định' }}</div>
                                                        <small class="text-muted">
                                                            {{ $contract->room->name ?? 'Không xác định' }} -
                                                            Ký: <span
                                                                class="text-primary">{{ $contract->start_date ? $contract->start_date->format('d/m/Y') : 'Không xác định' }}</span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có hợp đồng nào vừa ký.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Contracts Nearing Expiration -->
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"
                                    style="background: linear-gradient(135deg, #f472b6 0%, #db2777 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-white">
                                            <i class="fas fa-hourglass-half text-warning me-2"></i>
                                            Hợp đồng sắp hết hạn
                                        </h6>
                                        <a href="#" class="text-decoration-none small text-white">Xem tất cả</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($contracts as $contract)
                                            <li class="list-group-item py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-file-contract text-warning text-white"
                                                                style="font-size: 12px;"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                            {{ $contract->user->name ?? 'Không xác định' }}</div>
                                                        <small class="text-muted">
                                                            {{ $contract->room->name ?? 'Không xác định' }} -
                                                            Hết hạn: <span
                                                                class="text-primary">{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Không xác định' }}</span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có hợp đồng nào sắp hết hạn.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!--Yêu cầu trả phòng  -->
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"
                                    style="background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-white">
                                            <i class="fas fa-sign-out-alt text-danger me-2"></i>
                                            Yêu cầu trả phòng
                                        </h6>
                                        <a href="{{ route('checkouts.index') }}"
                                            class="text-decoration-none small text-white">Xem tất cả</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($checkouts as $checkout)
                                            <li class="list-group-item py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-user text-warning text-white"
                                                                style="font-size: 12px;"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                            <a
                                                                href="{{ route('checkouts.index') }}">{{ $checkout->contract->user->name ?? 'Không xác định' }}</a>
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $checkout->contract->room->name ?? 'Không xác định' }} -
                                                            Thời gian: <span
                                                                class="text-primary">{{ is_object($checkout) && $checkout->created_at instanceof \Carbon\Carbon ? $checkout->created_at->format('d/m/Y') : 'Không xác định' }}</span>
                                                        </small>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có hợp đồng nào sắp hết hạn.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--Phòng đang sửa chữa-->
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header bg-white py-3"
                                    style="background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-white">
                                            <i class="fas fa-sign-out-alt text-danger me-2"></i>
                                            Phòng đang sửa chữa
                                        </h6>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($roomsUnderRepair as $room)
                                            <li class="list-group-item py-2" id="room-item-{{ $room->id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px;">
                                                            <i class="fas fa-tools text-warning" style="font-size: 12px;"></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <div>
                                                            <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                                {{ $room->name ?? 'Không xác định' }}
                                                            </div>
                                                            <small class="text-muted">
                                                                Trạng thái: <span class="text-danger"
                                                                    name="status">{{ $room->status ?? 'Không xác định' }}</span>
                                                            </small>
                                                        </div>
                                                        <div>
                                                            <button class="btn btn-sm btn-outline-success confirm-repair-btn"
                                                                data-room-id="{{ $room->id }}"
                                                                data-status="{{ $room->status ?? 'Không xác định' }}">
                                                                <i class="fas fa-check me-2"></i>Xác nhận
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có phòng nào đang sửa chữa.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-3">
                    <!-- Expired Contracts -->
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3"
                            style="background: linear-gradient(135deg, #f87171 0%, #b91c1c 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-white">
                                    <i class="fas fa-message text-danger me-2"></i>
                                    Tin nhắn mới
                                </h6>
                                <a href="{{ route('messages.index') }}" class="text-decoration-none small text-white">Xem tất
                                    cả</a>
                            </div>
                        </div>
                        <div class="card-body p-0 fw-bold">
                            <ul class="list-group list-group-flush">
                                @forelse ($messages as $mess)
                                    <li class="list-group-item py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-message text-danger text-white"
                                                        style="font-size: 12px;"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size: 14px;">
                                                    {{ $mess->sender_name }}
                                                </div>
                                                <div class="text-dark" style="font-size: 13px;">
                                                    {{ $mess->message ?? '(Không có nội dung)' }}
                                                </div>
                                                <small class="text-muted">
                                                    <span class="text-primary">
                                                        {{ \Carbon\Carbon::parse($mess->created_at ?? now())->diffForHumans() }}
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item py-2 text-muted">
                                        Không có tin nhắn nào.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Check-in Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3"
                            style="background: linear-gradient(135deg, #4ade80 0%, #16a34a 100%);">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-sign-in-alt text-success me-2"></i>
                                Lịch xem trọ sắp tới
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($schedules as $schedule)
                                    <li class="list-group-item py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-calendar-plus text-success text-white"
                                                        style="font-size: 12px;"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                    {{ $schedule->user->name ?? 'Không xác định' }}</div>
                                                <small class="text-muted">
                                                    {{ $schedule->scheduled_at ? $schedule->scheduled_at->format('d/m/Y') : 'Không xác định' }}
                                                    -
                                                    <span
                                                        class="text-primary">{{ $schedule->motel->name ?? 'Không xác định' }}</span>
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Không có lịch check-in sắp tới.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Recently Renewed Contracts -->
                    <div class="card">
                        <div class="card-header bg-white py-3"
                            style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-white">
                                    <i class="fas fa-file-contract text-info text-white me-2"></i>
                                    Yêu cầu gia hạn
                                </h6>
                                <a href="#" class="text-decoration-none small text-white">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($contractExtensions as $extension)
                                    <li class="list-group-item py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;">
                                                    <i class="fas fa-file-contract text-info text-white"
                                                        style="font-size: 12px;"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">
                                                    {{ $extension->contract->user->name ?? 'Không xác định' }}</div>
                                                <small class="text-muted">
                                                    {{ $extension->contract->room->name ?? 'Không xác định' }} -
                                                    Gia hạn: <span
                                                        class="text-primary">{{ $extension->new_end_date ? \Carbon\Carbon::parse($extension->new_end_date)->format('d/m/Y') : 'Không xác định' }}</span>
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Không có yêu cầu gia hạn hợp đồng nào.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Thêm Ghi Chú -->
        <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addNoteModalLabel">
                            <i class="fas fa-plus me-2"></i>Thêm ghi chú mới
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="{{ route('notes.storeDashboard') }}" method="POST" id="addNoteForm" novalidate>
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="add_content" class="form-label fw-bold">Nội dung ghi chú <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('content') is-invalid @enderror" id="add_content" name="content" rows="4"
                                    placeholder="Nhập nội dung ghi chú..." required maxlength="255">{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Tối đa 255 ký tự</div>
                            </div>
                            <div class="mb-3">
                                <label for="add_type" class="form-label fw-bold">Loại ghi chú <span
                                        class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" id="add_type"
                                    name="type" required onchange="toggleCustomType(this)">
                                    <option value="">Chọn loại ghi chú</option>
                                    @forelse($types as $noteType)
                                        <option value="{{ $noteType }}" {{ old('type') == $noteType ? 'selected' : '' }}>
                                            {{ $noteType }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Không có loại ghi chú nào</option>
                                    @endforelse
                                    <option value="{{ old('type') }}" class="custom-option">Khác</option>
                                </select>
                                <input type="text" class="form-control mt-2 @error('type') is-invalid @enderror"
                                    id="custom_type" style="display: {{ $isCustomType ? 'block' : 'none' }};"
                                    placeholder="Nhập loại ghi chú mới..." maxlength="50" value="{{ old('type') }}"
                                    oninput="updateTypeValue(this)">
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Tối đa 50 ký tự cho loại ghi chú mới</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Hủy
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Lưu ghi chú
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    @endsection

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Lấy CSRF token từ meta tag (Laravel đặt sẵn trong layout)
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                document.querySelectorAll('.confirm-repair-btn').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const roomId = this.dataset.roomId;
                        const roomStatus = this.dataset.status;
                        if (!roomId) return;
                        // Xác nhận với user
                        if (!confirm(
                                'Bạn có chắc muốn xác nhận kết thúc sửa chữa và chuyển phòng sang "Phòng trống"?'
                            )) {
                            return;
                        }

                        // Disable button tạm thời
                        this.setAttribute('disabled', 'disabled');
                        this.innerHTML =
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý';

                        try {
                            const res = await fetch(`/rooms/${roomId}/confirm-repair`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    status: "Trống"
                                })
                            });

                            const data = await res.json();

                            if (!res.ok || !data.status) {
                                alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                                // khôi phục nút
                                this.removeAttribute('disabled');
                                this.innerHTML = '<i class="fas fa-check me-2"></i>Xác nhận';
                                return;
                            }

                            // Thành công: loại bỏ item khỏi danh sách hoặc cập nhật trạng thái
                            const item = document.getElementById('room-item-' + roomId);
                            if (item) {
                                // animation nhỏ rồi remove
                                item.style.transition = 'opacity 0.25s ease';
                                item.style.opacity = 0;
                                setTimeout(() => item.remove(), 260);
                            }

                            // (Tùy) hiển thị toast / alert
                            alert(data.message || 'Cập nhật thành công.');

                        } catch (err) {
                            console.error(err);
                            alert('Lỗi mạng hoặc server. Vui lòng thử lại sau.');
                            this.removeAttribute('disabled');
                            this.innerHTML = '<i class="fas fa-check me-2"></i>Xác nhận';
                        }
                    });
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if ($errors->any() || old('content') || old('type'))
                    var addNoteModal = new bootstrap.Modal(document.getElementById('addNoteModal'));
                    addNoteModal.show();

                    var select = document.getElementById('add_type');
                    var customInput = document.getElementById('custom_type');
                    var selectedValue = "{{ old('type') }}";
                    var isCustom = {{ $isCustomType ? 'true' : 'false' }};

                    if (isCustom && selectedValue) {
                        customInput.style.display = 'block';
                        customInput.value = selectedValue;
                        select.querySelector('.custom-option').value = selectedValue;
                        select.value = selectedValue;
                    }
                @endif
            });

            function toggleCustomType(select) {
                var customInput = document.getElementById('custom_type');
                var isCustom = select.options[select.selectedIndex].classList.contains('custom-option');
                customInput.style.display = isCustom ? 'block' : 'none';
                if (!isCustom) {
                    select.value = select.options[select.selectedIndex].value;
                } else {
                    customInput.focus();
                }
            }

            function updateTypeValue(input) {
                var select = document.getElementById('add_type');
                var customOption = select.querySelector('.custom-option');
                customOption.value = input.value;
                select.value = input.value;
            }
        </script>
    @endsection
    @section('styles')
        <style>
            .status-row {
                display: flex;
                align-items: center;
                gap: 8px;
                /* khoảng cách giữa chữ và nút */
            }

            .status-row .status-text {
                color: red;
                font-weight: 500;
            }

            .status-row .btn-confirm {
                padding: 2px 8px;
                font-size: 0.85rem;
                border-radius: 4px;
            }
        </style>
    @endsection
