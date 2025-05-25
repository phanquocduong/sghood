@extends('layouts.app')

@section('title', 'Danh sách phòng trọ')

@section('content')

<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <div class="d-flex align-items-center">
                <a href="{{ route('motels.index') }}" class="btn btn-light btn-sm me-3 shadow-sm" style="transition: all 0.3s;" title="Quay lại danh sách nhà trọ">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
                </a>
                <h6 class="mb-0 fw-bold">{{ __('Danh sách phòng trọ') }}
                    <span class="badge bg-light text-primary ms-2">{{ $motel->name ?? 'ID: ' . $motelId }}</span>
                </h6>
            </div>
            <div>
                <a href="{{ route('rooms.create', ['motel_id' => $motelId]) }}" class="btn btn-primary me-2 shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-plus me-1"></i> {{ __('Thêm phòng trọ') }}
                </a>
                <a href="{{ route('rooms.trash', ['motel_id' => $motelId]) }}" class="btn btn-danger shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i> {{ __('Thùng rác') }}
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Breadcrumb navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('motels.index') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>Nhà trọ
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-door-open me-1"></i>Phòng trọ
                    </li>
                </ol>
            </nav>

            <div class="mb-4">
                <form action="{{ route('rooms.index') }}" method="GET" class="row g-3">
                    <input type="hidden" name="motel_id" value="{{ $motelId }}">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="query" placeholder="Tìm kiếm phòng trọ..." value="{{ $querySearch }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shadow-sm" name="status" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Trống" {{ $status == 'Trống' ? 'selected' : '' }}>Trống</option>
                            <option value="Đã thuê" {{ $status == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="Sửa chữa" {{ $status == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                            <option value="Ẩn" {{ $status == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shadow-sm" name="sortOption" onchange="this.form.submit()">
                            <option value="">Sắp xếp</option>
                            <option value="name_asc" {{ $sortOption == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                            <option value="name_desc" {{ $sortOption == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                            <option value="created_at_asc" {{ $sortOption == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="created_at_desc" {{ $sortOption == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                        </select>
                    </div>
                </form>
            </div>
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
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;">Stt</th>
                            <th scope="col" style="width: 15%;">Ảnh</th>
                            <th scope="col">Tên phòng trọ</th>
                            <th scope="col">Ghi chú</th>
                            <th scope="col" style="width: 15%;">Trạng thái</th>
                            <th scope="col" style="width: 20%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $index => $room)
                            <tr class="table-row">
                                <td>{{ $rooms->firstItem() + $index }}</td>
                                <td>
                                    @if ($room->main_image)
                                        <img src="{{ asset($room->main_image->image_url) }}" alt="{{ $room->name }}" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @else
                                        <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('rooms.show', $room->id) }}" class="text-primary fw-bold text-decoration-none" style="transition: color 0.3s;">
                                        {{ $room->name }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($room->note ?? 'Không có ghi chú', 50) }}</td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        $statusText = 'Không xác định';

                                        switch($room->status) {
                                            case 'Trống':
                                                $badgeClass = 'bg-success';
                                                $statusText = 'Trống';
                                                break;
                                            case 'Đã thuê':
                                                $badgeClass = 'bg-primary';
                                                $statusText = 'Đã thuê';
                                                break;
                                            case 'Sửa chữa':
                                                $badgeClass = 'bg-warning text-dark';
                                                $statusText = 'Sửa chữa';
                                                break;
                                            case 'Ẩn':
                                                $badgeClass = 'bg-secondary';
                                                $statusText = 'Ẩn';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} py-2 px-3">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-sm btn-primary action-btn me-2" style="transition: all 0.3s;">
                                        <i class="fas fa-edit me-1"></i> Sửa
                                    </a>
                                    <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="6" class="text-center text-muted py-4">Không có phòng trọ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $rooms->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
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

.motel-image:hover {
    transform: scale(1.05);
}
</style>

@endsection
