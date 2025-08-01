@extends('layouts.app')

@section('title', 'Danh sách cấu hình')

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
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Danh sách cấu hình') }}</h6>
                <div class="d-flex gap-2">
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('configs.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Tìm kiếm cấu hình..." class="form-control shadow-sm">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm" style="transition: all 0.3s;">
                            <i class="fas fa-magnifying-glass me-1"></i> Tìm
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">STT</th>
                                <th scope="col">Khoá</th>
                                <th scope="col">Nội dung</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col" style="width: 15%;">Loại</th>
                                <th scope="col" style="width: 15%;">Chức năng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($configs as $index => $config)
                                <tr class="table-row">
                                    <td>{{ $configs->firstItem() + $index }}</td>
                                    <td>{{ $config->config_key }}</td>

                                    <td>
                                        @if($config->config_type === 'IMAGE')
                                            <img src="{{ $config->config_value }}" alt="Config Image" class="img-thumbnail" style="max-width: 100px; max-height: 50px;">
                                        @else
                                            {{ Str::limit($config->config_value, 50) }}
                                        @endif
                                    </td>
                                    <td>{{ $config->description ?? 'Không có mô tả' }}</td>
                                    <td>{{ $config->config_type }}</td>
                                    <td>
                                        <a href="{{ route('configs.edit', $config->id) }}"
                                            class="btn btn-sm btn-warning action-btn me-2" style="transition: all 0.3s;">
                                            <i class="fas fa-pen me-1"></i> Sửa
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
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
        .card {
            border-radius: 15px;
        }

        .card-header {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .table-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover,
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .alert-success,
        .alert-danger {
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }
    </style>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    @endsection
@endsection