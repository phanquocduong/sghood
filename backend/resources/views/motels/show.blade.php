@extends('layouts.app')

@section('title', 'Chi tiết nhà trọ')

@section('content')
<link rel="stylesheet" href="{{ asset('css/show-motel.css') }}">
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
            <h6 class="mb-0 fw-bold">{{ __('Chi tiết nhà trọ: ') . $motel->name }}</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12">
                    <h5 class="card-title text-primary fw-bold">{{ $motel->name }}</h5>
                </div>

                <!-- Thông tin cơ bản -->
                <div class="col-md-6">
                    <p><strong class="text-muted">Địa chỉ:</strong> {{ $motel->address }}</p>
                    <p><strong class="text-muted">Quận/Huyện:</strong> {{ $motel->district->name ?? 'N/A' }}</p>
                    <p><strong class="text-muted">Trạng thái:</strong>
                        <span class="badge {{ $motel->status == 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">{{ $motel->status }}</span>
                    </p>
                    <p><strong class="text-muted">Mô tả:</strong> {{ $motel->description ?? 'Không có mô tả' }}</p>
                </div>

                <!-- Chi phí cải tiến -->
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold mb-3">
                        <i class="fas fa-money-bill-wave text-success me-2"></i>Chi phí dịch vụ
                    </h6>
                    <div class="fees-container">
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-bolt text-warning"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Tiền điện</span>
                                <span class="fee-value">{{ number_format($motel->electricity_fee ?? 0, 0) }} VNĐ/kWh</span>
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-tint text-info"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Tiền nước</span>
                                <span class="fee-value">{{ number_format($motel->water_fee ?? 0, 0) }} VNĐ/m³</span>
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-car text-primary"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Phí giữ xe</span>
                                <span class="fee-value">{{ number_format($motel->parking_fee ?? 0, 0) }} VNĐ/tháng</span>
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-trash text-secondary"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Phí rác</span>
                                <span class="fee-value">{{ number_format($motel->junk_fee ?? 0, 0) }} VNĐ/tháng</span>
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-wifi text-success"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Phí internet</span>
                                <span class="fee-value">{{ number_format($motel->internet_fee ?? 0, 0) }} VNĐ/tháng</span>
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-icon">
                                <i class="fas fa-concierge-bell text-info"></i>
                            </div>
                            <div class="fee-content">
                                <span class="fee-label">Phí dịch vụ</span>
                                <span class="fee-value">{{ number_format($motel->service_fee ?? 0, 0) }} VNĐ/tháng</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tiện ích cải tiến -->
                <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3">
                        <i class="fas fa-star text-warning me-2"></i>Tiện ích
                    </h6>
                    <div class="amenities-container">
                        @forelse($motel->amenities as $amenity)
                            <div class="amenity-badge">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>{{ $amenity->name }}</span>
                            </div>
                        @empty
                            <div class="no-amenities">
                                <i class="fas fa-info-circle text-muted me-2"></i>
                                <span class="text-muted">Không có tiện ích nào</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Bản đồ cải tiến -->
                <div class="col-12">
                    <div class="map-section">
                        <h6 class="text-primary fw-bold mb-3">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>Vị trí trên bản đồ
                        </h6>
                        <div class="map-container">
                            <iframe src="{{ $motel->map_embed_url ?? 'https://maps.google.com' }}"
                                    width="100%"
                                    height="350"
                                    style="border:0; border-radius: 12px;"
                                    allowfullscreen=""
                                    loading="lazy">
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- Gallery ảnh cải tiến -->
                <div class="col-12">
                    <div class="image-gallery-section">
                        <h6 class="text-primary fw-bold mb-3">
                            <i class="fas fa-images text-info me-2"></i>
                            Thư viện ảnh
                            <small class="text-muted">({{ $motel->images->count() }} ảnh)</small>
                        </h6>

                        @if($motel->images->count() > 0)
                            @php
                                $mainImage = $motel->images->where('is_main', 1)->first();
                                $otherImages = $motel->images->where('is_main', 0);
                            @endphp

                            <div class="compact-gallery">
                                <!-- Ảnh chính -->
                                @if($mainImage)
                                    <div class="main-image-compact mb-3">
                                        <div class="main-image-wrapper-compact">
                                            <img src="{{ $mainImage->image_url }}"
                                                 class="main-image-small"
                                                 alt="Ảnh chính - {{ $motel->name }}">
                                            <div class="main-badge-compact">
                                                <i class="fas fa-star"></i>
                                            </div>
                                        </div>
                                        <div class="main-image-label">
                                            <i class="fas fa-crown text-warning me-1"></i>
                                            <span class="fw-bold">Ảnh chính</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Ảnh phụ -->
                                @if($otherImages->count() > 0)
                                    <div class="other-images-compact">
                                        <div class="other-images-label mb-2">
                                            <i class="fas fa-image text-primary me-1"></i>
                                            <span class="fw-semibold">Ảnh khác ({{ $otherImages->count() }})</span>
                                        </div>
                                        <div class="other-images-grid-compact">
                                            @foreach($otherImages as $index => $image)
                                                <div class="other-image-item-compact">
                                                    <img src="{{ $image->image_url }}"
                                                         class="other-image-small"
                                                         alt="Ảnh {{ $index + 2 }}">
                                                    <div class="image-number-compact">{{ $index + 2 }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="no-images-compact">
                                <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Chưa có ảnh nào</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2 p-4">
            <a href="{{ route('motels.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
            <a href="{{ route('motels.edit', $motel->id) }}" class="btn btn-warning shadow-sm" style="transition: all 0.3s;">
                <i class="fas fa-edit me-1"></i>Sửa
            </a>
            <form action="{{ route('motels.destroy', $motel->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger shadow-sm" onclick="return confirm('Bạn có chắc muốn xóa?')" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i>Xóa
                </button>
            </form>
        </div>
    </div>
</div>
@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
@endsection
