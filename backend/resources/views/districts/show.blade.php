@extends('layouts.app')

@section('title', 'Chi tiết khu vực')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(90deg, #4a90e2, #50e3c2);">
                    <h4 class="mb-0">{{ $district->name }}</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-5 position-relative">
                        @if($district->image)
                            <img src="{{ $district->image }}" alt="{{ $district->name }}" class="img-fluid rounded shadow-sm district-image" style="max-height: 350px; object-fit: cover; transition: transform 0.3s;">
                        @else
                            <div class="alert alert-info p-3">Không có hình ảnh</div>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-primary fw-bold">{{ __('Tên khu vực') }}</h5>
                        <p class="lead text-dark">{{ $district->name }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-primary fw-bold">{{ __('Ngày tạo') }}</h5>
                        <p class="text-muted">{{ $district->created_at ? $district->created_at->format('F d, Y h:i A') : 'N/A' }}</p>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-3 mt-5">
                        <a href="{{ route('districts.index') }}" class="btn btn-outline-secondary btn-lg w-100 text-decoration-none" style="transition: all 0.3s;">
                            {{ __('Quay lại') }}
                        </a>
                        <a href="{{ route('districts.edit', $district) }}" class="btn btn-primary btn-lg w-100 text-white text-decoration-none" style="transition: all 0.3s;">
                            {{ __('Sửa') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .district-image:hover {
        transform: scale(1.05);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 15px;
    }

    .bg-gradient {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
</style>
@endsection