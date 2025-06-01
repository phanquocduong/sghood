@extends('layouts.app')

@section('title', 'Danh sách ghi chú')

@section('content')

<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-3 shadow-sm" style="transition: all 0.3s;" title="Quay lại dashboard">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
                </a>
                <h6 class="mb-0 fw-bold">{{ __('Danh sách ghi chú') }}
                    <span class="badge bg-light text-primary ms-2">{{ $notes->total() }} ghi chú</span>
                </h6>
            </div>
            <div>
                <button type="button" class="btn btn-primary me-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal" style="transition: all 0.3s;">
                    <i class="fas fa-plus me-1"></i> {{ __('Thêm ghi chú') }}
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Breadcrumb navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-sticky-note me-1"></i>Ghi chú
                    </li>
                </ol>
            </nav>

            <!-- Filter Form -->
            <div class="mb-4">
                <form action="{{ route('notes.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="query" placeholder="Tìm kiếm nội dung ghi chú..." value="{{ $querySearch }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shadow-sm" name="user_id" onchange="this.form.submit()">
                            <option value="">Tất cả người dùng</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shadow-sm" name="type" onchange="this.form.submit()">
                            <option value="">Tất cả loại</option>
                            <option value="Ghi chú cá nhân" {{ $type == 'Ghi chú cá nhân' ? 'selected' : '' }}>Ghi chú cá nhân</option>
                            <option value="Công việc" {{ $type == 'Công việc' ? 'selected' : '' }}>Công việc</option>
                            <option value="Nhắc nhở" {{ $type == 'Nhắc nhở' ? 'selected' : '' }}>Nhắc nhở</option>
                            <option value="Quan trọng" {{ $type == 'Quan trọng' ? 'selected' : '' }}>Quan trọng</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="sortOption" onchange="this.form.submit()">
                            <option value="">Sắp xếp</option>
                            <option value="content_asc" {{ $sortOption == 'content_asc' ? 'selected' : '' }}>Nội dung A-Z</option>
                            <option value="content_desc" {{ $sortOption == 'content_desc' ? 'selected' : '' }}>Nội dung Z-A</option>
                            <option value="created_at_desc" {{ $sortOption == 'created_at_desc' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="created_at_asc" {{ $sortOption == 'created_at_asc' ? 'selected' : '' }}>Mới nhất</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Notes Table -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;" class="text-center">STT</th>
                            <th scope="col" style="width: 45%;">Nội dung</th>
                            <th scope="col" style="width: 15%;" class="text-center">Loại</th>
                            <th scope="col" style="width: 15%;" class="text-center">Người viết</th>
                            <th scope="col" style="width: 12%;" class="text-center">Ngày tạo</th>
                            <th scope="col" style="width: 15%;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notes as $index => $note)
                            <tr class="table-row">
                                <td class="text-center">{{ $notes->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-sticky-note text-primary me-2"></i>
                                        <span class="note-content">
                                            {{ $note->content }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary py-2 px-3">
                                        <i class="fas fa-tag me-1"></i>{{ $note->type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="fw-medium" style="font-weight: bold; color: #1e90ff;">{{ $note->user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $note->created_at ? $note->created_at->format('d/m/Y') : 'N/A' }}
                                        <br>
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $note->created_at ? $note->created_at->format('H:i') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger action-btn" onclick="return confirm('Bạn có chắc muốn xóa?')" style="transition: all 0.3s;">
                                            <i class="fas fa-trash me-1"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-sticky-note fa-3x mb-3 opacity-50"></i>
                                    <br>
                                    <span class="fs-5">Không có ghi chú nào.</span>
                                    <br>
                                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                        <i class="fas fa-plus me-1"></i> Tạo ghi chú đầu tiên
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="fas fa-plus me-2"></i>Thêm ghi chú mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('notes.store') }}" method="POST" id="addNoteForm" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_content" class="form-label fw-bold">Nội dung ghi chú <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="add_content" name="content" rows="4" placeholder="Nhập nội dung ghi chú..." required maxlength="255"></textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tối đa 255 ký tự</div>
                    </div>
                    <div class="mb-3">
                        <label for="add_type" class="form-label fw-bold">Loại ghi chú <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('type') is-invalid @enderror" id="add_content" name="type" rows="2" placeholder="Nhập loại ghi chú..." required maxlength="255"></textarea>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tối đa 50 ký tự</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Lưu ghi chú
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.breadcrumb {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 0.75rem 1rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    font-weight: bold;
    color: #6c757d;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.table-row:hover {
    background-color: #f8f9fa;
}

.avatar-circle {
    font-size: 14px;
}
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const formText = textarea.nextElementSibling;

        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            formText.textContent = `${currentLength}/${maxLength} ký tự`;

            if (currentLength > maxLength * 0.9) {
                formText.classList.add('text-warning');
            } else {
                formText.classList.remove('text-warning');
            }
        });
    });
});

// Delete note
function deleteNote(noteId) {
    if (confirm('Bạn có chắc chắn muốn xóa ghi chú này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/notes/${noteId}`;
        form.style.display = 'none';

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
