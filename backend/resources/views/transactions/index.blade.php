@extends('layouts.app')

@section('title', 'Danh sách giao dịch')

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
        <!-- Filter Info -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Thống kê hiện tại:</strong>
                    @if(!empty($filters['month']) || !empty($filters['year']))
                        @if(!empty($filters['month']) && !empty($filters['year']))
                            Tháng {{ $filters['month'] }}/{{ $filters['year'] }}
                        @elseif(!empty($filters['month']))
                            Tháng {{ $filters['month'] }} (tất cả năm)
                        @elseif(!empty($filters['year']))
                            Năm {{ $filters['year'] }} (tất cả tháng)
                        @endif
                    @else
                        Tất cả giao dịch
                    @endif
                    @if(!empty($filters['transfer_type']))
                        | Loại giao dịch: {{ $filters['transfer_type'] == 'in' ? 'Tiền vào' : 'Tiền ra' }}
                    @endif
                    @if(!empty($filters['search']))
                        | Tìm kiếm: "{{ $filters['search'] }}"
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Tổng giao dịch</h6>
                                <h4 class="mb-0 text-success">{{ $stats['total'] ?? 0 }}</h4>
                                <small class="text-muted">{{ number_format($stats['total_amount'] ?? 0) }} VND</small>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-exchange-alt fa-2x"></i>
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
                                <h6 class="text-muted mb-1">Tiền vào (IN)</h6>
                                <h4 class="mb-0 text-info">{{ $stats['in'] ?? 0 }}</h4>
                                <small class="text-muted">{{ number_format($stats['in_amount'] ?? 0) }} VND</small>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-arrow-down fa-2x"></i>
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
                                <h6 class="text-muted mb-1">Tiền ra (OUT)</h6>
                                <h4 class="mb-0 text-warning">{{ $stats['out'] ?? 0 }}</h4>
                                <small class="text-muted">{{ number_format($stats['out_amount'] ?? 0) }} VND</small>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-arrow-up fa-2x"></i>
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
                                <h6 class="text-muted mb-1">Số dư</h6>
                                <h4 class="mb-0 text-danger">{{ number_format(($stats['balance'] ?? 0), 0, ',', '.') }}</h4>
                            </div>
                            <div class="text-danger">
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #0056b3); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold">{{ __('Danh sách giao dịch') }}
                        <span class="badge bg-light text-primary ms-2">{{ $transactions->total() ?? 0 }} bản ghi</span>
                    </h6>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('transactions.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control shadow-sm" name="search"
                                placeholder="Tìm kiếm mã giao dịch hoặc mã tham chiếu..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="month">
                                <option value="">Tất cả tháng</option>
                                @foreach($months as $monthValue => $monthLabel)
                                    <option value="{{ $monthValue }}"
                                        {{ ($filters['month'] ?? '') == $monthValue ? 'selected' : '' }}>
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
                                        {{ ($filters['year'] ?? '') == $yearValue ? 'selected' : '' }}>
                                        {{ $yearLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="transfer_type">
                                <option value="">Tất cả loại giao dịch</option>
                                @foreach($transferTypes as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}"
                                        {{ ($filters['transfer_type'] ?? '') == $typeValue ? 'selected' : '' }}>
                                        {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col" style="width: 3%;" class="text-center">STT</th>
                                <th scope="col" style="width: 15%;" class="text-center">Mã hóa đơn</th>
                                <th scope="col" style="width: 13%;" class="text-center">Số tiền</th>
                                <th scope="col" style="width: 12%;" class="text-center">Mã tham chiếu</th>
                                <th scope="col" style="width: 16%;" class="text-center">Thời gian giao dịch</th>
                                <th scope="col" style="width: 8%;" class="text-center">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions ?? [] as $index => $transaction)
                                <tr class="table-row">
                                    <td class="text-center">{{ ($transactions->currentPage() - 1) * $transactions->perPage() + $index + 1 }}</td>
                                    <td class="text-center">
                                        @if($transaction->invoice_id)
                                            <span class="fw-bold text-primary">{{ $transaction->invoice->code ?? 'N/A' }}</span>
                                        @else
                                            <span class="text-muted">{{ $transaction->refund_request_id ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold {{ $transaction->transfer_type == 'in' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->transfer_type == 'in' ? '+' : '-' }}{{ number_format($transaction->transfer_amount ?? 0, 0, ',', '.') }} VND
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-secondary">{{ $transaction->reference_code ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick="showTransactionDetail({{ $transaction->id }})" title="Xem chi tiết giao dịch">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Không có dữ liệu giao dịch
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-labelledby="transactionDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="transactionDetailModalLabel">
                        <i class="fas fa-exchange-alt me-2"></i>Chi tiết giao dịch
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="transactionDetailContent">
                    <!-- Loading spinner -->
                    <div class="text-center py-4" id="loadingSpinner">
                        <div class="spinner-border text-primary" role="status">
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
            const filterSelects = document.querySelectorAll('select[name="month"], select[name="year"], select[name="transfer_type"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function () {
                    this.form.submit();
                });
            });
        });

        // Show transaction detail function
        function showTransactionDetail(transactionId) {
            const modal = new bootstrap.Modal(document.getElementById('transactionDetailModal'));
            const content = document.getElementById('transactionDetailContent');

            // Reset modal content trước khi hiển thị
            content.innerHTML = `
                <div class="text-center py-4" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            `;

            // Show modal
            modal.show();

            // Fetch transaction detail
            fetch(`/transactions/${transactionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        content.innerHTML = generateTransactionDetailHTML(data.data);
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
                            Có lỗi xảy ra khi tải thông tin giao dịch: ${error.message}
                        </div>
                    `;
                });
        }

        // Generate transaction detail HTML
        function generateTransactionDetailHTML(transaction) {
            const typeClass = transaction.transfer_type == 'in' ? 'success' : 'warning';
            const typeIcon = transaction.transfer_type == 'in' ? 'arrow-down' : 'arrow-up';
            const amountColor = transaction.transfer_type == 'in' ? 'text-success' : 'text-danger';
            const amountPrefix = transaction.transfer_type == 'in' ? '+' : '-';

            return `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin giao dịch</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> ${transaction.id}</p>
                                <p><strong>Nội dung:</strong> ${transaction.content || 'N/A'}</p>
                                <p><strong>Loại giao dịch:</strong>
                                    <span class="badge bg-${typeClass}">
                                        <i class="fas fa-${typeIcon} me-1"></i>${transaction.transfer_type}
                                    </span>
                                </p>
                                <p><strong>Số tiền:</strong>
                                    <span class="fw-bold ${amountColor}">${amountPrefix}${transaction.amount} VND</span>
                                </p>
                                <p><strong>Mã tham chiếu:</strong> ${transaction.reference_code || 'N/A'}</p>
                                <p><strong>Ngày tạo:</strong> ${transaction.created_at}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Thông tin hóa đơn</h6>
                            </div>
                            <div class="card-body">
                                ${transaction.invoice ? `
                                    <p><strong>Mã hóa đơn:</strong> ${transaction.invoice.code}</p>
                                    <p><strong>Trạng thái:</strong> ${transaction.invoice.status}</p>
                                    <p><strong>Tổng tiền:</strong> ${transaction.invoice.total_amount} VND</p>
                                    <p><strong>Tháng/Năm:</strong> ${transaction.invoice.month}/${transaction.invoice.year}</p>
                                ` : `
                                    <p class="text-muted"><i class="fas fa-info-circle me-2"></i>Không liên kết với hóa đơn</p>
                                `}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Optional: Add event listener để reset modal khi đóng
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('transactionDetailModal');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function () {
                    // Reset nội dung modal khi đóng
                    const content = document.getElementById('transactionDetailContent');
                    content.innerHTML = `
                        <div class="text-center py-4" id="loadingSpinner">
                            <div class="spinner-border text-primary" role="status">
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
