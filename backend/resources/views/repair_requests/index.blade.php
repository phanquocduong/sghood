@extends('layouts.app')

@section('title', 'Quản lý Yêu Cầu Sửa Chữa')

@section('content')
    @foreach (['success', 'error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg == 'success' ? 'success' : 'danger' }} alert-dismissible fade show animate__animated animate__fadeIn"
                role="alert">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    <div class="container-fluid py-5 px-4">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4">
                <h5 class="mb-0">Quản lý Yêu Cầu Sửa Chữa</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('repair_requests.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control rounded-3" name="querySearch"
                            placeholder="Tìm theo tiêu đề, mô tả, ghi chú..." value="{{ request('querySearch') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select rounded-3" name="status">
                            <option value="">Tất cả trạng thái</option>
                            @foreach (['Chờ xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Huỷ bỏ'] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
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
                            <i class="fas fa-search me-2"></i>Tìm
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center shadow-sm rounded-3 overflow-hidden">
                        <thead class="table-dark">
                            <tr>
                                <th>STT</th>
                                <th>Người thuê</th>
                                <th>Phòng</th>
                                <th>Tiêu Đề</th>
                                <!-- <th>Mô Tả</th> -->
                                <!-- <th>Hình Ảnh</th> -->
                                <th>Trạng Thái</th>
                                <th>Lý Do Hủy</th>
                                <th>Ngày Sửa</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($repairRequests as $repair)
                                <tr>
                                    <td>{{ ($repairRequests->currentPage() - 1) * $repairRequests->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $repair->contract->user->name ?? 'N/A' }}</td>
                                    <td>{{ $repair->contract->room->name }}</td>
                                    <td>{{ $repair->title ?? 'N/A' }}</td>
                                    <!-- <td>{{ Str::limit($repair->description, 100) ?? 'N/A' }}</td> -->
                                    <!-- <td>
                                                                        @if ($repair->images)
                                                                            <img src="{{ $repair->images }}" alt="Hình ảnh"
                                                                                style="width: 80px; border-radius: 8px;">
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </td> -->
                                    <td>
                                        @php
                                            $badgeClass = match ($repair->status) {
                                                'Đang thực hiện' => 'warning',
                                                'Huỷ bỏ' => 'danger',
                                                'Hoàn thành' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ $repair->status }}</span>
                                    </td>
                                    <td>{{ $repair->cancellation_reason ?? 'N/A' }}</td>
                                    <td>
                                        {{ $repair->repaired_at ? \Carbon\Carbon::parse($repair->repaired_at)->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('repair_requests.show', $repair->id) }}"
                                                class="btn btn-info btn-sm rounded-3">Chi tiết</a>
                                            @php
                                                $isFinal = in_array($repair->status, ['Hoàn thành', 'Huỷ bỏ']);
                                                $options = match ($repair->status) {
                                                    'Chờ xác nhận' => ['Chờ xác nhận', 'Đang thực hiện', 'Huỷ bỏ'],
                                                    'Đang thực hiện' => ['Đang thực hiện', 'Hoàn thành', 'Huỷ bỏ'],
                                                    default => [$repair->status],
                                                };
                                            @endphp
                                            @if (!$isFinal)
                                                <button type="button" class="btn btn-primary btn-sm rounded-3 update-status"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    data-original-value="{{ $repair->status }}"
                                                     data-repair-id="{{ $repair->id }}">
                                                    Cập nhật trạng thái
                                                </button>
                                                <form id="status-form-{{ $repair->id }}"
                                                    action="{{ route('repairs.updateStatus', $repair->id) }}" method="POST"
                                                    class="d-none">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $repair->status }}">
                                                    <input type="hidden" name="cancel_reason" class="cancel-reason-input">
                                                </form>
                                                <ul class="dropdown-menu">
                                                    @foreach ($options as $option)
                                                        <li><a class="dropdown-item status-option" href="#" data-status="{{ $option }}"
                                                                data-repair-id="{{ $repair->id }}">{{ $option }}</a></li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span
                                                    class="badge bg-secondary text-white p-2 rounded-3">{{ $repair->status }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Không có yêu cầu sửa chữa nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $repairRequests->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal hủy yêu cầu --}}
    <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-labelledby="cancelReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lý do hủy yêu cầu sửa chữa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <label for="cancelReason" class="form-label">Vui lòng nhập lý do hủy:</label>
                    <textarea class="form-control" id="cancelReason" rows="4" required></textarea>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('cancelReasonModal');
            const cancelModal = modalElement ? new bootstrap.Modal(modalElement) : null;

            // Event delegation for status updates
            document.addEventListener('click', function (e) {
                const statusOption = e.target.closest('.status-option');
                if (statusOption) {
                    e.preventDefault();
                    const repairId = statusOption.dataset.repairId;
                    const newStatus = statusOption.dataset.status;
                    const updateButton = document.querySelector(`.update-status[data-repair-id="${repairId}"]`);
                    const originalStatus = updateButton ? updateButton.dataset.originalValue : newStatus;

                    if (newStatus === originalStatus) return;

                    if (newStatus === 'Huỷ bỏ') {
                        if (cancelModal) {
                            document.getElementById('cancelReason').value = '';
                            cancelModal.show();
                            window.currentRepairId = repairId; // Store globally for modal
                        } else {
                            alert('Không thể hiển thị form nhập lý do hủy.');
                        }
                    } else if (confirm(`Bạn có chắc muốn chuyển trạng thái thành "${newStatus}"?`)) {
                        const form = document.getElementById(`status-form-${repairId}`);
                        if (form) {
                            form.querySelector('[name="status"]').value = newStatus;
                            form.submit();
                        }
                    }
                }

                // Handle delete confirmation
                const deleteBtn = e.target.closest('.delete-btn');
                if (deleteBtn) {
                    const repairId = deleteBtn.dataset.bsTarget.replace('#deleteConfirmationModal-', '');
                    const confirmDelete = document.getElementById(`confirmDelete-${repairId}`);
                    confirmDelete.addEventListener('click', function () {
                        document.querySelector(`#deleteConfirmationModal-${repairId} .delete-form`).submit();
                    });
                }
            });

            // Handle cancel reason confirmation
            document.getElementById('confirmCancel').addEventListener('click', function () {
                const reason = document.getElementById('cancelReason').value.trim();
                if (!reason) {
                    alert('Vui lòng nhập lý do hủy!');
                    return;
                }
                const form = document.getElementById(`status-form-${window.currentRepairId}`);
                if (form) {
                    form.querySelector('.cancel-reason-input').value = reason;
                    form.querySelector('[name="status"]').value = 'Huỷ bỏ';
                    form.submit();
                }
                if (cancelModal) cancelModal.hide();
            });

            // Reset on modal close
            modalElement.addEventListener('hidden.bs.modal', function () {
                if (window.currentRepairId) {
                    const updateButton = document.querySelector(`.update-status[data-repair-id="${window.currentRepairId}"]`);
                    if (updateButton) {
                        updateButton.dataset.originalValue = updateButton.dataset.originalValue; // Reset if needed
                    }
                    window.currentRepairId = null;
                }
            });
        });
    </script>
@endsection
