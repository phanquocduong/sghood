@extends('layouts.app')

@section('title', 'Danh sách nhà trọ')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/index-motel.css') }}">

    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center"
                style="background: linear-gradient(90deg, #6a11cb, #2575fc); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-2 mb-sm-0 fw-bold w-100 text-start text-sm-start">{{ __('Danh sách nhà trọ') }}</h6>
                <div class="d-flex flex-row w-100 w-sm-auto justify-content-between justify-content-sm-end">
                    <a href="{{ route('motels.create') }}" class="btn btn-primary me-2 shadow-sm w-50 w-sm-auto"
                        style="transition: all 0.3s;">
                        <i class="fas fa-plus me-1"></i> {{ __('Thêm nhà trọ') }}
                    </a>
                    <a href="{{ route('motels.trash') }}" class="btn btn-danger shadow-sm w-50 w-sm-auto" style="transition: all 0.3s;">
                        <i class="fas fa-trash me-1"></i> {{ __('Thùng rác') }}
                    </a>
                </div>
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
                    <form action="{{ route('motels.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="querySearch"
                                    placeholder="Tìm kiếm nhà trọ..." value="{{ request('querySearch') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="area" aria-label="Chọn khu vực">
                                <option value="">Tất cả khu vực</option>
                                @if (isset($districts) && $districts->count() > 0)
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}"
                                            {{ request('area') == $district->id ? 'selected' : '' }}>{{ $district->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">Không có quận/huyện nào.</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status" aria-label="Chọn trạng thái">
                                <option value="">Tất cả trạng thái</option>
                                <option value="Hoạt động" {{ request('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt
                                    động</option>
                                <option value="Không hoạt động"
                                    {{ request('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="sortOption" aria-label="Sắp xếp">
                                <option value="">Sắp xếp mặc định</option>
                                <option value="name_asc" {{ request('sortOption') == 'name_asc' ? 'selected' : '' }}>Tên:
                                    A-Z</option>
                                <option value="name_desc" {{ request('sortOption') == 'name_desc' ? 'selected' : '' }}>Tên:
                                    Z-A</option>
                                <option value="created_at_desc"
                                    {{ request('sortOption') == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="created_at_asc"
                                    {{ request('sortOption') == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
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
                                        @php
                                            $mainImage =
                                                $motel->images && $motel->images->count() > 0
                                                    ? $motel->images->where('is_main', 1)->first() ??
                                                        $motel->images->first()
                                                    : null;
                                        @endphp
                                        @if ($mainImage)
                                            <img src="{{ $mainImage->image_url }}" alt="{{ $mainImage->image_url }}"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @else
                                            <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('motels.show', $motel->id) }}"
                                            class="text-primary fw-bold text-decoration-none"
                                            style="transition: color 0.3s;">
                                            {{ $motel->name }}
                                        </a>
                                    </td>
                                    <td class="text-muted">{{ Str::limit($motel->description ?? 'Không có mô tả', 50) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('rooms.index', ['motel_id' => $motel->id]) }}"
                                            class="text-decoration-none">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="fw-bold text-primary">{{ $motel->rooms_count ?? 0 }}
                                                    phòng</span>
                                                <small class="text-muted">
                                                    <span
                                                        class="badge bg-success me-1">{{ $motel->rooms->where('status', 'Trống')->count() }}
                                                        Trống</span>
                                                    <span
                                                        class="badge bg-warning">{{ $motel->rooms->where('status', 'Sửa chữa')->count() }}
                                                        Sửa chữa</span>
                                                </small>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $motel->status == 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">
                                            {{ $motel->status == 'Hoạt động' ? 'Hoạt động' : 'Không hoạt động' }}
                                        </span>
                                    </td>
                                    <td class="d-flex justify-content-center align-items-center">
                                        <a href="{{ route('motels.edit', $motel->id) }}"
                                            class="btn btn-sm btn-primary action-btn me-2 action-icon" style="transition: all 0.3s;">
                                            <i class="fas fa-edit me-1"></i>
                                            <span class="d-none d-sm-inline ms-1">Sửa</span>
                                        </a>
                                        <form action="{{ route('motels.destroy', $motel->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger action-btn action-icon"
                                                onclick="return confirm('Bạn có chắc muốn xóa?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash me-1"></i>
                                                 <span class="d-none d-sm-inline ms-1">Xóa</span>
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

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
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
                    text-align: center;
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
@endsection
