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

        <!-- Quick Stats Cards với Icons Phù Hợp -->
        <div class="row g-4 mb-4">
            <!-- Doanh thu hôm nay - Icon tiền -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Doanh thu hôm nay</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ number_format(1250000, 0, ',', '.') }} VNĐ</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doanh thu tháng - Icon biểu đồ tăng -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Doanh thu tháng này</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ number_format(32500000, 0, ',', '.') }} VNĐ</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phòng đang thuê - Icon cửa mở -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-door-open fa-2x text-info"></i>

                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Phòng đang thuê</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ $roomsRentedCount }} / {{ $roomsCount }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phòng trống - Icon cửa đóng -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-door-closed fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Phòng trống</small>
                            <h5 class="mb-0 fw-semibold text-dark">10</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Người thuê hôm nay - Icon khách thuê -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-user-plus fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Khách thuê hôm nay</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ $countUsersToday }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Người thuê tháng này - Icon khách thuê -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-user fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Khách thuê tháng này</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ $countUsersThisMonth }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            Biểu đồ doanh thu theo tháng
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyRevenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-building text-info me-2"></i>
                            Phòng trống theo dãy
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <i class="fas fa-building text-success mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy A</small>
                                    <span class="badge bg-success">5</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <i class="fas fa-building text-success mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy B</small>
                                    <span class="badge bg-success">3</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                    <i class="fas fa-building text-warning mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy C</small>
                                    <span class="badge bg-warning">2</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                    <i class="fas fa-building text-danger mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy D</small>
                                    <span class="badge bg-danger">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="" class="card-footer text-center text-decoration-none py-2">Xem thêm</a>
                </div>
            </div>
        </div>
        <!-- Main Content Grid -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-xl-8">
                <!-- Notes Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gradient border-0"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">Ghi chú</h6>
                            <form action="{{ route('notes.index') }}" method="GET">
                                <button type="submit" class="btn btn-link text-decoration-none p-0 small">Xem tất
                                    cả</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @isset($notes)
                                @forelse($notes->take(3) as $note)
                                    <li class="list-group-item border-0 py-3" data-id-user="{{ $note->user_id }}"
                                        data-note-id="{{ $note->id }}">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <p class="mb-1">{{ $note->content }}</p>
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
                                    <li class="list-group-item border-0 text-center text-muted py-4">Chưa có ghi chú nào.</li>
                                @endforelse
                            @else
                                <li class="list-group-item border-0 text-center text-muted py-4">Chưa tải được ghi chú:
                                    {{ session('error') ?? 'Lỗi không xác định' }}</li>
                            @endisset
                        </ul>
                    </div>
                </div>

                <!-- Check-in/Check-out Section -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="fas fa-sign-in-alt text-success me-2"></i>
                                        Check-in Sắp Tới
                                    </h6>
                                    <a href="#" class="text-decoration-none small">Xem tất cả</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-plus text-success me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Nguyễn Văn A</h6>
                                                            <small class="text-muted">25/12/2024 - P101</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-plus text-success me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Trần Thị B</h6>
                                                            <small class="text-muted">26/12/2024 - P202</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-plus text-success me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Lê Văn C</h6>
                                                            <small class="text-muted">27/12/2024 - P305</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold">
                                        <i class="fas fa-sign-out-alt text-danger me-2"></i>
                                        Check-out Sắp Tới
                                    </h6>
                                    <a href="#" class="text-decoration-none small">Xem tất cả</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-minus text-danger me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Phạm Văn D</h6>
                                                            <small class="text-muted">24/12/2024 - P401</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-minus text-danger me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Hoàng Thị E</h6>
                                                            <small class="text-muted">25/12/2024 - P503</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-calendar-minus text-danger me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Đỗ Văn F</h6>
                                                            <small class="text-muted">26/12/2024 - P204</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repair Requests -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-tools text-primary me-2"></i>
                                Yêu cầu sửa chữa
                            </h6>
                            <a href="#" class="text-decoration-none small">Xem tất cả</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Mã YC</th>
                                        <th class="border-0 fw-semibold">Khách hàng</th>
                                        <th class="border-0 fw-semibold">Phòng</th>
                                        <th class="border-0 fw-semibold">Trạng thái</th>
                                        <th class="border-0 fw-semibold">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border-0 py-3">
                                            <i class="fas fa-wrench text-warning me-2"></i>
                                            REP-001
                                        </td>
                                        <td class="border-0 py-3">Nguyễn Văn A</td>
                                        <td class="border-0 py-3">P101</td>
                                        <td class="border-0 py-3">
                                            <span class="badge bg-opacity-20 text-warning">
                                                <i class="fas fa-clock me-1"></i>Đang xử lý
                                            </span>
                                        </td>
                                        <td class="border-0 py-3">
                                            <a class="btn btn-sm btn-outline-primary" href="#">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 py-3">
                                            <i class="fas fa-check text-success me-2"></i>
                                            REP-002
                                        </td>
                                        <td class="border-0 py-3">Trần Thị B</td>
                                        <td class="border-0 py-3">P202</td>
                                        <td class="border-0 py-3">
                                            <span class="badge bg-opacity-20 text-success">
                                                <i class="fas fa-check me-1"></i>Hoàn thành
                                            </span>
                                        </td>
                                        <td class="border-0 py-3">
                                            <a class="btn btn-sm btn-outline-primary" href="#">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 py-3">
                                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                            REP-003
                                        </td>
                                        <td class="border-0 py-3">Lê Văn C</td>
                                        <td class="border-0 py-3">P305</td>
                                        <td class="border-0 py-3">
                                            <span class="badge bg-opacity-20 text-danger">
                                                <i class="fas fa-times me-1"></i>Chưa xử lý
                                            </span>
                                        </td>
                                        <td class="border-0 py-3">
                                            <a class="btn btn-sm btn-outline-primary" href="#">
                                                <i class="fas fa-eye me-1"></i>Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-4">
                <!-- Notifications -->
                <div class="card border-0 shadow-sm mb-4">
                   <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-building text-info me-2"></i>
                            Phòng trống theo dãy
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <i class="fas fa-building text-success mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy A</small>
                                    <span class="badge bg-success">5</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <i class="fas fa-building text-success mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy B</small>
                                    <span class="badge bg-success">3</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                    <i class="fas fa-building text-warning mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy C</small>
                                    <span class="badge bg-warning">2</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                    <i class="fas fa-building text-danger mb-2 text-white"></i>
                                    <small class="mb-1 d-block text-white">Dãy D</small>
                                    <span class="badge bg-danger">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Tenants -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-users text-info me-2"></i>
                                Người thuê trọ
                            </h6>
                            <a href="#" class="text-decoration-none small">Xem tất cả</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-1">Nguyễn Văn A</div>
                                        <small class="text-muted">P101 - Đang thuê</small>
                                    </div>
                                    <span class="badge bg-opacity-20 text-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                </div>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-user-tie text-info"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-1">Trần Thị B</div>
                                        <small class="text-muted">P202 - Đang thuê</small>
                                    </div>
                                    <span class="badge bg-opacity-20 text-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                </div>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-user-clock text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold mb-1">Lê Văn C</div>
                                        <small class="text-muted">P305 - Sắp hết hạn</small>
                                    </div>
                                    <span class="badge bg-opacity-20 text-warning">
                                        <i class="fas fa-clock me-1"></i>Expiring
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Revenue Chart
        const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyRevenueChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: [18500000, 22000000, 19800000, 25600000, 28200000, 31000000, 29500000, 33200000, 27800000, 30500000, 32500000, 35200000],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                            borderDash: [5, 5]
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            callback: function (value) {
                                return new Intl.NumberFormat('vi-VN', {
                                    notation: 'compact',
                                    compactDisplay: 'short'
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection