@extends('layouts.app')

@section('title', 'Thêm nhà trọ')

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
            <h6 class="mb-0 fw-bold">{{ __('Thêm nhà trọ') }}</h6>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('motels.store') }}" method="POST" enctype="multipart/form-data" id="motelForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-primary">Tên dãy trọ</label>
                        <input type="text" class="form-control shadow-sm" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold text-primary">Địa chỉ</label>
                        <input type="text" class="form-control shadow-sm" id="address" name="address" value="{{ old('address') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="district_id" class="form-label fw-bold text-primary">Quận/Huyện</label>
                        <select class="form-select shadow-sm" id="district_id" name="district_id" required>
                            <option value="">Chọn quận/huyện</option>
                            @if(isset($districts) && $districts->count() > 0)
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ request('district_id') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                @endforeach
                            @else
                                <option value="">Không có quận/huyện nào.</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="map_embed_url" class="form-label fw-bold text-primary">URL nhúng bản đồ</label>
                        <input type="url" class="form-control shadow-sm" id="map_embed_url" name="map_embed_url" value="{{ old('map_embed_url') }}" placeholder="https://maps.google.com/...">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="electricity_fee" class="form-label fw-bold text-primary">Tiền điện (VNĐ/kWh)</label>
                        <input type="number" class="form-control shadow-sm" id="electricity_fee" name="electricity_fee" value="{{ old('electricity_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label for="water_fee" class="form-label fw-bold text-primary">Tiền nước (VNĐ/m³)</label>
                        <input type="number" class="form-control shadow-sm" id="water_cost" name="water_fee" value="{{ old('water_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label for="parking_fee" class="form-label fw-bold text-primary">Phí giữ xe (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm" id="parking_fee" name="parking_fee" value="{{ old('parking_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label for="junk_fee" class="form-label fw-bold text-primary">Phí rác (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm" id="junk_fee" name="junk_fee" value="{{ old('junk_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label for="internet_fee" class="form-label fw-bold text-primary">Phí internet (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm" id="internet_fee" name="internet_fee" value="{{ old('internet_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label for="service_fee" class="form-label fw-bold text-primary">Phí dịch vụ (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm" id="service_fee" name="service_fee" value="{{ old('service_fee') }}" step="1" min="0">
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                        <select class="form-select shadow-sm" id="status" name="status" required>
                            <option value="Hoạt động" {{ old('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Không hoạt động" {{ old('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="amenities" class="form-label fw-bold text-primary">Tiện ích</label>
                        <div class="row g-2">
                            @if(isset($amenities) && count($amenities) > 0)
                                @foreach($amenities as $amenity)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="amenity_{{ $amenity->id }}"
                                                name="amenities[]" value="{{ $amenity->id }}"
                                                {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="amenity_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted">Không có tiện ích nào.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="images" class="form-label fw-bold text-primary">Hình ảnh</label>
                        <input type="file" class="form-control shadow-sm" id="images" name="images[]" accept="image/*" multiple>
                        <div id="image-preview" class="row g-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('motels.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <button type="submit" name="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm nhà trọ</button>
                </div>
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

    #image-preview img {
        max-height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 10px;
        transition: transform 0.3s;
    }

    #image-preview img:hover {
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