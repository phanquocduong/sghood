@extends('layouts.app')

@section('title', 'Lịch sử chỉ số điện nước')

@section('content')
@php
    // Xác định đang lọc hay không nếu controller không truyền biến
    $isFiltering = ($isFiltering ?? null) ?? request()->hasAny([
        'search','month','year','date_from','date_to','sortOption'
    ]);
@endphp


    <div class="container-fluid">
        <!-- Header Section -->
         <a href="{{ route('meter_readings.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left me-2"></i>
            Quay lại
         </a>
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Lịch sử chỉ số điện nước
                        </h2>
                        <p class="text-muted mb-0">Theo dõi và quản lý chỉ số điện nước các phòng</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Bộ lọc
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3" action="{{ route('meter_readings.filter') }}" method="GET">
                            <!-- Tìm kiếm theo tên -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">
                                    <i class="fas fa-search me-1"></i>Tìm kiếm
                                </label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="Tìm theo tên phòng, tên nhà trọ..." value="{{ request('search') }}"
                                    autocomplete="off">
                            </div>

                            <div class="col-md-2">
                                <label for="month_filter" class="form-label">Tháng</label>
                                <select class="form-select" id="month_filter" name="month">
                                    <option value="">Tất cả</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                            Tháng {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="year_filter" class="form-label">Năm</label>
                                <select class="form-select" id="year_filter" name="year">
                                    <option value="">Tất cả</option>
                                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-4" style="margin-top: 48px;">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-primary" id="searchButton">
                                        <i class="fas fa-search me-1"></i>
                                        Tìm kiếm
                                    </button>
                                    <a href="{{ route('meter_readings.history') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-1"></i>
                                        Xóa bộ lọc
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel me-1"></i>
                                        Xuất Excel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ $totalReadings ?? 0 }}</h4>
                                <p class="mb-0">Tổng số lần ghi</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ $roomsWithReadings ?? 0 }}</h4>
                                <p class="mb-0">Phòng đã ghi</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-home fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ number_format($totalElectricity ?? 0, 0) }}</h4>
                                <p class="mb-0">Tổng điện (kWh)</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-bolt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-1">{{ number_format($totalWater ?? 0, 0) }}</h4>
                                <p class="mb-0">Tổng nước (m³)</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tint fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Danh sách chỉ số điện nước
                            </h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-success btn-sm" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-1"></i>
                                    Xuất Excel
                                </button>
                                <div class="dropdown">
                                    <a class="btn btn-outline-success " href="#" onclick="printTable()">
                                                <i class="fas fa-print me-2"></i>In danh sách
                                            </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="displayResults">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">STT</th>
                                            <th class="text-center" style="width: 120px;">
                                                <i class="fas fa-home me-1"></i>Phòng
                                            </th>
                                            <th class="text-center" style="width: 100px;">
                                                <i class="fas fa-calendar me-1"></i>Tháng/Năm
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                <i class="fas fa-bolt me-1 text-warning"></i>Điện (kWh)
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                <i class="fas fa-tint me-1 text-info"></i>Nước (m³)
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                <i class="fas fa-clock me-1"></i>Ngày ghi
                                            </th>
                                            <th class="text-center" style="width: 120px;">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($meterReadings as $index => $meterReading)
                                            <tr class="table-row-hover">
                                                <td class="text-center fw-bold">
                                                    {{ ($meterReadings->currentPage() - 1) * $meterReadings->perPage() + $index + 1 }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-semibold">{{ $meterReading->room->name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary fs-6">
                                                        {{ str_pad($meterReading->month, 2, '0', STR_PAD_LEFT) }}/{{ $meterReading->year }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="">
                                                        <span class="fw-bold text-warning m-1">
                                                            {{ number_format($meterReading->electricity_kwh, 0) }}
                                                        </span>
                                                        <span class="text-muted"> kWh</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="">
                                                        <span class="fw-bold text-info m-1">
                                                            {{ number_format($meterReading->water_m3, 0) }}
                                                        </span>
                                                        <small class="text-muted">m³</small>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold">{{ $meterReading->created_at->format('d/m/Y') }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>
                                                        Đã nhập
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                        <h5 class="text-muted">Không có dữ liệu</h5>
                                                        <p class="text-muted mb-3">Chưa có chỉ số điện nước nào được ghi nhận</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($meterReadings->hasPages())
                            <div class="mt-4 px-3">
                                @if($isFiltering)
                                    {{ $meterReadings->appends(request()->query())->links('pagination::bootstrap-5') }}
                                @else
                                    {{ $meterReadings->links('pagination::bootstrap-5') }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Đang xử lý...</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-row-hover:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .empty-state { padding: 2rem; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .card-header { border-bottom: 1px solid #e9ecef; background-color: #f8f9fa; }
        .badge { font-size: 0.75rem; }
        .table th { border-top: none; font-weight: 600; font-size: 0.875rem; }
        .btn-sm { font-size: 0.8rem; }
        .table-responsive { border-radius: 0.375rem; }
        .opacity-75 { opacity: 0.75; }
        .search-hint { font-size: 0.85rem; color: #6c757d; margin-top: 0.25rem; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Elements
            const filterForm   = document.getElementById('filterForm');
            const searchInput  = document.getElementById('search');
            const searchButton = document.getElementById('searchButton');
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));

            // ❌ Không auto-submit: chặn submit mặc định của form
            filterForm.addEventListener('submit', function(e) { e.preventDefault(); });

            // ❌ Không submit khi nhấn Enter ở bất kỳ input nào trong form
            filterForm.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                }
            });

            // 👉 Chỉ submit khi người dùng bấm nút "Tìm kiếm"
            searchButton.addEventListener('click', handleSearch);

            function handleSearch() {
                // Show loading
                loadingModal.show();

                // Disable button + spinner
                searchButton.disabled = true;
                const originalText = searchButton.innerHTML;
                searchButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang tìm...';

                const formData = new FormData(filterForm);
                const query = new URLSearchParams(formData).toString();
                const url = filterForm.getAttribute('action') + (query ? `?${query}` : '');

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        // Cập nhật bảng
                        document.getElementById('displayResults').innerHTML = data.html;

                        // Cập nhật thẻ thống kê (nếu trả về)
                        if (data.statistics) updateStatisticsCards(data.statistics);

                        // Thông báo
                        if (data.search_summary) {
                            showNotification(
                                `Tìm thấy ${data.search_summary.total_found} kết quả cho "${data.search_summary.term}"`,
                                'success'
                            );
                        } else if (data.total_count !== undefined) {
                            showNotification(`Hiển thị ${data.total_count} kết quả`, 'info');
                        }

                        // Cập nhật URL (không reload)
                        const newUrl = new URL(window.location);
                        newUrl.search = query;
                        window.history.pushState({}, '', newUrl);
                    } else {
                        showNotification(data.error || 'Có lỗi xảy ra khi tìm kiếm', 'error');
                    }
                })
                .catch(err => {
                    console.error('Search error:', err);
                    showNotification('Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại.', 'error');
                })
                .finally(() => {
                    loadingModal.hide();
                    searchButton.disabled = false;
                    searchButton.innerHTML = originalText;
                });
            }

            // Cập nhật các card thống kê
            function updateStatisticsCards(statistics) {
                const cards = document.querySelectorAll('.card h4');
                if (cards.length >= 4) {
                    cards[0].textContent = statistics.totalReadings || 0;
                    cards[1].textContent = statistics.roomsWithReadings || 0;
                    cards[2].textContent = new Intl.NumberFormat('vi-VN').format(statistics.totalElectricity || 0);
                    cards[3].textContent = new Intl.NumberFormat('vi-VN').format(statistics.totalWater || 0);
                }
            }

            // Gợi ý tìm kiếm (static)
            (function initSearchSuggestions() {
                searchInput.setAttribute('list', 'search-suggestions');
                const datalist = document.createElement('datalist');
                datalist.id = 'search-suggestions';
                datalist.innerHTML = `
                    <option value="Phòng 101">
                    <option value="Phòng 201">
                    <option value="Phòng A01">
                    <option value="Phòng B02">
                    <option value="Nhà trọ">
                `;
                document.body.appendChild(datalist);
            })();

            // Feedback UI khi focus/blur
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('border-primary');
            });
            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('border-primary');
            });

            console.log('History page initialized - submit chỉ khi bấm nút Tìm kiếm');
        });

        // Export Excel
        function exportToExcel() {
            try {
                const params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
                const exportUrl = "{{ route('meter_readings.export') }}?" + params.toString();

                showNotification('Đang chuẩn bị file Excel...', 'info');

                const form = document.createElement('form');
                form.method = 'GET';
                form.action = exportUrl;
                form.style.display = 'none';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

                setTimeout(() => {
                    showNotification('Tải xuống file Excel thành công!', 'success');
                }, 2000);

            } catch (error) {
                console.error('Export error:', error);
                showNotification('Có lỗi xảy ra khi xuất Excel', 'error');
            }
        }

        // In bảng
        function printTable() {
            const printContent = document.getElementById('displayResults').innerHTML;
            const originalContent = document.body.innerHTML;
            const searchTerm = document.getElementById('search').value;

            document.body.innerHTML = `
                <div class="container-fluid">
                    <h2 class="text-center mb-4">LỊCH SỬ CHỈ SỐ ĐIỆN NƯỚC</h2>
                    <p class="text-center text-muted mb-4">
                        Ngày in: ${new Date().toLocaleDateString('vi-VN')}
                        ${searchTerm ? `<br>Từ khóa tìm kiếm: "${searchTerm}"` : ''}
                    </p>
                    ${printContent}
                </div>
            `;

            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }

        // Thông báo (global)
        function showNotification(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            
            const icon = type === 'success' ? 'fas fa-check-circle' : 
                        type === 'error' ? 'fas fa-exclamation-triangle' : 
                        'fas fa-info-circle';
            
            toast.innerHTML = `
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }
    </script>
@endsection
