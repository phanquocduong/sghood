@extends('layouts.app')

@section('title', 'Người dùng')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Người dùng') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm kiếm người dùng..." class="form-control shadow-sm">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select shadow-sm">
                        <option value="">-- Tất cả vai trò --</option>
                        <option value="Người đăng ký" {{ request('role') == 'Người đăng ký' ? 'selected' : '' }}>Người đăng ký</option>
                        <option value="Người thuê" {{ request('role') == 'Người thuê' ? 'selected' : '' }}>Người thuê</option>
                        <option value="Quản trị viên" {{ request('role') == 'Quản trị viên' ? 'selected' : '' }}>Quản trị viên</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select shadow-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Khoá" {{ request('status') == 'Khoá' ? 'selected' : '' }}>Khoá</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select shadow-sm">
                        <option value="">-- Sắp xếp --</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A → Z</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z → A</option>
                        <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Ngày tạo ↑</option>
                        <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Ngày tạo ↓</option>
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
                            <th scope="col" style="width: 15%;">Ngày tạo</th>
                            <th scope="col" style="width: 15%;">Vai trò</th>
                            <th scope="col" style="width: 15%;">Trạng thái</th>
                            <th scope="col" style="width: 15%;">Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr class="table-row">
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $user->role }}</td>
                                <td>
                                    @php
                                        $badgeClass = $user->status === 'Hoạt động' ? 'bg-success' : 'bg-danger';
                                        $statusText = $user->status;
                                    @endphp
                                    <span class="badge {{ $badgeClass }} py-2 px-3">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.editUser', $user->id) }}" class="btn btn-sm btn-warning action-btn" style="transition: all 0.3s;">
                                        <i class="fas fa-pen me-1"></i> Sửa
                                    </a>
                                </td>
                            </tr>
                        @endforeach
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

    .action-btn:hover, .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }
</style>

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
@endsection
@endsection