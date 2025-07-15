@extends('layouts.app')

@section('title', 'Trang tin nhắn')

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
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4 shadow-lg">
            <h6 class="mb-4">Danh sách bài viết</h6>
            <div class="card-body p-4">
                <form action="{{ route('blogs.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control rounded-3" name="querySearch"
                            placeholder="Tìm theo tiêu đề và tác giả" value="{{ request('querySearch') }}">
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
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">
                            <i class="fas fa-search me-2"></i>Tìm
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-success w-100 rounded-3" href="{{ route('blogs.create') }}">Thêm bài viết</a>
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-danger w-100 rounded-3" href="{{ route('blogs.trash') }}">Thùng rác</a>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Stt</th>
                            <th scope="col">Hình ảnh</th>
                            <th scope="col">Tiêu đề</th>
                            <th scope="col">Tác giả</th>
                            <th scope="col">Ngày đăng</th>
                            <th scope="col">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($blogs)
                            @foreach ($blogs as $blog)
                                <tr class="table-row">
                                    <td>{{ $blog->id }}</td>
                                    <td>
                                        @if ($blog->thumbnail)
                                            <img src="{{ asset($blog->thumbnail) }}" alt="{{ $blog->title }}"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @else
                                            <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @endif
                                    </td>
                                    <td><a href="{{ route('blogs.detail', $blog->id) }}">{{ $blog->title }}</a></td>
                                    <td>{{ $blog->author->name }}</td>
                                    <td>{{ $blog->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('blogs.edit', $blog->id) }}"
                                            class="btn btn-sm btn-primary action-btn me-2" style="transition: all 0.3s;">
                                            <i class="fas fa-edit me-1"></i> Sửa
                                        </a>
                                        <form action="{{ route('blogs.delete', $blog->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger action-btn"
                                                onclick="return confirm('Bạn có chắc muốn xóa?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash me-1"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>
                                    Không có bài viết nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $blogs->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
@endsection
