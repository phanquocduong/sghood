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
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Danh sách bình luận') }}</h6>
            </div>
            <div class="card-body p-4">
                @if (session('success') || session('message'))
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn"
                        role="alert">
                        {{ session('success') ?: session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn"
                        role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <div class="mb-4">
                    <form action="{{ route('comments.index', $blog->id) }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control rounded-3" name="querySearch"
                                placeholder="Tìm theo nội dung và tác giả" value="{{ request('querySearch') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select rounded-3" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="visible" {{ request('status') == 'visible' ? 'selected' : '' }}>Hiển thị
                                </option>
                                <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select rounded-3" name="sort_by">
                                <option value="">Sắp xếp theo</option>
                                <option value="created_at_desc"
                                    {{ request('sort_by') == 'created_at_desc' ? 'selected' : '' }}>
                                    Mới nhất</option>
                                <option value="created_at_asc"
                                    {{ request('sort_by') == 'created_at_asc' ? 'selected' : '' }}>Cũ
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
                                <th scope="col">Bài viết</th>
                                <th scope="col">Tác giả</th>
                                <th scope="col">Nội dung</th>
                                <th scope="col">Ngày đăng</th>
                                <th scope="col">Trạng thái</th> {{-- Thêm cột --}}
                                <th scope="col">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comments as $comment)
                                <tr class="table-row" id="comment-{{ $comment->id }}">
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ $comment->blog_id }}</td>
                                    <td>{{ $comment->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->content }}</td>
                                    <td>{{ $comment->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($comment->is_hidden)
                                            <span class="badge bg-secondary">Đã ẩn</span>
                                        @else
                                            <span class="badge bg-success">Hiển thị</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-sm toggle-visibility-btn {{ $comment->is_hidden ? 'btn-success' : 'btn-warning' }}"
                                            data-id="{{ $comment->id }}"
                                            data-url="{{ route('comments.toggleVisibility', [$blog->id, $comment->id]) }}"
                                            data-hidden="{{ $comment->is_hidden ? 1 : 0 }}">
                                            <i class="fas {{ $comment->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                            {{ $comment->is_hidden ? 'Hiện' : 'Ẩn' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $comments->withQueryString()->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    @endsection


    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.toggle-visibility-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        let url = this.dataset.url;

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    let row = document.getElementById('comment-' + this.dataset.id);
                                    let badgeCell = row.querySelector('td:nth-child(6)');
                                    let button = row.querySelector('.toggle-visibility-btn');

                                    if (data.is_hidden) {
                                        badgeCell.innerHTML =
                                            '<span class="badge bg-secondary">Đã ẩn</span>';
                                        button.innerHTML = '<i class="fas fa-eye"></i> Hiện';
                                        button.classList.remove('btn-warning');
                                        button.classList.add('btn-success');
                                    } else {
                                        badgeCell.innerHTML =
                                            '<span class="badge bg-success">Hiển thị</span>';
                                        button.innerHTML = '<i class="fas fa-eye-slash"></i> Ẩn';
                                        button.classList.remove('btn-success');
                                        button.classList.add('btn-warning');
                                    }
                                }
                            });
                    });
                });
            });
        </script>
    @endsection
