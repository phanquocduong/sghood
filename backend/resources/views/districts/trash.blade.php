@extends('layouts.app')

@section('title', 'Thùng rác quận/huyện')

@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header d-flex justify-content-between align-items-center bg-gradient text-white"
                style="background: linear-gradient(90deg, #ff6f61, #ff9a76); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title mb-0">{{ __('Thùng rác quận/huyện') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('districts.index') }}" class="btn btn-sm btn-light text-dark shadow-sm"
                        style="transition: all 0.3s;">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
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
                                            <button type="submit" class="btn btn-sm btn-success me-2 action-btn"
                                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash-restore me-1"></i> {{ __('Khôi phục') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('districts.forceDelete', $district->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger action-btn"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn?')"
                                                style="transition: all 0.3s;">
                                                <i class="fas fa-trash me-1"></i> {{ __('Xóa vĩnh viễn') }}
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
    </style>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @endsection
@endsection