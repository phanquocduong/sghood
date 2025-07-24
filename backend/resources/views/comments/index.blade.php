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
            <h6 class="mb-4">Danh sách bình luận bài viết</h6>
            <div class="card-body p-4">
                <form action="{{ route('blogs.comment', $blog->id) }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control rounded-3" name="querySearch"
                            placeholder="Tìm theo nội dung và tác giả" value="{{ request('querySearch') }}">
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
                    {{-- <div class="col-md-2">
                        <a class="btn btn-success w-100 rounded-3" href="{{ route('comments.create') }}">Thêm bài viết</a>
                    </div>
                    <div class="col-md-2">
                        <a class="btn btn-danger w-100 rounded-3" href="{{ route('comments.trash') }}">Thùng rác</a>
                    </div> --}}
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
                            <th scope="col">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($comments->count() > 0)
                            @foreach ($comments as $comment)
                                <tr class="table-row">
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ $comment->blog_id }}</td>
                                    <td>{{ $comment->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->content }}</td>
                                    <td>{{ $comment->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm reply-btn"
                                            data-id="{{ $comment->id }}" data-content="{{ $comment->content }}"
                                            data-bs-toggle="modal" data-bs-target="#replyModal" title="Trả lời">
                                            <i class="fas fa-reply"></i>Trả lời
                                        </button>
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
                {{ $comments->withQueryString()->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
@endsection
<!-- Modal trả lời bình luận -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('blogs.comment.reply', $blog->id) }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" id="replyParentId">
                <input type="hidden" name="blog_id" value="{{ $blog->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Trả lời bình luận</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="replyContent" class="form-label">Trả lời cho bình luận</label>
                        <p id="parentCommentContent" class="text-muted fst-italic"></p>
                    </div>

                    <div class="mb-3">
                        <label for="replyContent" class="form-label">Nội dung trả lời</label>
                        <textarea name="content" id="replyContent" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi trả lời</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
        document.addEventListener('DOMContentLoaded', () => {
            const replyModal = document.getElementById('replyModal');
            replyModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const commentId = button.getAttribute('data-id');
                const commentContent = button.getAttribute('data-content');

                const parentInput = replyModal.querySelector('#replyParentId');
                const parentContentP = replyModal.querySelector('#parentCommentContent');

                parentInput.value = commentId;
                parentContentP.textContent = commentContent;
            });
        });
    </script>
@endsection
