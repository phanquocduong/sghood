@extends('layouts.app')

@section('title', 'Chi tiết phòng trọ')

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
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold mb-3">Tiện nghi</h6>
                    <ul class="list-unstyled">
                        @forelse($room->amenities as $amenity)
                            <li>{{ $amenity->name }}</li>
                        @empty
                            <li class="text-muted">Không có tiện nghi.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-12">
                    <h6 class="text-primary fw-bold">Ảnh phòng trọ</h6>
                    <div class="row g-2">
                        @forelse($room->images as $image)
                            <div class="col-md-3 mb-2 position-relative">
                                <img src="{{ asset($image->image_url) }}" class="img-fluid rounded shadow-sm motel-image" alt="Room Image" style="max-height: 150px; object-fit: cover; transition: transform 0.3s;">
                            </div>
                        @empty
                            <p class="text-muted">Không có ảnh nào.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2 p-4">
            <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Quay lại</a>
            <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning shadow-sm" style="transition: all 0.3s;">Sửa</a>
            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger shadow-sm" onclick="return confirm('Bạn có chắc muốn xóa?')" style="transition: all 0.3s;">Xóa</button>
            </form>
        </div>
    </div>
</div>
@endsection
