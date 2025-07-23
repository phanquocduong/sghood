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
            <div class="col-lg-8">
                <!-- Notes Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-sticky-note me-2"></i>
                                Ghi chú quan trọng
                            </h6>
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
                                    <li class="list-group-item border-0 py-3" data-id-user="{{ $note->user_id }}"
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
                                    <li class="list-group-item border-0 text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Chưa có ghi chú nào.
                                    </li>
                                @endforelse
                            @else
                                <li class="list-group-item border-0 text-center text-muted py-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Chưa tải được ghi chú:
                                    {{ session('error') ?? 'Lỗi không xác định' }}
                                </li>
                            @endisset
                        </ul>
                    </div>
                </div>

                <!-- Repair Requests -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-tools text-warning me-2"></i>
                                Yêu cầu sửa chữa cần xử lý
                            </h6>
                            <a href="{{ route('repair_requests.index') }}" class="text-decoration-none small text-dark">Xem
                                tất cả</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Phòng</th>
                                        <th class="border-0 fw-semibold">Khách hàng</th>
                                        <th class="border-0 fw-semibold">Ngày tạo</th>
                                        <th class="border-0 fw-semibold">Mô tả</th>
                                        <th class="border-0 fw-semibold">Trạng thái</th>
                                        <th class="border-0 fw-semibold">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($repairRequests)
                                        @forelse($repairRequests as $request)
                                            <tr>
                                                <td class="border-0 py-3 text-dark">
                                                    <span
                                                        class="fw-semibold text-primary">{{ $request->contract->room->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="border-0 py-3 text-dark">
                                                    {{ $request->contract->user->name ?? 'N/A' }}</td>
                                                <td class="border-0 py-3 text-dark">
                                                    <small
                                                        class="text-muted">{{ $request->created_at ? $request->created_at->format('d/m/Y') : 'N/A' }}</small>
                                                </td>
                                                <td class="border-0 py-3">
                                                    <small
                                                        class="text-muted">{{ Str::limit($request->description ?? ($request->title ?? 'Không có mô tả'), 30) }}</small>
                                                </td>
                                                <td class="border-0 py-3">
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
                                                <td class="border-0 py-3">
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="{{ route('repair_requests.show', $request->id) }}">
                                                        <i class="fas fa-eye me-1"></i>Chi tiết
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="border-0 text-center text-muted py-4">
                                                        <i class="fas fa-info-circle me-2"></i>Không có yêu cầu sửa chữa nào cần xử
                                                        lý.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        @else
                                            <tr>
                                                <td colspan="6" class="border-0 text-center text-muted py-4">
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

                    <!-- Contract Management Section -->
                    <div class="row g-4">
                        <!-- Newly Signed Contracts -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-dark">
                                            <i class="fas fa-file-signature text-success me-2"></i>
                                            Hợp đồng vừa ký
                                        </h6>
                                        <a href="#" class="text-decoration-none small text-dark">Xem tất cả</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <!-- <li class="list-group-item border-0 py-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0 me-3">
                                                                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                                     style="width: 32px; height: 32px;">
                                                                                    <i class="fas fa-file-contract text-success text-white" style="font-size: 12px;"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Nguyễn Văn A</div>
                                                                                <small class="text-muted">P101 - Ký: 01/07/2025</small>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                    <li class="list-group-item border-0 py-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0 me-3">
                                                                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                                     style="width: 32px; height: 32px;">
                                                                                    <i class="fas fa-file-contract text-success text-white" style="font-size: 12px;"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Trần Thị B</div>
                                                                                <small class="text-muted">P202 - Ký: 02/07/2025</small>
                                                                            </div>
                                                                        </div>
                                                                    </li> -->
                                        @forelse($justSignedContracts as $contract)
                                            <li class="list-group-item border-0 py-2">
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
                                            <li class="list-group-item border-0 text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có hợp đồng nào vừa ký.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Contracts Nearing Expiration -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-dark">
                                            <i class="fas fa-hourglass-half text-warning me-2"></i>
                                            Hợp đồng sắp hết hạn
                                        </h6>
                                        <a href="#" class="text-decoration-none small text-dark">Xem tất cả</a>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <!-- <li class="list-group-item border-0 py-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0 me-3">
                                                                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                                     style="width: 32px; height: 32px;">
                                                                                    <i class="fas fa-file-contract text-warning text-white" style="font-size: 12px;"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Lê Văn C</div>
                                                                                <small class="text-muted">P305 - Hết hạn: 15/07/2025</small>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                    <li class="list-group-item border-0 py-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="flex-shrink-0 me-3">
                                                                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                                     style="width: 32px; height: 32px;">
                                                                                    <i class="fas fa-file-contract text-warning text-white" style="font-size: 12px;"></i>
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Phạm Văn D</div>
                                                                                <small class="text-muted">P401 - Hết hạn: 20/07/2025</small>
                                                                            </div>
                                                                        </div>
                                                                    </li> -->
                                        @forelse($contracts as $contract)
                                            <li class="list-group-item border-0 py-2">
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
                                            <li class="list-group-item border-0 text-center text-muted py-4">
                                                <i class="fas fa-info-circle me-2"></i>Không có hợp đồng nào sắp hết hạn.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Expired Contracts -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-dark">
                                    <i class="fas fa-message text-danger me-2"></i>
                                    Tin nhắn mới
                                </h6>
                                <a href="{{ route('messages.index') }}" class="text-decoration-none small text-dark">Xem tất
                                    cả</a>
                            </div>
                        </div>
                        <div class="card-body p-0 fw-bold">
                            <ul class="list-group list-group-flush">
                                @forelse ($messages as $message)
                                    <li class="list-group-item border-0 py-2">
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
                                                    {{ $message->sender_name }}
                                                </div>
                                                <div class="text-dark" style="font-size: 13px;">
                                                    {{ $message->message ?? '[Không có nội dung]' }}
                                                </div>

                                                <small class="text-muted">
                                                    <span class="text-primary">
                                                        {{ \Carbon\Carbon::parse($message->created_at ?? now())->diffForHumans() }}
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item border-0 py-2 text-muted">
                                        Không có tin nhắn nào.
                                    </li>
                                @endforelse
                            </ul>

                        </div>

                    </div>

                    <!-- Check-in Section -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-sign-in-alt text-success me-2"></i>
                                Check-in Sắp Tới
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <!-- <li class="list-group-item border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                             style="width: 32px; height: 32px;">
                                                                            <i class="fas fa-calendar-plus text-success text-white" style="font-size: 12px;"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Nguyễn Văn A</div>
                                                                        <small class="text-muted">25/12/2024 - P101</small>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                             style="width: 32px; height: 32px;">
                                                                            <i class="fas fa-calendar-plus text-success text-white" style="font-size: 12px;"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Trần Thị B</div>
                                                                        <small class="text-muted">26/12/2024 - P202</small>
                                                                    </div>
                                                                </div>
                                                            </li> -->
                                @forelse($schedules as $schedule)
                                    <li class="list-group-item border-0 py-2">
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
                                    <li class="list-group-item border-0 text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Không có lịch check-in sắp tới.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Recently Renewed Contracts -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-dark">
                                    <i class="fas fa-file-contract text-info text-white me-2"></i>
                                    Yêu cầu gia hạn hợp đồng
                                </h6>
                                <a href="#" class="text-decoration-none small text-dark">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <!-- <li class="list-group-item border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                             style="width: 32px; height: 32px;">
                                                                            <i class="fas fa-file-contract text-info text-white" style="font-size: 12px;"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Nguyễn Thị G</div>
                                                                        <small class="text-muted">P102 - Gia hạn: 05/07/2025</small>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item border-0 py-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                                             style="width: 32px; height: 32px;">
                                                                            <i class="fas fa-file-contract text-info text-white" style="font-size: 12px;"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-semibold mb-0 text-dark" style="font-size: 14px;">Trần Văn H</div>
                                                                        <small class="text-muted">P303 - Gia hạn: 06/07/2025</small>
                                                                    </div>
                                                                </div>
                                                            </li> -->
                                @forelse($contractExtensions as $extension)
                                    <li class="list-group-item border-0 py-2">
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
                                    <li class="list-group-item border-0 text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Không có yêu cầu gia hạn hợp đồng nào.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
