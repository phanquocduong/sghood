```html
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
                <form action="{{ route('motels.store') }}" method="POST" enctype="multipart/form-data" id="motelForm" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold text-primary">Tên dãy trọ</label>
                            <input type="text" class="form-control shadow-sm {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold text-primary">Địa chỉ</label>
                            <input type="text" class="form-control shadow-sm {{ $errors->has('address') ? 'is-invalid' : '' }}" id="address" name="address" value="{{ old('address') }}" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="district_id" class="form-label fw-bold text-primary">Quận/Huyện</label>
                            <select class="form-select shadow-sm {{ $errors->has('district_id') ? 'is-invalid' : '' }}" id="district_id" name="district_id" required>
                                <option value="">Chọn quận/huyện</option>
                                @if(isset($districts) && $districts->count() > 0)
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                                    @endforeach
                                @else
                                    <option value="">Không có quận/huyện nào.</option>
                                @endif
                            </select>
                            @error('district_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="map_embed_url" class="form-label fw-bold text-primary">URL nhúng bản đồ</label>
                            <input type="url" class="form-control shadow-sm {{ $errors->has('map_embed_url') ? 'is-invalid' : '' }}" id="map_embed_url" name="map_embed_url" value="{{ old('map_embed_url') }}" placeholder="https://maps.google.com/...">
                            @error('map_embed_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                            <textarea class="form-control shadow-sm {{ $errors->has('description') ? 'is-invalid' : '' }}" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="electricity_fee" class="form-label fw-bold text-primary">Tiền điện (VNĐ/kWh)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('electricity_fee') ? 'is-invalid' : '' }}" id="electricity_fee" name="electricity_fee" value="{{ old('electricity_fee') }}" step="1" min="0">
                            @error('electricity_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="water_fee" class="form-label fw-bold text-primary">Tiền nước (VNĐ/m³)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('water_fee') ? 'is-invalid' : '' }}" id="water_fee" name="water_fee" value="{{ old('water_fee') }}" step="1" min="0">
                            @error('water_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="parking_fee" class="form-label fw-bold text-primary">Phí giữ xe (VNĐ/tháng)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('parking_fee') ? 'is-invalid' : '' }}" id="parking_fee" name="parking_fee" value="{{ old('parking_fee') }}" step="1" min="0">
                            @error('parking_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="junk_fee" class="form-label fw-bold text-primary">Phí rác (VNĐ/tháng)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('junk_fee') ? 'is-invalid' : '' }}" id="junk_fee" name="junk_fee" value="{{ old('junk_fee') }}" step="1" min="0">
                            @error('junk_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="internet_fee" class="form-label fw-bold text-primary">Phí internet (VNĐ/tháng)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('internet_fee') ? 'is-invalid' : '' }}" id="internet_fee" name="internet_fee" value="{{ old('internet_fee') }}" step="1" min="0">
                            @error('internet_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="service_fee" class="form-label fw-bold text-primary">Phí dịch vụ (VNĐ/tháng)</label>
                            <input type="number" class="form-control shadow-sm {{ $errors->has('service_fee') ? 'is-invalid' : '' }}" id="service_fee" name="service_fee" value="{{ old('service_fee') }}" step="1" min="0">
                            @error('service_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                            <select class="form-select shadow-sm {{ $errors->has('status') ? 'is-invalid' : '' }}" id="status" name="status" required>
                                <option value="Hoạt động" {{ old('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="Không hoạt động" {{ old('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 {{ $errors->has('amenities') ? 'is-invalid' : '' }}">
                            <label for="amenities" class="form-label fw-bold text-primary">Tiện ích</label>
                            <div class="row g-2">
                                @if(isset($amenities) && count($amenities) > 0)
                                    @foreach($amenities as $amenity)
                                        @if($amenity->type == 'Nhà trọ')
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="amenity_{{ $amenity->id }}"
                                                        name="amenities[]" value="{{ $amenity->id }}"
                                                        {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
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
                            @error('amenities')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label fw-bold text-primary">Hình ảnh</label>
                            <input type="file" class="form-control shadow-sm {{ $errors->has('images') ? 'is-invalid' : '' }}" id="images" name="images[]" accept="image/*" multiple>
                            <input type="hidden" name="main_image_index" id="main_image_index" value="0">
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

        /* #image-preview img:hover {
            transform: scale(1.1);
        } */

        .alert-success, .alert-danger {
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.875em;
            color: #dc3545;
            margin-top: 0.25rem;
        }

        .is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    </style>
@endsection