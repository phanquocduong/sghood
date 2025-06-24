@extends('layouts.app')

@section('title', 'Sửa nhà trọ')

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
        <div class="card-header bg-gradient text-white d-flex align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <a href="{{ route('motels.index') }}" class="btn btn-light btn-sm me-3 shadow-sm" style="transition: all 0.3s;" title="Quay lại danh sách phòng trọ">
                <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
            </a>
            <h6 class="mb-0 fw-bold">{{ __('Sửa nhà trọ') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('motels.update', $motel->id) }}" method="POST" enctype="multipart/form-data" id="motelForm" novalidate>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold text-primary">Tên dãy trọ</label>
                        <input type="text" class="form-control shadow-sm {{ $errors->has('name') ? 'is-invalid' : '' }}" id="name" name="name" value="{{ old('name', $motel->name) }}" required>
                        @if ($errors->has('name'))
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold text-primary">Địa chỉ</label>
                        <input type="text" class="form-control shadow-sm {{ $errors->has('address') ? 'is-invalid' : '' }}" id="address" name="address" value="{{ old('address', $motel->address) }}" required>
                        @if ($errors->has('address'))
                            <div class="invalid-feedback">
                                {{ $errors->first('address') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="district_id" class="form-label fw-bold text-primary">Quận/Huyện</label>
                        <select class="form-select shadow-sm {{ $errors->has('district_id') ? 'is-invalid' : '' }}" id="district_id" name="district_id" required>
                            <option value="">Chọn quận/huyện</option>
                            @if(isset($districts) && $districts->count() > 0)
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" {{ old('district_id', $motel->district_id) == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                @endforeach
                            @else
                                <option value="">Không có quận/huyện nào.</option>
                            @endif
                        </select>
                        @if ($errors->has('district_id'))
                            <div class="invalid-feedback">
                                {{ $errors->first('district_id') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="map_embed_url" class="form-label fw-bold text-primary">URL nhúng bản đồ</label>
                        <input type="url" class="form-control shadow-sm {{ $errors->has('map_embed_url') ? 'is-invalid' : '' }}" id="map_embed_url" name="map_embed_url" value="{{ old('map_embed_url', $motel->map_embed_url) }}" placeholder="https://maps.google.com/...">
                        @if ($errors->has('map_embed_url'))
                            <div class="invalid-feedback">
                                {{ $errors->first('map_embed_url') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <textarea class="form-control shadow-sm {{ $errors->has('description') ? 'is-invalid' : '' }}" id="description" name="description" rows="3">{{ old('description', $motel->description) }}</textarea>
                        @if ($errors->has('description'))
                            <div class="invalid-feedback">
                                {{ $errors->first('description') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="electricity_fee" class="form-label fw-bold text-primary">Tiền điện (VNĐ/kWh)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('electricity_fee') ? 'is-invalid' : '' }}" id="electricity_fee" name="electricity_fee" value="{{ old('electricity_fee', $motel->electricity_fee) }}" step="1" min="0">
                        @if ($errors->has('electricity_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('electricity_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="water_fee" class="form-label fw-bold text-primary">Tiền nước (VNĐ/m³)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('water_fee') ? 'is-invalid' : '' }}" id="water_fee" name="water_fee" value="{{ old('water_fee', $motel->water_fee) }}" step="1" min="0">
                        @if ($errors->has('water_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('water_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="parking_fee" class="form-label fw-bold text-primary">Phí giữ xe (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('parking_fee') ? 'is-invalid' : '' }}" id="parking_fee" name="parking_fee" value="{{ old('parking_fee', $motel->parking_fee) }}" step="1" min="0">
                        @if ($errors->has('parking_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('parking_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="junk_fee" class="form-label fw-bold text-primary">Phí rác (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('junk_fee') ? 'is-invalid' : '' }}" id="junk_fee" name="junk_fee" value="{{ old('junk_fee', $motel->junk_fee) }}" step="1" min="0">
                        @if ($errors->has('junk_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('junk_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="internet_fee" class="form-label fw-bold text-primary">Phí internet (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('internet_fee') ? 'is-invalid' : '' }}" id="internet_fee" name="internet_fee" value="{{ old('internet_fee', $motel->internet_fee) }}" step="1" min="0">
                        @if ($errors->has('internet_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('internet_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label for="service_fee" class="form-label fw-bold text-primary">Phí dịch vụ (VNĐ/tháng)</label>
                        <input type="number" class="form-control shadow-sm {{ $errors->has('service_fee') ? 'is-invalid' : '' }}" id="service_fee" name="service_fee" value="{{ old('service_fee', $motel->service_fee) }}" step="1" min="0">
                        @if ($errors->has('service_fee'))
                            <div class="invalid-feedback">
                                {{ $errors->first('service_fee') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                        <select class="form-select shadow-sm {{ $errors->has('status') ? 'is-invalid' : '' }}" id="status" name="status" required>
                            <option value="Hoạt động" {{ old('status', $motel->status) == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Không hoạt động" {{ old('status', $motel->status) == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                        @if ($errors->has('status'))
                            <div class="invalid-feedback">
                                {{ $errors->first('status') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="amenities" class="form-label fw-bold text-primary">Tiện ích</label>
                        <div class="row g-2">
                            @if(isset($amenities) && count($amenities) > 0)
                                @foreach($amenities as $amenity)
                                   @if($amenity->type == 'Nhà trọ')
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input {{ $errors->has('amenities') ? 'is-invalid' : '' }}" id="amenity_{{ $amenity->id }}"
                                                name="amenities[]" value="{{ $amenity->id }}"
                                                {{ in_array($amenity->id, old('amenities', $motel->amenities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="amenity_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                        </div>
                                    </div>
                                   @endif
                                @endforeach
                            @else
                                <div class="col-12">
                                    <p class="text-muted">Không có tiện ích nào.</p>
                                </div>
                            @endif
                        </div>
                        @if ($errors->has('amenities'))
                            <div class="invalid-feedback d-block mt-2">
                                {{ $errors->first('amenities') }}
                            </div>
                        @endif
                    </div>
                    <div class="col-12">
                        <label for="existing_images" class="form-label fw-bold text-primary">Hình ảnh hiện tại</label>
                        <div id="existing-image-preview" class="row g-2 mb-3" data-images="{{ json_encode($motel->images ?? []) }}">
                            @if($motel->images && count($motel->images) > 0)
                                <!-- Dữ liệu đã được JavaScript xử lý -->
                            @else
                                <p class="text-muted">Chưa có hình ảnh nào.</p>
                            @endif
                        </div>
                        <label for="images" class="form-label fw-bold text-primary">Thêm hình ảnh mới</label>
                        <input type="file" class="form-control shadow-sm {{ $errors->has('images') ? 'is-invalid' : '' }}" id="images" name="images[]" accept="image/*" multiple>
                        @if ($errors->has('images'))
                            <div class="invalid-feedback">
                                {{ $errors->first('images') }}
                            </div>
                        @endif
                        <div id="image-preview" class="row g-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('motels.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <button type="submit" name="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Cập nhật nhà trọ</button>
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

    #image-preview img, #existing-image-preview img {
        max-height: 100px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 10px;
        transition: transform 0.3s;
    }

    /* #image-preview img:hover, #existing-image-preview img:hover {
        transform: scale(1.1);
    } */

    .image-item .delete-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 10;
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
