@extends('layouts.app')

@section('title', 'Trang thùng rác bài viết')

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
            <h6 class="mb-4">Danh sách bài viết đã xóa</h6>
            <div class="card-body p-4">
                <form action="{{ route('blogs.trash') }}" method="GET" class="row g-3 mb-4">
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
                    <div class="col-md-3">
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
                        <a class="btn btn-danger w-100 rounded-3" href="{{ route('blogs.index') }}">Quay lại</a>
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
                                <td>{{ $blog->title }}</td>
                                <td>{{ $blog->author->name }}</td>
                                <td>{{ $blog->created_at->format('d/m/Y') }}</td>
                                <td class="d-flex justify-content-center align-items-center">
                                    <form action="{{ route('blogs.restore', $blog->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success action-btn action-icon"
                                            onclick="return confirm('Bạn có chắc muốn khôi phục?')"
                                            style="transition: all 0.3s;">
                                            <i class="fas fa-undo"></i>
                                            <span class="d-none d-sm-inline ms-1">Khôi phục bài viết</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('blogs.force-delete', $blog->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger action-btn"
                                            onclick="return confirm('Bạn có chắc muốn xóa bài viết vĩnh viễn?')" style="transition: all 0.3s;">
                                            <i class="fas fa-trash me-1"></i>
                                            <span class="d-none d-sm-inline">Xóa bài viết</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('styles')
        <style>
            @media (max-width: 576px) {

                /* Nút hành động trên mobile chỉ là icon tròn */
                .action-icon {
                    padding: 6px 8px;
                    /* Nhỏ gọn */
                    border-radius: 50%;
                    /* Bo tròn */
                    width: 36px;
                    height: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center
                }

                /* Icon căn giữa */
                .action-icon i {
                    margin: 0 !important;
                    font-size: 14px;
                }

                .card-header .btn {
                    font-size: 14px;
                    padding: 6px 8px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
            }
        </style>
    @endsection
