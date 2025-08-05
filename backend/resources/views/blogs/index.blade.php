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
    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #6a11cb, #2575fc); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Danh sách bài viết') }}</h6>
                <div>
                <a href="{{ route('blogs.create') }}" class="btn btn-primary me-2 shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-plus me-1"></i> {{ __('Thêm bài viết') }}
                </a>
                <a href="{{ route('blogs.trash') }}" class="btn btn-danger shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i> {{ __('Thùng rác') }}
                </a>
                </div>
            </div>
            <div class="card-body p-4">
                @if (session('success') || session('message'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    {{ session('success') ?: session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="mb-4">
                <form action="{{ route('blogs.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control rounded-3" name="querySearch"
                            placeholder="Tìm theo tiêu đề và tác giả" value="{{ request('querySearch') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select rounded-3" name="status">
                            <option value="">Tất cả trạng thái</option>
                            @foreach (['Đã xuất bản', 'Nháp'] as $status)
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
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Stt</th>
                            <th scope="col">Hình ảnh</th>
                            <th scope="col" style="width: 10%;">Tiêu đề</th>
                            <th scope="col">Bình luận</th>
                            <th scope="col">Tác giả</th>
                            <th scope="col">Thể loại</th>
                            <th scope="col">Ngày đăng</th>
                            <th scope="col">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($blogs->count() > 0)
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
                                    <td><a
                                            href="{{ route('blogs.detail', $blog->id) }}">{{ Str::limit($blog->title, 30) }}</a>
                                    </td>
                                    <td><a href="{{ route('comments.index', $blog->id) }}">{{ $blog->comments_count }}</a>
                                    </td>
                                    <td>{{ $blog->author->name }}</td>
                                    <td>
                                        <form action="{{ route('blogs.updateCategory', $blog->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="category" class="form-select form-select-sm"
                                                onchange="confirmCategoryChange(this)">
                                                <option value="Tin tức"
                                                    {{ $blog->category == 'Tin tức' ? 'selected' : '' }}>
                                                    Tin tức
                                                </option>
                                                <option value="Hướng dẫn"
                                                    {{ $blog->category == 'Hướng dẫn' ? 'selected' : '' }}>
                                                    Hướng dẫn
                                                </option>
                                                <option value="Khuyến mãi"
                                                    {{ $blog->category == 'Khuyến mãi' ? 'selected' : '' }}>Khuyến mãi
                                                </option>
                                                <option value="Pháp luật"
                                                    {{ $blog->category == 'Pháp luật' ? 'selected' : '' }}>Pháp luật
                                                </option>
                                                <option value="Kinh nghiệm"
                                                    {{ $blog->category == 'Kinh nghiệm' ? 'selected' : '' }}>Kinh nghiệm
                                                </option>
                                            </select>
                                        </form>
                                    </td>
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
                                <td colspan="7">
                                    <p>Không có bài viết nào</p>
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
@section('scripts')
    <script src="{{ asset('ckfinder/ckfinder.js') }}"></script>
    <script src="{{ asset('js/blog.js') }}"></script>

    <script>
        function confirmCategoryChange(selectElement) {
            const confirmed = confirm("Bạn có chắc chắn muốn thay đổi thể loại của bài viết này?");
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
