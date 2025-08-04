@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-dark fw-semibold mb-1">Thống kê hệ thống</h4>
                <p class="text-muted small mb-0">Quản lý trọ - Cập nhật {{ date('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Quick Stats Cards - Reorganized into one row -->
        <div class="row g-4 mb-4">
            <!-- Doanh thu hôm nay -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Doanh thu hôm nay</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ number_format($todayRevenue, 0, ',', '.') }} VNĐ</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doanh thu tháng -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Doanh thu tháng này</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ number_format($monthRevenue, 0, ',', '.') }} VNĐ</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phòng đang thuê -->
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

            <!-- Phòng trống -->
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-home fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted mb-1 d-block">Phòng trống</small>
                            <h5 class="mb-0 fw-semibold text-dark">{{ $roomsCount - $roomsRentedCount }} phòng</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second row of stats -->
        <div class="row g-4 mb-4">
            <!-- Người thuê hôm nay -->
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

            <!-- Người thuê tháng này -->
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

        <!-- Main Content Grid - Restructured -->
        <div class="row g-4">
            <!-- Left Column - Charts and Stats -->
            <div class="col-xl-8">
                <!-- Revenue Chart -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            Biểu đồ doanh thu theo tháng
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyRevenueChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Available Rooms by Motel -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-building text-info me-2"></i>
                            Phòng trống theo dãy
                        </h6>
                        <span class="badge bg-primary">Tổng phòng trống: {{ $roomsCount - $roomsRentedCount }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($availableRoomsByMotel as $index => $motel)
                                @php
                                    $available = $motel->available_rooms;
                                    $color = 'danger';
                                    if ($available > 4) {
                                        $color = 'success';
                                    } elseif ($available > 0) {
                                        $color = 'warning';
                                    }
                                @endphp

                                <div class="col-6 col-md-3 {{ $index >= 8 ? 'd-none more-motel' : '' }}">
                                    <div class="text-center p-3 bg-{{ $color }} bg-opacity-10 rounded">
                                        <i class="fas fa-building text-{{ $color }} mb-2"></i>
                                        <small class="mb-1 d-block" style="color: white;"><strong>{{ $motel->motel->name }}</strong></small>
                                        <span class="badge bg-{{ $color }}">{{ $available }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (count($availableRoomsByMotel) > 8)
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-primary" onclick="toggleMotels(this)">Xem thêm</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Tenant Information -->
            <div class="col-xl-4">
                <!-- Card for Tenant Overview -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-users text-primary me-2"></i>
                            Tình trạng người thuê
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <!-- Đang thuê -->
                        <div class="p-3 border-bottom">
                            <h6 class="text-primary fw-bold">
                                <i class="fas fa-check-circle me-2"></i>
                                Đang thuê (Còn hạn)
                            </h6>
                            <ul class="list-group list-group-flush">
                                @forelse($currentTenants as $index => $tenant)
                                    <li class="list-group-item border-0 py-2 {{ $index >= 3 ? 'd-none extra-current' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="fas fa-user-circle text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold mb-0">{{ $tenant->user->name }}</div>
                                                <small class="text-muted">{{ $tenant->room->name }}</small>
                                            </div>
                                            <span class="badge bg-success">Còn hạn</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item border-0 py-2 text-muted text-center">Không có người thuê</li>
                                @endforelse

                                @if (count($currentTenants) > 3)
                                    <li class="list-group-item border-0 py-1 text-center">
                                        <!-- <button class="btn btn-sm btn-outline-primary" onclick="toggleTenants('extra-current', this)">
                                            Xem tất cả
                                        </button> -->
                                        <a href="{{ url('/contracts') }}?querySearch=&status=Hoạt%20động&sort=desc" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <!-- Sắp hết hạn -->
                        <div class="p-3 border-bottom">
                            <h6 class="text-warning fw-bold">
                                <i class="fas fa-clock me-2"></i>
                                Sắp hết hạn (≤ {{ $isNearExpiration }} ngày)
                            </h6>
                            <ul class="list-group list-group-flush">
                                @forelse($expiringTenants as $index => $tenant)
                                    <li class="list-group-item border-0 py-2 {{ $index >= 3 ? 'd-none extra-expiring' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="fas fa-user-clock text-warning"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold mb-0">{{ $tenant->user->name }}</div>
                                                <small class="text-muted">{{ $tenant->room->name }}</small>
                                            </div>
                                            <span class="badge bg-warning text-dark">Sắp hết hạn</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item border-0 py-2 text-muted text-center">Không có hợp đồng sắp hết hạn</li>
                                @endforelse

                                @if (count($expiringTenants) > 3)
                                    <li class="list-group-item border-0 py-1 text-center">
                                        <button class="btn btn-sm btn-outline-warning" onclick="toggleTenants('extra-expiring', this)">
                                            Xem tất cả
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <!-- Đã hết hạn -->
                        <div class="p-3">
                            <h6 class="text-danger fw-bold">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Đã hết hạn
                            </h6>
                            <ul class="list-group list-group-flush">
                                @forelse($expiredTenants as $index => $tenant)
                                    <li class="list-group-item border-0 py-2 {{ $index >= 3 ? 'd-none extra-expired' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="fas fa-user-times text-danger"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold mb-0">{{ $tenant->user->name }}</div>
                                                <small class="text-muted">{{ $tenant->room->name }}</small>
                                            </div>
                                            <span class="badge bg-danger">Hết hạn</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item border-0 py-2 text-muted text-center">Không có hợp đồng đã hết hạn</li>
                                @endforelse

                                @if (count($expiredTenants) > 3)
                                    <li class="list-group-item border-0 py-1 text-center">
                                        <!-- <button class="btn btn-sm btn-outline-danger" onclick="toggleTenants('extra-expired', this)">
                                            Xem tất cả
                                        </button> -->

                                          <a href="{{ url('/contracts') }}?querySearch=&status=Kết%20thúc&sort=desc" class="btn btn-sm btn-outline-danger">Xem tất cả</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
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
                    data: [18500000, 22000000, 19800000, 25600000, 28200000, 31000000, 29500000, 33200000,
                        27800000, 30500000, 32500000, 35200000
                    ],
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
                            callback: function(value) {
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

        // Toggle functions
        function toggleMotels(btn) {
            const hiddenItems = document.querySelectorAll('.more-motel');
            hiddenItems.forEach(el => el.classList.toggle('d-none'));
            btn.textContent = btn.textContent === 'Xem thêm' ? 'Thu gọn' : 'Xem thêm';
        }

        function toggleTenants(className, btn) {
            const items = document.querySelectorAll('.' + className);
            items.forEach(el => el.classList.toggle('d-none'));
            btn.innerText = btn.innerText === 'Ẩn bớt' ? 'Xem tất cả' : 'Ẩn bớt';
        }
    </script>
@endsection
