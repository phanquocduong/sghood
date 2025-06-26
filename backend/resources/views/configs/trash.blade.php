@extends('layouts.app')

@section('title', 'Thùng rác cấu hình')

@section('content')

<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Thùng rác cấu hình') }}</h6>
            <div>
                <a href="{{ route('configs.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại danh sách') }}
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Breadcrumb navigation -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('configs.index') }}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i>Cấu hình
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="fas fa-door-open me-1"></i>Thùng rác
                        </li>
                    </ol>
                </nav>
            <div class="mb-4">
                <form action="{{ route('configs.trash') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control shadow-sm" name="search" placeholder="Tìm kiếm cấu hình..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm" style="transition: all 0.3s;">Tìm</button>
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
                            <th scope="col">Khóa</th>
                            <th scope="col">Giá trị</th>
                            <th scope="col">Mô tả</th>
                            <th scope="col" style="width: 15%;">Loại</th>
                            <th scope="col" style="width: 15%;">Ngày xóa</th>
                            <th scope="col" style="width: 25%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($configs as $index => $config)
                            <tr class="table-row">
                                <td>{{ $configs->firstItem() + $index }}</td>
                                <td>{{ $config->config_key }}</td>
                                <td>
                                    @if($config->config_type == 'IMAGE' && $config->config_value)
                                        <img src="{{ asset($config->config_value) }}" alt="{{ $config->config_key }}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                    @else
                                        {{ Str::limit($config->config_value, 50) }}
                                    @endif
                                </td>
                                <td>{{ $config->description ?? 'Không có mô tả' }}</td>
                                <td>{{ $config->config_type }}</td>
                                <td>{{ $config->deleted_at->setTimezone('Asia/Ho_Chi_Minh')->locale('vi')->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form action="{{ route('configs.restore', $config->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success action-btn me-2" onclick="return confirm('Bạn có chắc muốn khôi phục?')" style="transition: all 0.3s;">
                                            <i class="fas fa-undo me-1"></i> Khôi phục
                                        </button>
                                    </form>
                                    <form action="{{ route('configs.forceDelete', $config->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger action-btn" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn?')" style="transition: all 0.3s;">
                                            <i class="fas fa-trash me-1"></i> Xóa vĩnh viễn
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Không có cấu hình nào trong thùng rác.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $configs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<style>
    .table-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.3s ease;
    }

    .action-btn:hover, .btn-primary:hover, .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }
</style>

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
@endsection
