@extends('layouts.app')

@section('title', 'Danh sách khu vực')

@section('content')
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
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Danh sách khu vực') }}</h6>
            <div>
                <a href="{{ route('districts.create') }}" class="btn btn-primary me-2 shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-plus me-1"></i> {{ __('Thêm khu vực') }}
                </a>
                <a href="{{ route('districts.trash') }}" class="btn btn-danger shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i> {{ __('Thùng rác') }}
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <form action="{{ route('districts.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="query" placeholder="Tìm kiếm khu vực..." value="{{ request('query','') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm" style="transition: all 0.3s;">Tìm</button>
                    </div>
                    <!-- lọc -->
                    <div class="col-md-2">
                        <select name="sortOption" class="form-select shadow-sm" onchange="this.form.submit()">
                            <option value="created_at_desc" {{ request('sortOption') == 'created_at_desc' ? 'selected' : '' }}>Sắp xếp theo</option>
                            <option value="name_asc" {{ request('sortOption') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                            <option value="name_dsc" {{ request('sortOption') == 'name_dsc' ? 'selected' : '' }}>Tên Z-A</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" style="width: 5%;">Stt</th>
                            <th scope="col" style="width: 15%;">Ảnh</th>
                            <th scope="col">Tên khu vực</th>
                            <th scope="col" style="width: 20%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($districts as $index => $district)
                            <tr class="table-row">
                                <td>{{ $districts->firstItem() + $index }}</td>
                                <td>
                                    @if (isset($district->image))
                                        <img src="{{ $district->image }}" alt="{{ $district->name }}" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @else
                                        <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image" class="img-fluid rounded motel-image" style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('districts.show', $district->id) }}" class="text-primary fw-bold text-decoration-none" style="transition: color 0.3s;">
                                        {{ $district->name }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('districts.edit', $district->id) }}" class="btn btn-sm btn-primary action-btn me-2" style="transition: all 0.3s;">
                                        <i class="fas fa-edit me-1"></i> Sửa
                                    </a>
                                    <form action="{{ route('districts.destroy', $district->id) }}" method="POST" style="display:inline-block;">
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
                                <td colspan="4" class="text-center text-muted py-4">Không có khu vực nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $districts->appends(request()->query())->links('pagination::bootstrap-5') }}
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

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }

    .text-primary:hover {
        color: #007bff !important;
    }
</style>
@endsection