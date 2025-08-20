@extends('layouts.app')

@section('title', 'Quản lý người ở chung')

@section('content')
    <div class="container-fluid py-5 px-4">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg border-0 rounded-4" style="overflow: hidden;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center rounded-top-4"
                style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users me-2"></i>Quản lý người ở chung
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $contractTenants->total() }} người</span>
                    </h5>
                </div>
            </div>

            <div class="card-body p-4 bg-light">
                <!-- Notification for pending bookings -->
                <div class="mb-4">
                    @php
                        $pendingCount = \App\Models\ContractTenant::where('status', 'Chờ duyệt')->count();
                        $hasFilters = $querySearch || $room_id || $status;
                    @endphp
                    @if(!$hasFilters)
                        @if($pendingCount > 0)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span>Có <strong>{{ $pendingCount }}</strong> yêu cầu người ở chung đang chờ duyệt</span>
                            </div>
                        @else
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <span>Không có yêu cầu nào chờ duyệt</span>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('contract-tenants.index') }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-primary"></i></span>
                                <input type="text" class="form-control border-0" name="querySearch"
                                    placeholder="Tìm kiếm theo tên, SĐT, email..." value="{{ $querySearch }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm rounded-3" name="room_id">
                                <option value="">Tất cả phòng</option>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" {{ $room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm rounded-3" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ duyệt" {{ $status == 'Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="Đã duyệt" {{ $status == 'Đã duyệt' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="Từ chối" {{ $status == 'Từ chối' ? 'selected' : '' }}>Từ chối</option>
                                <option value="Huỷ bỏ" {{ $status == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                                <option value="Đang ở" {{ $status == 'Đang ở' ? 'selected' : '' }}>Đang ở</option>
                                <option value="Đã rời đi" {{ $status == 'Đã rời đi' ? 'selected' : '' }}>Đã rời đi</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm rounded-3" name="sort">
                                <option value="desc" {{ $sort == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="asc" {{ $sort == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary shadow-sm w-100 rounded-3">
                                <i class="fas fa-search me-1"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Room-based Accordion -->
                <div class="accordion" id="roomAccordion">
                    @php
                        $tenantsByRoom = $contractTenants->groupBy(function($tenant) {
                            return $tenant->contract->room->id;
                        });
                    @endphp

                    @forelse ($tenantsByRoom as $roomId => $roomTenants)
                        @php
                            $room = $roomTenants->first()->contract->room;
                            $pendingTenantsCount = $roomTenants->where('status', 'Chờ duyệt')->count();
                            $approvedTenantsCount = $roomTenants->where('status', 'Đã duyệt')->count();
                            $rejectedTenantsCount = $roomTenants->where('status', 'Từ chối')->count();
                            $currentlyInTenantsCount = $roomTenants->where('status', 'Đang ở')->count();
                            $leftTenantsCount = $roomTenants->where('status', 'Đã rời đi')->count();
                            $cancelledTenantsCount = $roomTenants->where('status', 'Huỷ bỏ')->count();

                            // Get primary tenant info
                            $primaryTenant = $roomTenants->first()->contract->user;
                        @endphp

                        <div class="accordion-item border-0 shadow-sm mb-3 rounded-3">
                            <h2 class="accordion-header" id="heading{{ $roomId }}">
                                <button class="accordion-button collapsed rounded-3 fw-bold" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $roomId }}"
                                    aria-expanded="false" aria-controls="collapse{{ $roomId }}"
                                    style="background: linear-gradient(90deg, #f8f9fa, #e9ecef);">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-door-open me-3 text-primary"></i>
                                            <div>
                                                <h6 class="mb-0 text-primary">{{ $room->name }}</h6>
                                                <small class="text-muted">Người ký chính: {{ $primaryTenant->name }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($pendingTenantsCount > 0)
                                                <span class="badge bg-warning rounded-pill">{{ $pendingTenantsCount }} chờ duyệt</span>
                                            @endif
                                            @if($approvedTenantsCount > 0)
                                                <span class="badge bg-success rounded-pill">{{ $approvedTenantsCount }} đã duyệt</span>
                                            @endif
                                            @if($rejectedTenantsCount > 0)
                                                <span class="badge bg-danger rounded-pill">{{ $rejectedTenantsCount }} từ chối</span>
                                            @endif
                                            @if($currentlyInTenantsCount > 0)
                                                <span class="badge bg-info rounded-pill">{{ $currentlyInTenantsCount }} đang ở</span>
                                            @endif
                                            @if($leftTenantsCount > 0)
                                                <span class="badge bg-dark rounded-pill">{{ $leftTenantsCount }} đã rời đi</span>
                                            @endif
                                            @if($cancelledTenantsCount > 0)
                                                <span class="badge bg-secondary rounded-pill">{{ $cancelledTenantsCount }} đã huỷ bỏ</span>
                                            @endif
                                            <span class="badge bg-primary rounded-pill">{{ $roomTenants->count() }} thành viên</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $roomId }}" class="accordion-collapse collapse"
                                aria-labelledby="heading{{ $roomId }}" data-bs-parent="#roomAccordion">
                                <div class="accordion-body p-0">
                                    <!-- Primary Tenant Info -->
                                    <div class="bg-light p-3 border-bottom">
                                        <h6 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-crown me-2"></i>Người ký hợp đồng chính
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Tên:</strong> {{ $primaryTenant->name }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>SĐT:</strong> {{ $primaryTenant->phone }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Email:</strong> {{ $primaryTenant->email }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tenants Table -->
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead class="table-dark" style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 5%;">STT</th>
                                                    <th scope="col" style="width: 15%;">Tên</th>
                                                    <th scope="col" style="width: 12%;">SĐT</th>
                                                    <th scope="col" style="width: 20%;">Email</th>
                                                    <th scope="col" style="width: 10%;">Chi tiết</th>
                                                    <th scope="col" style="width: 20%;">Lý do từ chối</th>
                                                    <th scope="col" style="width: 18%;">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($roomTenants as $index => $tenant)
                                                    <tr class="table-row">
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <div class="fw-bold">{{ $tenant->name }}</div>
                                                                    <small class="text-muted">{{ $tenant->relation_with_primary ?? 'Chưa rõ mối quan hệ' }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $tenant->phone }}</td>
                                                        <td>{{ Str::limit($tenant->email, 20) }}</td>
                                                        <td>
                                                            <button class="btn btn-outline-primary btn-sm rounded-pill"
                                                                data-bs-toggle="modal" data-bs-target="#detailModal{{ $tenant->id }}">
                                                                <i class="fas fa-eye me-1"></i>Xem
                                                            </button>
                                                        </td>
                                                        <td>
                                                            @if($tenant->rejection_reason)
                                                                <span class="text-danger">{{ Str::limit($tenant->rejection_reason, 30) }}</span>
                                                            @else
                                                                <span class="text-muted">Không có</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($tenant->status == 'Chờ duyệt')
                                                                <form action="{{ route('contract-tenants.update-status', $tenant->id) }}"
                                                                    method="POST" class="status-form">
                                                                    @csrf
                                                                    <select class="form-select form-select-sm status-select shadow-sm rounded-pill"
                                                                        name="status" onchange="handleStatusChange(this, {{ $tenant->id }})">
                                                                        <option value="Chờ duyệt" selected>Chờ duyệt</option>
                                                                        <option value="Đã duyệt">Chấp nhận</option>
                                                                        <option value="Từ chối">Từ chối</option>
                                                                    </select>
                                                                </form>
                                                            @elseif ($tenant->status == 'Đã duyệt')
                                                                <form action="{{ route('contract-tenants.update-status', $tenant->id) }}"
                                                                    method="POST" class="status-form">
                                                                    @csrf
                                                                    <select class="form-select form-select-sm status-select shadow-sm rounded-pill"
                                                                        name="status" onchange="handleStatusChange(this, {{ $tenant->id }})">
                                                                        <option value="Đã duyệt" selected>Đã duyệt</option>
                                                                        <option value="Đang ở">Đang ở</option>
                                                                    </select>
                                                                </form>
                                                            @elseif ($tenant->status == 'Đang ở')
                                                                <form action="{{ route('contract-tenants.update-status', $tenant->id) }}"
                                                                    method="POST" class="status-form">
                                                                    @csrf
                                                                    <select class="form-select form-select-sm status-select shadow-sm rounded-pill"
                                                                        name="status" onchange="handleStatusChange(this, {{ $tenant->id }})">
                                                                        <option value="Đang ở" selected>Đang ở</option>
                                                                        <option value="Đã rời đi">Đã rời đi</option>
                                                                    </select>
                                                                </form>
                                                            @else
                                                                @php
                                                                    $badgeClass = match ($tenant->status) {
                                                                        'Đã duyệt' => 'success',
                                                                        'Từ chối' => 'danger',
                                                                        'Chờ duyệt' => 'warning',
                                                                        'Huỷ bỏ' => 'secondary',
                                                                        'Đang ở' => 'primary',
                                                                        'Đã rời đi' => 'dark',
                                                                        default => 'light',
                                                                    };
                                                                    $statusText = $tenant->status;
                                                                @endphp
                                                                <span class="badge bg-{{ $badgeClass }} py-2 px-3 rounded-pill">{{ $statusText }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <!-- Detail Modal -->
                                                    <div class="modal fade" id="detailModal{{ $tenant->id }}" tabindex="-1"
                                                        aria-labelledby="detailModalLabel{{ $tenant->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content rounded-4 shadow-lg">
                                                                <div class="modal-header bg-primary text-white rounded-top-4">
                                                                    <h5 class="modal-title" id="detailModalLabel{{ $tenant->id }}">Chi tiết người ở chung</h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-4">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Tên:</strong> {{ $tenant->name }}</p>
                                                                            <p><strong>SĐT:</strong> {{ $tenant->phone }}</p>
                                                                            <p><strong>Email:</strong> {{ $tenant->email }}</p>
                                                                            <p><strong>Giới tính:</strong> {{ $tenant->gender ?? 'Chưa có' }}</p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p><strong>Ngày sinh:</strong> {{ $tenant->birthday ? \Carbon\Carbon::parse($tenant->birthday)->format('d/m/Y') : 'Chưa có' }}</p>
                                                                            <p><strong>Địa chỉ:</strong> {{ $tenant->address ?? 'Chưa có' }}</p>
                                                                            <p><strong>Mối quan hệ:</strong> {{ $tenant->relation_with_primary ?? 'Chưa có' }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row mt-3">
                                                                        <div class="col-12">
                                                                            <strong>Giấy tờ tùy thân:</strong>
                                                                            @if ($tenant->identity_document)
                                                                                @php
                                                                                    $imagePaths = explode('|', $tenant->identity_document);
                                                                                @endphp
                                                                                @foreach ($imagePaths as $path)
                                                                                    @php
                                                                                        $fileName = basename($path, '.enc');
                                                                                    @endphp
                                                                                    <img src="{{ route('contract-tenants.identity-document', [$tenant->id, $fileName]) }}"
                                                                                         alt="Giấy tờ tùy thân" class="img-fluid mb-2" style="max-width: 300px; border: 1px solid #ddd; border-radius: 4px;">
                                                                                @endforeach
                                                                            @else
                                                                                Chưa có
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-outline-secondary rounded-3"
                                                                        data-bs-dismiss="modal">Đóng</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Rejection Reason Modal -->
                                                    <div class="modal fade" id="rejectionModal{{ $tenant->id }}" tabindex="-1"
                                                        aria-labelledby="rejectionModalLabel{{ $tenant->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content rounded-4 shadow-lg border-0">
                                                                <div class="modal-header bg-gradient rounded-top-4 p-4"
                                                                    style="background: linear-gradient(135deg, #dc3545, #b02a37);">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                                                        <h5 class="modal-title mb-0 fw-bold"
                                                                            id="rejectionModalLabel{{ $tenant->id }}">Từ chối yêu cầu</h5>
                                                                    </div>
                                                                    <button type="button" class="btn-close btn-close-white shadow-sm" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <form action="{{ route('contract-tenants.update-status', $tenant->id) }}" method="POST">
                                                                    @csrf
                                                                    <div class="modal-body p-4 bg-light">
                                                                        <input type="hidden" name="status" value="Từ chối">

                                                                        <!-- Tenant Info Card -->
                                                                        <div class="card bg-white border-0 shadow-sm rounded-3 mb-4">
                                                                            <div class="card-body p-3">
                                                                                <h6 class="text-primary fw-bold mb-2">
                                                                                    <i class="fas fa-user me-2"></i>Thông tin người ở chung
                                                                                </h6>
                                                                                <div class="row">
                                                                                    <div class="col-6">
                                                                                        <p class="mb-1"><strong>Tên:</strong> {{ $tenant->name }}</p>
                                                                                        <p class="mb-0"><strong>SĐT:</strong> {{ $tenant->phone }}</p>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <p class="mb-1"><strong>Email:</strong> {{ Str::limit($tenant->email, 20) }}</p>
                                                                                        <p class="mb-0"><strong>Quan hệ:</strong> {{ $tenant->relation_with_primary ?? 'Chưa rõ' }}</p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Rejection Reason Input -->
                                                                        <div class="mb-3">
                                                                            <label for="rejection_reason_{{ $tenant->id }}"
                                                                                class="form-label fw-bold text-dark d-flex align-items-center">
                                                                                <i class="fas fa-edit me-2 text-danger"></i>
                                                                                Lý do từ chối yêu cầu <span class="text-danger">*</span>
                                                                            </label>
                                                                            <textarea class="form-control shadow-sm rounded-3 border-2"
                                                                                id="rejection_reason_{{ $tenant->id }}"
                                                                                name="rejection_reason"
                                                                                rows="4"
                                                                                placeholder="Vui lòng nhập lý do từ chối cụ thể để người dùng hiểu rõ..."
                                                                                style="border-color: #e9ecef; transition: all 0.3s ease;"
                                                                                required></textarea>
                                                                            <div class="form-text text-muted">
                                                                                <i class="fas fa-info-circle me-1"></i>
                                                                                Lý do này sẽ được gửi đến người yêu cầu
                                                                            </div>
                                                                        </div>

                                                                        <!-- Warning Alert -->
                                                                        <div class="alert alert-warning d-flex align-items-center rounded-3 border-0 shadow-sm" role="alert">
                                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                                            <div>
                                                                                <strong>Lưu ý:</strong> Hành động này không thể hoàn tác.
                                                                                Người dùng sẽ nhận được thông báo từ chối kèm lý do.
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer bg-white p-4 border-0">
                                                                        <button type="button" class="btn btn-outline-secondary rounded-3 px-4 shadow-sm"
                                                                            data-bs-dismiss="modal">
                                                                            <i class="fas fa-times me-2"></i>Hủy bỏ
                                                                        </button>
                                                                        <button type="submit" class="btn btn-danger rounded-3 px-4 shadow-sm">
                                                                            <i class="fas fa-ban me-2"></i>Xác nhận từ chối
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                            <br>
                            <span class="fs-5">Không có người ở chung nào.</span>
                            <br>
                            <small>Hãy thử thay đổi bộ lọc tìm kiếm.</small>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if ($contractTenants->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $contractTenants->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .accordion-button:not(.collapsed) {
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
            color: white;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }

        .accordion-item {
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .avatar-sm {
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge {
            font-size: 0.8rem;
            font-weight: 500;
        }

        .form-select-sm {
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        @media (max-width: 768px) {
            .accordion-button {
                font-size: 0.9rem;
                padding: 1rem;
            }

            .badge {
                font-size: 0.7rem;
                margin: 2px;
            }

            .table-responsive {
                font-size: 0.85rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to accordion items
            const accordionItems = document.querySelectorAll('.accordion-item');
            accordionItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
                item.classList.add('animate__animated', 'animate__fadeInUp');
            });

            // Add animation to modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('show.bs.modal', function () {
                    this.querySelector('.modal-content').classList.add('animate__animated', 'animate__zoomIn');
                });
                modal.addEventListener('hide.bs.modal', function () {
                    this.querySelector('.modal-content').classList.remove('animate__zoomIn');
                    this.querySelector('.modal-content').classList.add('animate__zoomOut');
                });
            });
        });

        function handleStatusChange(select, tenantId) {
            const status = select.value;

            if (status === 'Từ chối') {
                select.value = 'Chờ duyệt';
                const modal = new bootstrap.Modal(document.getElementById('rejectionModal' + tenantId));
                modal.show();
            } else if (status === 'Đã duyệt') {
                if (confirm('Bạn có chắc chắn muốn chấp nhận người ở chung này?')) {
                    select.closest('form').submit();
                } else {
                    select.value = 'Chờ duyệt';
                }
            } else if (status === 'Đang ở') {
                if (confirm('Bạn có chắc muốn đổi sang trạng thái này?')) {
                    select.closest('form').submit();
                } else {
                    select.value = 'Chờ duyệt';
                }
            } else if (status === 'Đã rời đi') {
                if (confirm('Bạn có chắc muốn đổi sang trạng thái này?')) {
                    select.closest('form').submit();
                } else {
                    select.value = 'Chờ duyệt';
                }
            } else {
                select.closest('form').submit();
            }
        }
    </script>
@endsection
