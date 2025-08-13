@extends('layouts.app')

@section('title', 'Quản lý Yêu Cầu Sửa Chữa')

@section('content')
    @foreach (['success', 'error'] as $msg)
        @if (session($msg))
            <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }} alert-dismissible fade show animate__animated animate__fadeIn shadow-sm"
                role="alert">
                <i class="fas fa-{{ $msg == 'success' ? 'check-circle' : 'exclamation-circle' }} me-2"></i>
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach
    <div class="container-fluid py-4">
        
        <!-- Header -->
        <div class="card-header bg-gradient text-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center"
            style="background: linear-gradient(90deg, #6a11cb, #2575fc);">

            <h4 class="mb-2 mb-sm-0 fw-bold w-100 text-start text-sm-start">
                <i class="fas fa-tools me-2"></i>Quản lý Yêu Cầu Sửa Chữa
            </h4>
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                Số yêu cầu: {{ $repairRequests->total() }}
            </span>
        </div>

        <!-- Search Form -->
        <form action="{{ route('repair_requests.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <input type="text" class="form-control" name="querySearch" placeholder="Tìm theo tiêu đề, mô tả, ghi chú..."
                    value="{{ request('querySearch') }}">
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" name="status">
                    <option value="">Đang thực hiện</option>
                    @foreach (['Chờ xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Huỷ bỏ'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" name="sort_by">
                    <option value="">Sắp xếp theo</option>
                    <option value="created_at_desc" {{ request('sort_by') == 'created_at_desc' ? 'selected' : '' }}>Mới nhất
                    </option>
                    <option value="created_at_asc" {{ request('sort_by') == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất
                    </option>
                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Theo trạng thái</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Tìm
                </button>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">STT</th>
                        <th style="width: 150px;">Người thuê</th>
                        <th style="width: 120px;">Phòng</th>
                        <th style="width: 250px;">Tiêu Đề</th>
                        <th style="width: 150px;">Ghi Chú</th>
                        <th style="width: 150px;">Trạng Thái</th>
                        <th style="width: 120px;">Ngày Sửa</th>
                        <th style="width: 200px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repairRequests as $repair)
                        <tr>
                            <td class="text-center">
                                {{ ($repairRequests->currentPage() - 1) * $repairRequests->perPage() + $loop->iteration }}
                            </td>
                            <td>{{ $repair->contract->user->name ?? 'N/A' }}</td>
                            <td>{{ $repair->contract->room->name ?? 'N/A' }}</td>
                            <td>
                                <div class="repair-title">
                                    {{ $repair->title ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="repair-note d-flex align-items-center">
                                    <span
                                        class="note-text-{{ $repair->id }}">{{ $repair->note ?? 'Chưa có ghi chú nào' }}</span>
                                    <button type="button" class="btn btn-link p-0 ms-2 edit-note-btn"
                                        data-repair-id="{{ $repair->id }}" data-current-note="{{ $repair->note ?? '' }}"
                                        title="{{ $repair->note ? 'Chỉnh sửa ghi chú: ' . $repair->note : 'Thêm ghi chú' }}">
                                        <i
                                            class="fas {{ $repair->note ? 'fa-edit text-primary' : 'fa-plus-circle text-muted' }}"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match ($repair->status) {
                                        'Đang thực hiện' => 'warning',
                                        'Huỷ bỏ' => 'danger',
                                        'Hoàn thành' => 'success',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} px-3 py-2">{{ $repair->status }}</span>
                            </td>
                            <td class="text-center">
                                {{ $repair->repaired_at ? \Carbon\Carbon::parse($repair->repaired_at)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('repair_requests.show', $repair->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                    </a>
                                    @php
                                        $isFinal = in_array($repair->status, ['Hoàn thành', 'Huỷ bỏ']);
                                        $options = match ($repair->status) {
                                            'Chờ xác nhận' => ['Chờ xác nhận', 'Đang thực hiện', 'Huỷ bỏ'],
                                            'Đang thực hiện' => ['Đang thực hiện', 'Hoàn thành', 'Huỷ bỏ'],
                                            default => [$repair->status],
                                        };
                                    @endphp
                                    @if (!$isFinal)
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                data-original-value="{{ $repair->status }}" data-repair-id="{{ $repair->id }}">
                                                Cập nhật trạng thái
                                            </button>
                                            <form id="status-form-{{ $repair->id }}"
                                                action="{{ route('repairs.updateStatus', $repair->id) }}" method="POST"
                                                class="d-none">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="{{ $repair->status }}">
                                            </form>
                                            <ul class="dropdown-menu">
                                                @foreach ($options as $option)
                                                    <li>
                                                        <a class="dropdown-item status-option" href="#" data-status="{{ $option }}"
                                                            data-repair-id="{{ $repair->id }}">
                                                            {{ $option }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2">{{ $repair->status }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Không có yêu cầu sửa chữa nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $repairRequests->links('vendor.pagination.custom') }}
        </div>
    </div>

    <!-- Edit Note Modal -->
    <div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editNoteModalLabel">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa ghi chú
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="editNoteForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="noteContent" class="form-label fw-bold">Ghi chú:</label>
                            <textarea class="form-control" id="noteContent" name="note" rows="4"
                                placeholder="Nhập ghi chú cho yêu cầu sửa chữa..."></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ghi chú sẽ giúp theo dõi tiến trình sửa chữa tốt hơn.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background: #343a40 !important;
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 20px;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .repair-title {
            display: flex;
            align-items: center;
        }

        .edit-note-btn {
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .edit-note-btn:hover {
            transform: scale(1.1);
        }

        /* Modal styling */
        #editNoteModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        #editNoteModal .modal-header {
            border-radius: 12px 12px 0 0;
            border-bottom: none;
        }

        #editNoteModal .form-control {
            border-radius: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table {
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .action-icon {
                padding: 6px 8px;
                border-radius: 50%;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center
            }

            .action-icon i {
                margin: 0 !important;
                font-size: 14px;
            }

            .card-header .btn {
                font-size: 14px;
                padding: 6px 8px;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Edit note modal functionality
            const editNoteModal = new bootstrap.Modal(document.getElementById('editNoteModal'));
            const editNoteForm = document.getElementById('editNoteForm');
            const noteContentTextarea = document.getElementById('noteContent');

            // Handle edit note button clicks
            document.addEventListener('click', function (e) {
                const editBtn = e.target.closest('.edit-note-btn');
                if (editBtn) {
                    e.preventDefault();

                    const repairId = editBtn.dataset.repairId;
                    const currentNote = editBtn.dataset.currentNote || '';

                    editNoteForm.action = `{{ url('repair-requests') }}/${repairId}/note`;
                    noteContentTextarea.value = currentNote;
                    editNoteModal.show();

                    setTimeout(() => {
                        noteContentTextarea.focus();
                    }, 500);
                }
            });

            // Handle form submission
            editNoteForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...';
                submitBtn.disabled = true;

                const formData = new FormData(this);
                formData.append('_method', 'PUT');

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        console.log('Trạng thái response:', response.status);
                        console.log('URL request:', this.action);

                        if (!response.ok) {
                            return response.text().then(text => {
                                console.error('Nội dung response:', text);
                                throw new Error(`Lỗi HTTP! status: ${response.status}`);
                            });
                        }

                        return response.json();
                    })
                    .then(data => {
                        console.log('Dữ liệu response:', data);

                        if (data.success) {
                            const actionParts = this.action.split('/');
                            const repairId = actionParts[actionParts.length - 2];

                            const noteTextElement = document.querySelector(`.note-text-${repairId}`);
                            if (noteTextElement) {
                                noteTextElement.textContent = noteContentTextarea.value.trim() || 'Chưa có ghi chú nào';
                            }

                            const editBtn = document.querySelector(`[data-repair-id="${repairId}"]`);
                            if (editBtn) {
                                const icon = editBtn.querySelector('i');
                                if (icon) {
                                    if (noteContentTextarea.value.trim()) {
                                        icon.className = 'fas fa-edit text-primary';
                                        editBtn.setAttribute('title', `Chỉnh sửa ghi chú: ${noteContentTextarea.value}`);
                                    } else {
                                        icon.className = 'fas fa-plus-circle text-muted';
                                        editBtn.setAttribute('title', 'Thêm ghi chú');
                                    }
                                }
                                editBtn.dataset.currentNote = noteContentTextarea.value;
                            }

                            editNoteModal.hide();
                            showAlert('success', data.message || 'Cập nhật ghi chú thành công!');
                        } else {
                            showAlert('error', data.message || 'Có lỗi xảy ra khi cập nhật ghi chú');
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi fetch:', error);
                        showAlert('error', `Có lỗi xảy ra khi cập nhật ghi chú: ${error.message}`);
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });

            // ✅ Sửa hàm showAlert để chèn đúng vị trí
            function showAlert(type, message) {
                // Xóa thông báo cũ nếu có
                const existingAlert = document.querySelector('.alert:not(.alert-dismissible)');
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertHtml = `
                        <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show animate__animated animate__fadeIn mb-3" role="alert">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                // ✅ Chèn thông báo ở đầu body content, trước container
                const contentSection = document.querySelector('#app .content') || document.querySelector('main') || document.body;

                // ✅ Nếu không tìm thấy, chèn vào đầu container-fluid
                const targetElement = contentSection || document.querySelector('.container-fluid');

                if (targetElement) {
                    targetElement.insertAdjacentHTML('afterbegin', alertHtml);
                } else {
                    // Fallback: chèn vào đầu body
                    document.body.insertAdjacentHTML('afterbegin', alertHtml);
                }

                // Tự động ẩn sau 5 giây
                setTimeout(() => {
                    const alert = document.querySelector('.alert.alert-dismissible');
                    if (alert) {
                        // Sử dụng Bootstrap alert hide
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);

                // ✅ Scroll lên đầu để thấy thông báo
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            // Event delegation for status updates - giữ nguyên
            document.addEventListener('click', function (e) {
                const statusOption = e.target.closest('.status-option');
                if (statusOption) {
                    e.preventDefault();
                    const repairId = statusOption.dataset.repairId;
                    const newStatus = statusOption.dataset.status;
                    const updateButton = document.querySelector(
                        `.dropdown-toggle[data-repair-id="${repairId}"]`);
                    const originalStatus = updateButton ? updateButton.dataset.originalValue : newStatus;

                    if (newStatus === originalStatus) return;

                    if (confirm(`Bạn có chắc muốn chuyển trạng thái thành "${newStatus}"?`)) {
                        const form = document.getElementById(`status-form-${repairId}`);
                        if (form) {
                            form.querySelector('[name="status"]').value = newStatus;
                            form.submit();
                        }
                    }
                }
            });
        });
    </script>
@endsection