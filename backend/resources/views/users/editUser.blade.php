@extends('layouts.app')

@section('title', 'Sửa người dùng')

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
            <h6 class="mb-0 fw-bold">{{ __('Sửa người dùng') }}</h6>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('users.updateUser', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data" id="userEditForm" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="role" class="form-label fw-bold text-primary">Vai trò</label>
                        <select name="role" class="form-select shadow-sm" id="role">
                            <option value="Quản trị viên" {{ $user->role == 'Quản trị viên' ? 'selected' : '' }}>Quản trị viên</option>
                            <option value="Người thuê" {{ $user->role == 'Người thuê' ? 'selected' : '' }}>Người thuê</option>
                            <option value="Người đăng ký" {{ $user->role == 'Người đăng ký' ? 'selected' : '' }}>Người đăng ký</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                        <select class="form-select shadow-sm mb-3" name="status" id="status" aria-label="Default select example">
                            <option value="Hoạt động" {{ $user->status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Khóa" {{ $user->status == 'Khóa' ? 'selected' : '' }}>Khóa</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('users.user') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Trở lại</a>
                    <button type="submit" class="btn btn-success shadow-sm" style="transition: all 0.3s;">Sửa người dùng</button>
                </div>
            </form>
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

    .btn:hover {
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
@endsection
@endsection