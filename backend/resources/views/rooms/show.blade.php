@extends('layouts.app')

@section('title', 'Chi tiết phòng trọ')

@section('content')
<link rel="stylesheet" href="{{ asset('css/show-room.css') }}">

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
            <h6 class="mb-0 fw-bold">{{ __('Chi tiết phòng trọ: ') . $room->name }}</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12">
                    <h5 class="card-title text-primary fw-bold">{{ $room->name }}</h5>
                </div>
                <div class="col-md-6">
                    <p><strong class="text-muted">Nhà trọ:</strong>
                        <a href="{{ route('motels.show', $room->motel_id) }}" class="text-primary text-decoration-none" style="transition: color 0.3s;">{{ $room->motel->name }}</a>
                    </p>
                    <p><strong class="text-muted">Giá phòng:</strong> {{ number_format($room->price, 0, ',', '.') }} VNĐ</p>
                    <p><strong class="text-muted">Diện tích:</strong> {{ $room->area }} m²</p>
                    <p><strong class="text-muted">Trạng thái:</strong>
                        @php
                            $badgeClass = 'bg-secondary';
                            $statusText = 'Không xác định';
                            switch($room->status) {
                                case 'Trống':
                                    $badgeClass = 'bg-success';
                                    $statusText = 'Trống';
                                    break;
                                case 'Đã thuê':
                                    $badgeClass = 'bg-primary';
                                    $statusText = 'Đã thuê';
                                    break;
                                case 'Sửa chữa':
                                    $badgeClass = 'bg-warning text-dark';
                                    $statusText = 'Sửa chữa';
                                    break;
                                case 'Ẩn':
                                    $badgeClass = 'bg-secondary';
                                    $statusText = 'Ẩn';
                                    break;
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }} py-2 px-3">{{ $statusText }}</span>
                    </p>
                    <p><strong class="text-muted">Mô tả:</strong> {{ $room->description ?? 'Không có mô tả.' }}</p>
                    <p><strong class="text-muted">Ghi chú:</strong> {{ $room->note ?? 'Không có ghi chú.' }}</p>
                </div>

                <!-- Tiện nghi đã cải thiện -->
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold mb-3">
                        <i class="fas fa-star text-warning me-2"></i>Tiện nghi
                    </h6>
                    <div class="amenities-container">
                        @forelse($room->amenities as $amenity)
                            <div class="amenity-badge">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>{{ $amenity->name }}</span>
                            </div>
                        @empty
                            <div class="no-amenities">
                                <i class="fas fa-info-circle text-muted me-2"></i>
                                <span class="text-muted">Không có tiện nghi</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Gallery ảnh đã cải thiện -->
                <div class="col-12">
                    <div class="image-gallery-section">
                        <h6 class="text-primary fw-bold mb-3">
                            <i class="fas fa-images text-info me-2"></i>
                            Thư viện ảnh
                            <small class="text-muted">({{ $room->images->count() }} ảnh)</small>
                        </h6>

                        @if($room->images->count() > 0)
                            @php
                                $mainImage = $room->images->where('is_main', 1)->first();
                                $otherImages = $room->images->where('is_main', 0);
                            @endphp

                            <div class="compact-gallery">
                                <!-- Ảnh chính nhỏ gọn -->
                                @if($mainImage)
                                    <div class="main-image-compact mb-3">
                                        <div class="main-image-wrapper-compact">
                                            <img src="{{ asset($mainImage->image_url) }}"
                                                 class="main-image-small"
                                                 alt="Ảnh chính - {{ $room->name }}">
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

                                <!-- Ảnh phụ nhỏ gọn -->
                                @if($otherImages->count() > 0)
                                    <div class="other-images-compact">
                                        <div class="other-images-label mb-2">
                                            <i class="fas fa-image text-primary me-1"></i>
                                            <span class="fw-semibold">Ảnh khác ({{ $otherImages->count() }})</span>
                                        </div>
                                        <div class="other-images-grid-compact">
                                            @foreach($otherImages as $index => $image)
                                                <div class="other-image-item-compact">
                                                    <img src="{{ asset($image->image_url) }}"
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
            <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
            <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning shadow-sm" style="transition: all 0.3s;">
                <i class="fas fa-edit me-1"></i>Sửa
            </a>
            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger shadow-sm" onclick="return confirm('Bạn có chắc muốn xóa?')" style="transition: all 0.3s;">
                    <i class="fas fa-trash me-1"></i>Xóa
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
