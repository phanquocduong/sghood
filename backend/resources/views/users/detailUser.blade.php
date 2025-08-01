
@extends('layouts.app')

@section('title', 'Chi tiết người dùng')

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
            <h6 class="mb-0 fw-bold">{{ __('Chi tiết người dùng') }}</h6>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Thông tin người dùng</h5>
                    <p><strong></strong> <img src="{{ $user->avatar }}" alt="Avatar" class="img-fluid" style="max-width: 150px; border-radius: 10px;"></p>
                    <p><strong>Họ tên:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $user->phone }}</p>
                    <p><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Thông tin bổ sung</h5>
                    <p><strong>Địa chỉ:</strong> {{ $user->address }}</p>
                    <p><strong>Ngày sinh:</strong> {{ $user->birthdate->format('d/m/Y') }}</p>
                    <p><strong>Giới tính:</strong> {{ $user->gender }}</p>
                    <p><strong>Vai trò:</strong> {{ $user->role }}</p>
                    <p><strong>Trạng thái:</strong> {{ $user->status }}</p>
                </div>
                <a href="{{ route('users.user') }}" class="btn btn-primary">Quay lại</a>
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
@endsection
