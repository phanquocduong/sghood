@extends('layouts.app')

@section('title', 'Quản lý hợp đồng')

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

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient text-white align-items-center rounded-top-4"
                style="background: linear-gradient(90deg, #007bff, #00c6ff);">
                <div class="card-header bg-gradient text-white d-flex flex-column flex-sm-row justify-content-between align-items-center rounded-top-4"
                    style="background: linear-gradient(90deg, #007bff, #00c6ff);">
                    <h5 class="mb-2 mb-sm-0 fw-bold text-start">
                        <i class="fas fa-file-contract me-2"></i>Quản lý hợp đồng
                        <span class="badge bg-light text-primary ms-2">{{ $contracts->total() ?? 0 }} hợp đồng</span>
                    </h5>
                    <a href="{{ route('contracts.contract-extensions') }}"
                        class="btn btn-primary me-0 shadow-sm w-auto px-2 px-sm-4 d-flex align-items-center justify-content-center"
                        style="transition: all 0.3s;" title="Xem danh sách hợp đồng gia hạn">
                        <i class="fas fa-history me-1"></i>
                        <span class="d-none d-sm-inline">Danh sách hợp đồng gia hạn</span>
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Notification for pending bookings -->
                <div class="mb-4">
                    @php
                        $pendingCount = \App\Models\Contract::where('status', 'Chờ duyệt')->count();
                        $pendingCountt = \App\Models\Contract::where('status', 'Chờ duyệt thủ công')->count();
                    @endphp
                    @if($pendingCount > 0 || $pendingCountt > 0)
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span>Có <strong>{{ $pendingCount }}</strong> hợp đồng đang chờ duyệt và <strong>{{ $pendingCountt }}</strong> hợp đồng đang chờ duyệt thủ công</span>
                        </div>
                    @else
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>Không có hợp đồng nào chờ duyệt</span>
                        </div>
                    @endif
                </div>

                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="{{ route('contracts.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control shadow-sm" name="querySearch"
                                    placeholder="Tìm kiếm theo tên người thuê hoặc phòng..." value="{{ $querySearch }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Chờ xác nhận" {{ $status == 'Chờ xác nhận' ? 'selected' : '' }}>Chờ xác nhận
                                </option>
                                <option value="Chờ duyệt" {{ $status == 'Chờ duyệt' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="Chờ duyệt thủ công" {{ $status == 'Chờ duyệt thủ công' ? 'selected' : '' }}>Chờ duyệt thủ công</option>
                                <option value="Chờ chỉnh sửa" {{ $status == 'Chờ chỉnh sửa' ? 'selected' : '' }}>Chờ chỉnh
                                    sửa</option>
                                <option value="Chờ ký" {{ $status == 'Chờ ký' ? 'selected' : '' }}>Chờ ký</option>
                                <option value="Chờ thanh toán tiền cọc"
                                    {{ $status == 'Chờ thanh toán tiền cọc' ? 'selected' : '' }}>Chờ thanh toán tiền cọc
                                </option>
                                <option value="Hoạt động" {{ $status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="Kết thúc" {{ $status == 'Kết thúc' ? 'selected' : '' }}>Kết thúc</option>
                                <option value="Kết thúc sớm" {{ $status == 'Kết thúc sớm' ? 'selected' : '' }}>Kết thúc sớm</option>
                                <option value="Huỷ bỏ" {{ $status == 'Huỷ bỏ' ? 'selected' : '' }}>Huỷ bỏ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="sort">
                                <option value="desc" {{ ($sort ?? 'desc') == 'desc' ? 'selected' : '' }}>Mới nhất
                                </option>
                                <option value="asc" {{ ($sort ?? 'desc') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary shadow-sm w-100">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contracts Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm rounded-3 overflow-hidden"
                        style="text-align: center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 8%;" class="text-center">Mã HD</th>
                                <th scope="col" style="width: 15%;">Người thuê</th>
                                <th scope="col" style="width: 13%;">Tên phòng</th>
                                <th scope="col" style="width: 12%;" class="text-center">Ngày kết thúc</th>
                                <th scope="col" style="width: 13%;" class="text-center">Giá thuê</th>
                                <th scope="col" style="width: 12%;" class="text-center">Trạng thái</th>
                                <th scope="col" style="width: 14%;" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contracts as $contractItem)
                                <tr class="table-row">
                                    <td class="text-center">{{ 'HD'.$contractItem->id }}</td>
                                    <td>
                                        <span class="user-name-clickable"
                                            data-user-id="{{ $contractItem->user->id ?? '' }}"
                                            data-user-name="{{ $contractItem->user->name ?? 'N/A' }}"
                                            data-user-email="{{ $contractItem->user->email ?? 'N/A' }}"
                                            data-user-phone="{{ $contractItem->user->phone ?? 'N/A' }}"
                                            data-user-address="{{ $contractItem->user->address ?? 'N/A' }}"
                                            data-user-cccd="{{ $contractItem->user->cccd ?? 'N/A' }}"
                                            data-user-created="{{ $contractItem->user->created_at ?? 'N/A' }}"
                                            title="Nhấn để xem thông tin chi tiết">
                                            {{ $contractItem->user->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="room-name-clickable"
                                            data-room-id="{{ $contractItem->room->id ?? '' }}"
                                            title="Nhấn để xem chi tiết phòng">
                                            {{ $contractItem->room->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $contractItem->end_date ? \Carbon\Carbon::parse($contractItem->end_date)->format('d/m/Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-success">
                                            {{ number_format($contractItem->rental_price ?? 0, 0, ',', '.') }} VNĐ
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $calculatedStatus =
                                                $contractItem->calculated_status ?? $contractItem->status;
                                            $badgeClass = match ($calculatedStatus) {
                                                'Chờ xác nhận' => 'primary',
                                                'Chờ duyệt' => 'warning',
                                                'Chờ duyệt thủ công' => 'warning',
                                                'Chờ chỉnh sửa' => 'danger',
                                                'Chờ ký' => 'info',
                                                'Hoạt động' => 'success',
                                                'Kết thúc' => 'secondary',
                                                'Kết thúc sớm' => 'secondary',
                                                'Huỷ bỏ' => 'dark',
                                                'Chờ thanh toán tiền cọc' => 'warning',
                                                default => 'light',
                                            };
                                            $iconClass = match ($calculatedStatus) {
                                                'Chờ xác nhận' => 'fas fa-clock',
                                                'Chờ duyệt' => 'fas fa-eye',
                                                'Chờ duyệt thủ công' => 'fas fa-eye',
                                                'Chờ chỉnh sửa' => 'fas fa-edit',
                                                'Chờ ký' => 'fas fa-pen',
                                                'Hoạt động' => 'fas fa-check-circle',
                                                'Kết thúc' => 'fas fa-flag-checkered',
                                                'Kết thúc sớm' => 'fas fa-flag-checkered',
                                                'Huỷ bỏ' => 'fas fa-times-circle',
                                                default => 'fas fa-info-circle',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }} py-2 px-3">
                                            <i class="{{ $iconClass }} me-1" style="font-size: 8px;"></i>
                                            {{ $calculatedStatus }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('contracts.show', $contractItem->id) }}"
                                                class="btn btn-info btn-sm shadow-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye me-1"></i>Xem
                                            </a>
                                            @if ($contractItem->file && Storage::disk('private')->exists($contractItem->file))
                                                <a href="{{ route('contracts.download', $contractItem->id) }}"
                                                    class="btn btn-outline-primary btn-sm shadow-sm"
                                                    title="Tải xuống PDF">
                                                    <i class="fas fa-download me-1"></i>PDF
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm"
                                                    disabled title="Không có file">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-file-contract fa-3x mb-3 opacity-50"></i>
                                        <br>
                                        <span class="fs-5">Không có hợp đồng nào.</span>
                                        <br>
                                        <small>Hãy thử thay đổi bộ lọc tìm kiếm hoặc tạo hợp đồng mới.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($contracts->hasPages())
                    <div class="mt-4">
                        {{ $contracts->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Info Modal -->
    <div class="modal fade" id="userInfoModal" tabindex="-1" aria-labelledby="userInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="userInfoModalLabel">
                        <i class="fas fa-user-circle me-2"></i>Thông tin người thuê
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img id="modalUserAvatar" src="{{ asset('img/user.jpg') }}" alt="User Avatar"
                                class="img-fluid rounded-circle mb-3"
                                style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-user me-2"></i>Họ và tên:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserName">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-envelope me-2"></i>Email:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserEmail">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-phone me-2"></i>Số điện thoại:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserPhone">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserAddress">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-venus-mars me-2"></i>Giới tính:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserGender">-</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-birthday-cake me-2"></i>Ngày sinh:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserBirthdate">-
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">
                                    <i class="fas fa-calendar-plus me-2"></i>Ngày tạo tài khoản:
                                </label>
                                <p class="form-control-plaintext border rounded p-2 bg-light" id="modalUserCreated">-</p>
                            </div>
                        </div>
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

    <style>
        .breadcrumb {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.85rem;
            border-radius: 15px;
            font-weight: 500;
        }

        .form-select:focus,
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table-row:hover {
            background-color: #f8f9fa;
        }

        .avatar-circle {
            font-size: 14px;
        }

        .btn-group .btn {
            margin: 0 1px;
        }

        .user-name-clickable,
        .room-name-clickable {
            cursor: pointer;
            color: #007bff;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        /* Thêm biểu tượng nhỏ trước tên người dùng và phòng */
        .user-name-clickable::before {
            content: '\f007';
            /* FontAwesome user icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 6px;
            font-size: 0.9rem;
            color: #007bff;
        }

        .room-name-clickable::before {
            content: '\f015';
            /* FontAwesome home icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 6px;
            font-size: 0.9rem;
            color: #007bff;
        }

        .user-name-clickable:hover,
        .room-name-clickable:hover {
            color: #0056b3;
            background-color: #e7f1ff;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Đảm bảo tên dài không bị tràn */
        .user-name-clickable,
        .room-name-clickable {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Tăng khoảng cách trong ô bảng */
        .table td {
            padding: 12px 8px;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
        }

        .form-control-plaintext {
            font-weight: 500;
            color: #495057 !important;
        }
    </style>

    <script>
        // Add smooth scroll effect when clicking navigation links
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to table rows
            const tableRows = document.querySelectorAll('.table-row');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                row.classList.add('animate__animated', 'animate__fadeInUp');
            });

            // Handle user name click to show popup
            const userNameElements = document.querySelectorAll('.user-name-clickable');
            userNameElements.forEach(element => {
                element.addEventListener('click', function() {
                    const userName = this.getAttribute('data-user-name');
                    const userEmail = this.getAttribute('data-user-email');
                    const userPhone = this.getAttribute('data-user-phone');
                    const userAddress = this.getAttribute('data-user-address');
                    const userCreated = this.getAttribute('data-user-created');
                    const userGender = this.getAttribute('data-user-gender');
                    const userBirthdate = this.getAttribute('data-user-birthdate');
                    const userAvatar = this.getAttribute('data-user-avatar');

                    // Update modal content
                    document.getElementById('modalUserName').textContent = userName || 'N/A';
                    document.getElementById('modalUserEmail').textContent = userEmail || 'N/A';
                    document.getElementById('modalUserPhone').textContent = userPhone || 'N/A';
                    document.getElementById('modalUserAddress').textContent = userAddress || 'N/A';
                    document.getElementById('modalUserGender').textContent = userGender || 'N/A';
                    document.getElementById('modalUserAvatar').src = userAvatar ||
                        '{{ asset('img/user.jpg') }}';

                    // Format birthdate if available
                    if (userBirthdate && userBirthdate !== 'N/A') {
                        const date = new Date(userBirthdate);
                        const formattedBirthdate = date.toLocaleDateString('vi-VN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        document.getElementById('modalUserBirthdate').textContent =
                            formattedBirthdate;
                    } else {
                        document.getElementById('modalUserBirthdate').textContent = 'N/A';
                    }

                    // Format created date if available
                    if (userCreated && userCreated !== 'N/A') {
                        const date = new Date(userCreated);
                        const formattedDate = date.toLocaleDateString('vi-VN', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        document.getElementById('modalUserCreated').textContent = formattedDate;
                    } else {
                        document.getElementById('modalUserCreated').textContent = 'N/A';
                    }

                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('userInfoModal'));
                    modal.show();
                });
            });

            // Handle room name click to navigate to room detail
            const roomNameElements = document.querySelectorAll('.room-name-clickable');
            roomNameElements.forEach(element => {
                element.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    if (roomId && roomId !== '') {
                        // Navigate to room detail page
                        window.open(`{{ url('rooms') }}/${roomId}`, '_blank');
                    }
                });
            });
        });
    </script>
@endsection
