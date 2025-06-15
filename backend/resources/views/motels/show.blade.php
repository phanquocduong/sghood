@extends('layouts.app')

@section('title', 'Chi tiết nhà trọ')

@section('content')
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
                <div class="col-md-6">
                    <p><strong class="text-muted">Địa chỉ:</strong> {{ $motel->address }}</p>
                    <p><strong class="text-muted">Quận/Huyện:</strong> {{ $motel->district->name ?? 'N/A' }}</p>
                    <p><strong class="text-muted">Trạng thái:</strong> <span class="badge {{ $motel->status == 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">{{ $motel->status }}</span></p>
                    <p><strong class="text-muted">Mô tả:</strong> {{ $motel->description ?? 'Không có mô tả' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold mb-3">Chi phí</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Tiền điện:</strong> {{ number_format($motel->electricity_fee ?? 0, 0) }} VNĐ/kWh</p>
                        </div>
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Tiền nước:</strong> {{ number_format($motel->water_fee ?? 0, 0) }} VNĐ/m³</p>
                        </div>
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Phí giữ xe:</strong> {{ number_format($motel->parking_fee ?? 0, 0) }} VNĐ/tháng</p>
                        </div>
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Phí rác:</strong> {{ number_format($motel->junk_fee ?? 0, 0) }} VNĐ/tháng</p>
                        </div>
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Phí internet:</strong> {{ number_format($motel->internet_fee ?? 0, 0) }} VNĐ/tháng</p>
                        </div>
                        <div class="col-md-6">
                            <p class="border-bottom pb-2 mb-2"><strong>Phí dịch vụ:</strong> {{ number_format($motel->service_fee ?? 0, 0) }} VNĐ/tháng</p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <h6 class="text-primary fw-bold">Tiện ích</h6>
                    @if($motel->amenities->count() > 5)
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    @foreach($motel->amenities->take(ceil($motel->amenities->count() / 2)) as $amenity)
                                        <li>{{ $amenity->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    @foreach($motel->amenities->skip(ceil($motel->amenities->count() / 2)) as $amenity)
                                        <li>{{ $amenity->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <ul class="list-unstyled">
                            @forelse($motel->amenities as $amenity)
                                <li>{{ $amenity->name }}</li>
                            @empty
                                <li class="text-muted">Không có tiện ích nào.</li>
                            @endforelse
                        </ul>
                    @endif
                </div>
                <div class="col-12">
                    <h6 class="text-primary fw-bold">Bản đồ</h6>
                    <div class="mb-3">
                        <iframe src="{{ $motel->map_embed_url ?? 'https://maps.google.com' }}" width="100%" height="400" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
                <div class="col-12">
                    <h6 class="text-primary fw-bold">Ảnh nhà trọ</h6>
                    <div class="row g-2">
                        @forelse($motel->images as $image)
                            <div class="col-md-3 mb-2 position-relative">
                                <img src="{{ $image->image_url }}" class="img-fluid rounded shadow-sm motel-image" alt="Motel Image" style="max-height: 150px; object-fit: cover; transition: transform 0.3s;">
                                @if($image->is_main == 1)
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">Ảnh chính</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">Chưa có hình ảnh nào.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2 p-4">
            <a href="{{ route('motels.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Quay lại</a>
            <a href="{{ route('motels.edit', $motel->id) }}" class="btn btn-warning shadow-sm" style="transition: all 0.3s;">Sửa</a>
            <form action="{{ route('motels.destroy', $motel->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger shadow-sm" onclick="return confirm('Bạn có chắc muốn xóa?')" style="transition: all 0.3s;">Xóa</button>
            </form>
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

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .motel-image:hover {
        transform: scale(1.1);
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