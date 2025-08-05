@extends('layouts.app')

@section('title', 'Người dùng')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Người dùng') }}</h6>
            </div>
            <div class="card-body p-4">
                <form action="" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                placeholder="Tìm kiếm người dùng..." class="form-control shadow-sm">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select shadow-sm">
                            <option value="">-- Tất cả vai trò --</option>
                            <option value="Người đăng ký" {{ request('role') == 'Người đăng ký' ? 'selected' : '' }}>Người
                                đăng ký</option>
                            <option value="Người thuê" {{ request('role') == 'Người thuê' ? 'selected' : '' }}>Người thuê
                            </option>
                            <option value="Quản trị viên" {{ request('role') == 'Quản trị viên' ? 'selected' : '' }}>Quản
                                trị viên</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select shadow-sm">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động
                            </option>
                            <option value="Khoá" {{ request('status') == 'Khoá' ? 'selected' : '' }}>Khoá</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select shadow-sm">
                            <option value="">-- Sắp xếp --</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A → Z
                            </option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z → A
                            </option>
                            <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Ngày
                                tạo ↑</option>
                            <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>
                                Ngày tạo ↓</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm" style="transition: all 0.3s;">
                            <i class="fas fa-magnifying-glass me-1"></i> Tìm
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">STT</th>
                                <th scope="col">Họ tên</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Email</th>
                                <th scope="col" style="width: 15%;">Ngày đăng ký</th>
                                <th scope="col" style="width: 15%;">Vai trò</th>
                                <th scope="col" style="width: 15%;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $index => $user)
                                <tr class="table-row">
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <a href="javascript:void(0);" class="text-decoration-none text-primary"
                                            onclick="showUserModal({{ $user->id }})">
                                            {{ $user->name }}
                                        </a>
                                    </td>

                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</td>
                                    {{-- <td>{{ $user->role }}</td> --}}
                                    <td>
                                        <form action="{{ route('users.updateRole', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-select form-select-sm"
                                                onchange="confirmRoleChange(this)">
                                                <option value="Người đăng ký"
                                                    {{ $user->role == 'Người đăng ký' ? 'selected' : '' }}>Người đăng ký
                                                </option>
                                                <option value="Người thuê"
                                                    {{ $user->role == 'Người thuê' ? 'selected' : '' }}>Người thuê</option>
                                                <option value="Quản trị viên"
                                                    {{ $user->role == 'Quản trị viên' ? 'selected' : '' }}>Quản trị viên
                                                </option>

                                                {{-- Chỉ hiển thị nếu bản thân user đã là Super admin --}}
                                                @if ($user->role === 'Super admin')
                                                    <option value="Super admin" selected>Super admin</option>
                                                @endif
                                            </select>

                                        </form>
                                    </td>
                                    <td>
                                        @php
                                            $canEditStatus =
                                                Auth::user()->role === 'Quản trị viên' ||
                                                $user->role !== 'Quản trị viên';
                                        @endphp

                                        @if (Auth::id() !== $user->id && $canEditStatus)
                                            <form action="{{ route('users.updateStatus', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái của người dùng này?')">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="form-select form-select-sm"
                                                    onchange="confirmStatusChange(this)">
                                                    <option value="Hoạt động"
                                                        {{ $user->status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động
                                                    </option>
                                                    <option value="Khoá" {{ $user->status == 'Khoá' ? 'selected' : '' }}>
                                                        Khoá</option>
                                                </select>
                                            </form>
                                        @else
                                            <span
                                                class="badge {{ $user->status === 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">
                                                {{ $user->status }}
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Không tìm thấy người dùng phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
                <div class="mt-4">
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 15px;
        }

        .card-header {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .table-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover,
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .alert-success,
        .alert-danger {
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }
    </style>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    @endsection


<!-- User Info Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" style="max-width: 650px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="fas fa-user-circle me-2"></i>
                        Thông tin người dùng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body" id="userModalBody">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        Đang tải dữ liệu...
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function confirmRoleChange(selectElement) {
            const confirmed = confirm("Bạn có chắc chắn muốn thay đổi vai trò của người dùng này?");
            if (confirmed) {
                selectElement.form.submit();
            } else {
                // Reload lại trang hoặc khôi phục giá trị cũ nếu cần (tùy chọn)
                window.location.reload(); // đơn giản
            }
        }

        function confirmStatusChange(selectElement) {
            const confirmed = confirm("Bạn có chắc chắn muốn thay đổi trạng thái của người dùng này?");
            if (confirmed) {
                selectElement.form.submit();
            } else {
                window.location.reload();
            }
        }
    </script>
    <script>
        function showUserModal(userId) {
            const modal = new bootstrap.Modal(document.getElementById('userModal'));
            const modalBody = document.getElementById('userModalBody');

            modalBody.innerHTML = '<div class="text-center text-muted">Đang tải dữ liệu...</div>';

            fetch(`/users/${userId}`)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    modal.show();
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="text-danger">Lỗi khi tải dữ liệu người dùng.</div>';
                    console.error(error);
                });
        }
    </script>


<!-- User Info Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" style="max-width: 650px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="fas fa-user-circle me-2"></i>
                        Thông tin người dùng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body" id="userModalBody">
                    <div class="loading">
                        <i class="fas fa-spinner"></i>
                        Đang tải dữ liệu...
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
