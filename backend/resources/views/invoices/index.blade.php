@extends('layouts.app')

@section('title', 'Danh sách hóa đơn')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container-fluid py-5 px-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Tổng hóa đơn</h6>
                                <h4 class="mb-0 text-success">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-file-invoice fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #17a2b8 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Đã thanh toán</h6>
                                <h4 class="mb-0 text-info">{{ $stats['paid'] }}</h4>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Chưa thanh toán</h6>
                                <h4 class="mb-0 text-warning">{{ $stats['unpaid'] }}</h4>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Đã hoàn tiền</h6>
                                <h4 class="mb-0 text-danger">{{ $stats['overdue'] }}</h4>
                            </div>
                            <div class="text-danger">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #28a745, #20c997); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold">{{ __('Danh sách hóa đơn') }}
                        <span class="badge bg-light text-success ms-2">{{ $invoices->total() }} bản ghi</span>
                    </h6>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('invoices.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control shadow-sm" name="search"
                                   placeholder="Tìm kiếm mã hóa đơn..." value="{{ $filters['search'] }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="month">
                                <option value="">Tất cả tháng</option>
                                @foreach($months as $monthValue => $monthLabel)
                                    <option value="{{ $monthValue }}"
                                        {{ $filters['month'] == $monthValue ? 'selected' : '' }}>
                                        {{ $monthLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="year">
                                <option value="">Tất cả năm</option>
                                @foreach($years as $yearValue => $yearLabel)
                                    <option value="{{ $yearValue }}"
                                        {{ $filters['year'] == $yearValue ? 'selected' : '' }}>
                                        {{ $yearLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="status">
                                <option value="">Tất cả trạng thái</option>
                                @foreach($statuses as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}"
                                        {{ $filters['status'] == $statusValue ? 'selected' : '' }}>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Invoices Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-success">
                            <tr>
                                <th scope="col" style="width: 8%;" class="text-center">ID</th>
                                <th scope="col" style="width: 20%;" class="text-center">Mã hóa đơn</th>
                                <th scope="col" style="width: 15%;" class="text-center">Tổng tiền</th>
                                <th scope="col" style="width: 12%;" class="text-center">Trạng thái</th>
                                <th scope="col" style="width: 12%;" class="text-center">Tháng/Năm</th>
                                <th scope="col" style="width: 15%;" class="text-center">Ngày tạo</th>
                                <th scope="col" style="width: 18%;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr class="table-row">
                                    <td class="text-center">{{ $invoice->id }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">{{ $invoice->code }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = match($invoice->status) {
                                                'Đã trả' => 'success',
                                                'Chưa trả' => 'warning',
                                                'Quá hạn' => 'danger',
                                                'Đã hủy' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $invoice->status }}</span>
                                    </td>
                                    <td class="text-center">{{ $invoice->month }}/{{ $invoice->year }}</td>
                                    <td class="text-center">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick="showInvoiceDetail({{ $invoice->id }})">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Không có dữ liệu hóa đơn
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                    <div class="mt-4">
                        {{ $invoices->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Invoice Detail Modal -->
    <div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-labelledby="invoiceDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="invoiceDetailModalLabel">
                        <i class="fas fa-file-invoice me-2"></i>Chi tiết hóa đơn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="invoiceDetailContent">
                    <!-- Loading spinner -->
                    <div class="text-center py-4" id="loadingSpinner">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Đang tải thông tin...</p>
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

    <script>
        // Auto-submit form when filter changes
        document.addEventListener('DOMContentLoaded', function () {
            const filterSelects = document.querySelectorAll('select[name="month"], select[name="year"], select[name="status"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function () {
                    this.form.submit();
                });
            });
        });

        // Show invoice detail function
        function showInvoiceDetail(invoiceId) {
            const modal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
            const content = document.getElementById('invoiceDetailContent');
            const spinner = document.getElementById('loadingSpinner');

            // Reset modal content trước khi hiển thị
            content.innerHTML = `
                <div class="text-center py-4" id="loadingSpinner">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            `;

            // Show modal
            modal.show();

            // Fetch invoice detail
            fetch(`/invoices/${invoiceId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        content.innerHTML = generateInvoiceDetailHTML(data.data);
                    } else {
                        content.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Có lỗi xảy ra khi tải thông tin hóa đơn: ${error.message}
                        </div>
                    `;
                });
        }

        // Generate invoice detail HTML
        function generateInvoiceDetailHTML(invoice) {
            const statusClass = {
                'Đã trả': 'success',
                'Chưa trả': 'warning',
                'Quá hạn': 'danger',
                'Đã hủy': 'secondary'
            }[invoice.status] || 'secondary';

            return `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin hóa đơn</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Mã hóa đơn:</strong> ${invoice.code}</p>
                                <p><strong>Loại:</strong> ${invoice.type}</p>
                                <p><strong>Kỳ:</strong> ${invoice.month}/${invoice.year}</p>
                                <p><strong>Trạng thái:</strong> <span class="badge bg-${statusClass}">${invoice.status}</span></p>
                                <p><strong>Ngày tạo:</strong> ${invoice.created_at}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Tên:</strong> ${invoice.customer.name}</p>
                                <p><strong>Email:</strong> ${invoice.customer.email}</p>
                                <p><strong>Điện thoại:</strong> ${invoice.customer.phone}</p>
                                <p><strong>Phòng:</strong> ${invoice.room.name}</p>
                                <p><strong>Nhà trọ:</strong> ${invoice.room.motel_name}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Chỉ số điện nước</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Điện:</strong> ${invoice.meter_reading.electricity_kwh} kWh</p>
                                <p><strong>Nước:</strong> ${invoice.meter_reading.water_m3} m³</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Chi tiết chi phí</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Tiền điện:</strong> ${invoice.fees.electricity_fee} VND</p>
                                <p><strong>Tiền nước:</strong> ${invoice.fees.water_fee} VND</p>
                                <p><strong>Phí giữ xe:</strong> ${invoice.fees.parking_fee} VND</p>
                                <p><strong>Phí rác:</strong> ${invoice.fees.junk_fee} VND</p>
                                <p><strong>Phí internet:</strong> ${invoice.fees.internet_fee} VND</p>
                                <p><strong>Phí dịch vụ:</strong> ${invoice.fees.service_fee} VND</p>
                                <hr>
                                <p class="h5 text-success">Tổng cộng: ${invoice.fees.total_amount} VND</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Optional: Add event listener để reset modal khi đóng
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('invoiceDetailModal');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function () {
                    // Reset nội dung modal khi đóng
                    const content = document.getElementById('invoiceDetailContent');
                    content.innerHTML = `
                        <div class="text-center py-4" id="loadingSpinner">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Đang tải thông tin...</p>
                        </div>
                    `;
                });
            }
        });
    </script>
@endsection
