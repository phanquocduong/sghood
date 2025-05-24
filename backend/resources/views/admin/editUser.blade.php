@extends('layouts.app')
@section('content')
<div class="container-fluid pt-4 px-4">
    <!-- Table Start -->

    <div class="col-12">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Sửa người dùng</h6>
            <form action="{{ route('admin.updateUser', ['id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <label class="fw-bold col-sm-3 col-form-label" for="">Vai trò:</label>
                <select name="role" class="form-select">
                    <option value="Quản trị viên" {{ $user->role == 'Quản trị viên' ? 'selected' : '' }}>Quản trị viên</option>
                    <option value="Người thuê" {{ $user->role == 'Người thuê' ? 'selected' : '' }}>Người thuê</option>
                    <option value="Người đăng ký" {{ $user->role == 'Người đăng ký' ? 'selected' : '' }}>Người đăng ký</option>
                </select>

                <label class="fw-bold col-sm-3 col-form-label" for="">Trạng thái:</label>
                <select class="form-select mb-3" name="status" aria-label="Default select example">
                    <option value="Hoạt động" {{ $user->status == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="Khóa" {{ $user->status == 'Khóa' ? 'selected' : '' }}>Khóa</option>
                </select>

                <a class="btn btn-secondary" href="{{ route('admin.users') }}">Trở lại</a>
                <button type="submit" class="btn btn-success m-2">Sửa người dùng</button>

            </form>
        </div>
    </div>
</div>
@endsection
