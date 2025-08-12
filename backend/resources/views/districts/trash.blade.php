@extends('layouts.app')

@section('title', 'Thùng rác quận/huyện')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header d-flex justify-content-between align-items-center bg-gradient text-white"
                style="background: linear-gradient(90deg, #ff6f61, #ff9a76); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title mb-0">{{ __('Thùng rác') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('districts.index') }}" class="btn btn-sm btn-light text-dark shadow-sm action-icon"
                        style="transition: all 0.3s;">
                        <i class="fas fa-arrow-left me-1"></i>
                        <span class="d-none d-sm-inline">{{ __('Quay lại') }}</span>
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                    <!-- tìm kiếm và lọc -->
                      <div class="mb-4">
                <form action="{{ route('districts.trash') }}" method="GET" class="row g-3">
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
                    <table class="table table-bordered table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width: 8%;">ID</th>
                                <th class="text-center">{{ __('Ảnh khu vực') }}</th>
                                <th class="text-center">{{ __('Tên khu vực') }}</th>
                                <th class="text-center">{{ __('Ngày xóa') }}</th>
                                <th class="text-center" style="width: 25%;">{{ __('Thao tác') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($districts as $district)
                                <tr class="table-row">
                                    <td class="text-center">{{ $district->id }}</td>
                                    <td><img src="{{ $district->image }}" alt="{{ $district->name }}" width="100"></td>
                                    <td>{{ $district->name ?? 'Không có' }}</td>
                                    <td class="text-center">{{ $district->deleted_at->format('d/m/Y H:i:s') }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('districts.restore', $district->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success me-2 action-btn action-icon"
                                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash-restore me-1"></i>
                                                <span class="d-none d-sm-inline">{{ __('Khôi phục') }}</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('districts.forceDelete', $district->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger action-btn action-icon"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash me-1"></i>
                                                <span class="d-none d-sm-inline">{{ __('Xóa vĩnh viễn') }}</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $districts->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            border-left: 5px solid #28a745;
        }
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

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @endsection
@endsection
