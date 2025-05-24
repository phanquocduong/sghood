@extends('layouts.app')

@section('title', 'Danh sách nhà trọ')

@section('content')
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #6a11cb, #2575fc); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Danh sách nhà trọ') }}</h6>
            <div>
                <a href="{{ route('motels.create') }}" class="btn btn-primary me-2 shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-plus me-1"></i> {{ __('Thêm nhà trọ') }}
                </a>
                <a href="{{ route('motels.trash') }}" class="btn btn-danger shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i> {{ __('Thùng rác') }}
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="mb-4">
                <form action="{{ route('motels.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="querySearch" placeholder="Tìm kiếm nhà trọ..." value="{{ request('querySearch') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="area" aria-label="Chọn khu vực">
                            <option value="">Tất cả khu vực</option>
                            @if(isset($districts) && $districts->count() > 0)
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ request('area') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                @endforeach
                            @else
                                <option value="">Không có quận/huyện nào.</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status" aria-label="Chọn trạng thái">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Không hoạt động" {{ request('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="sortOption" aria-label="Sắp xếp">
                            <option value="">Sắp xếp mặc định</option>
                            <option value="name_asc" {{ request('sortOption') == 'name_asc' ? 'selected' : '' }}>Tên: A-Z</option>
                            <option value="name_desc" {{ request('sortOption') == 'name_desc' ? 'selected' : '' }}>Tên: Z-A</option>
                            <option value="created_at_desc" {{ request('sortOption') == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="created_at_asc" {{ request('sortOption') == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100" style="transition: all 0.3s;">Lọc</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;">Stt</th>
                            <th scope="col" style="width: 15%;">Ảnh</th>
                            <th scope="col">Tên nhà trọ</th>
                            <th scope="col">Mô tả</th>
                            <th scope="col">Số lượng phòng</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col" style="width: 20%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($motels as $index => $motel)
                            <tr class="table-row">
                                <td>{{ $motels->firstItem() + $index }}</td>
                                <td>
                                    @if ($motel->images && $motel->images->count() > 0)
                                        <img src="{{ $motel->images->first()->image_url }}" alt="{{ $motel->name }}" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @else
                                        <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('motels.show', $motel->id) }}" class="text-primary fw-bold text-decoration-none" style="transition: color 0.3s;">
                                        {{ $motel->name }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ Str::limit($motel->description ?? 'Không có mô tả', 50) }}</td>
                                <td>
                                      <a href="{{ route('rooms.index', ['motel_id'=> $motel->id]) }}" class="text-success text-decoration-none">{{ $motel->rooms_count ?? 0 }} phòng</a>
                                </td>
                                <td>
                                    <span class="badge {{ $motel->status == 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">
                                        {{ $motel->status == 'Hoạt động' ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('motels.edit', $motel->id) }}" class="btn btn-sm btn-primary action-btn me-2" style="transition: all 0.3s;">
                                        <i class="fas fa-edit me-1"></i> Sửa
                                    </a>
                                    <form action="{{ route('motels.destroy', $motel->id) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="7" class="text-center text-muted py-4">Không có nhà trọ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $motels->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    .table-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.3s ease;
    }

    .motel-image:hover {
        transform: scale(1.1);
    }

    .action-btn:hover, .btn-primary:hover, .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-success {
        border-left: 5px solid #28a745;
    }

    .text-primary:hover {
        color: #6a11cb !important;
    }
</style>

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
@endsection
