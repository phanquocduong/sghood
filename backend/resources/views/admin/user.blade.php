@extends('layouts.app')
@section('content')
<div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Người dùng</h6>
            <form action="" class="mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-9 col-sm-11">
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm kiếm người dùng..." class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">-- Tất cả vai trò --</option>
                            <option value="Người đăng ký" {{ request('role') == 'Người đăng ký' ? 'selected' : '' }}>Người đăng ký</option>
                            <option value="Người thuê" {{ request('role') == 'Người thuê' ? 'selected' : '' }}>Người thuê</option>
                            <option value="Quản trị viên" {{ request('role') == 'Quản trị viên' ? 'selected' : '' }}>Quản trị viên</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Khoá" {{ request('status') == 'Khoá' ? 'selected' : '' }}>Khoá</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="">-- Sắp xếp --</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A → Z</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z → A</option>
                            <option value="created_at_asc" {{ request('sort') == 'created_at_asc' ? 'selected' : '' }}>Ngày tạo ↑</option>
                            <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>Ngày tạo ↓</option>
                        </select>

                    </div>
                    <div class="col-3 col-sm-1">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
                <table class="table text-center align-middle">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Họ tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Ngày tạo</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Chức năng</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($users as $user)
                       <tr>
                           <td data-label="STT">{{ $user->id }}</td>
                           <td data-label="Họ tên">{{ $user->name }}</td>
                           <td data-label="Số điện thoại">{{ $user->phone }}</td>
                            <td data-label="Email">{{ $user->email }}</td>
                            <td data-label="Ngày tạo">{{ $user->created_at->format('d/m/Y') }}</td>
                           <td data-label="Vai trò">{{ $user->role }}</td>
                           <td data-label="Trạng thái">{{ $user->status }}</td>
                           <td data-label="Chức năng">
                               <a href="{{ route('admin.editUser', $user->id) }}">
                                   <button class="btn btn-warning"><i class="fa-solid fa-pen"></i></button>
                               </a>
                           </td>
                       </tr>
                       @endforeach
                   </tbody>
               </table>
               <div>
                   {{ $users->links() }}
               </div>
           </div>
        </div>
    </div>
    <!-- Table End -->

@endsection
