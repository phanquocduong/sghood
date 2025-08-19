@extends('layouts.app')

@section('title', 'Thùng rác tiện ích')

@section('content')
    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Thùng rác tiện ích') }}</h6>
                <div>
                    <a href="{{ route('amenities.index') }}" class="btn btn-secondary shadow-sm action-icon" style="transition: all 0.3s;">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span class="d-none d-sm-inline"> {{ __('Quay lại danh sách') }}</span>
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <form action="{{ route('amenities.trash') }}" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control shadow-sm" name="query"
                                    placeholder="Tìm kiếm tiện ích..." value="{{ $querySearch ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="sortOption" onchange="this.form.submit()">
                                <option value="">Sắp xếp</option>
                                <option value="name_asc" {{ $sortOption == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                                <option value="name_desc" {{ $sortOption == 'name_desc' ? 'selected' : '' }}>Tên Z-A
                                </option>
                                <option value="created_at_asc" {{ $sortOption == 'created_at_asc' ? 'selected' : '' }}>Cũ
                                    nhất</option>
                                <option value="created_at_desc" {{ $sortOption == 'created_at_desc' ? 'selected' : '' }}>Mới
                                    nhất</option>
                            </select>
                        </div>
                    </form>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn"
                        role="alert">
                        {{ session('success') }}
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
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">Stt</th>
                                <th scope="col">Tên tiện ích</th>
                                <th scope="col">Loại</th>
                                <th scope="col">Thứ tự</th>
                                <th scope="col" style="width: 15%;">Trạng thái</th>
                                <th scope="col" style="width: 20%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($amenities as $index => $amenity)
                                <tr class="table-row">
                                    <td>{{ $amenities->firstItem() + $index }}</td>
                                    <td>{{ $amenity->name }}</td>
                                    <td>{{ $amenity->type }}</td>
                                    <td>{{ $amenity->order }}</td>
                                    <td>
                                        @php
                                            $badgeClass =
                                                $amenity->status === 'Hoạt động' ? 'bg-success' : 'bg-secondary';
                                            $statusText =
                                                $amenity->status === 'Hoạt động' ? 'Hoạt động' : 'Không hoạt động';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} py-2 px-3">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <form action="{{ route('amenities.restore', $amenity->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('POST')
                                                <button type="submit"
                                                    class="btn btn-sm btn-success action-btn me-2 action-icon"
                                                    onclick="return confirm('Bạn có chắc muốn khôi phục?')"
                                                    style="transition: all 0.3s;">
                                                    <i class="fas fa-undo me-1"></i>
                                                    <span class="d-none d-sm-inline ms-1">Khôi phục</span>
                                                </button>
                                            </form>
                                            <form action="{{ route('amenities.forceDelete', $amenity->id) }}"
                                                method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-btn action-icon"
                                                    onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn?')"
                                                    style="transition: all 0.3s;">
                                                    <i class="fas fa-trash me-1"></i>
                                                    <span class="d-none d-sm-inline ms-1">Xóa</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Không có tiện ích nào trong thùng
                                        rác.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $amenities->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
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
