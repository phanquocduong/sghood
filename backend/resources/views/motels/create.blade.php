@extends('layouts.app')

@section('title', 'Thêm nhà trọ')

@section('content')
    <!-- Adding FilePond CSS -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/create-motel.css') }}">

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
                            <label for="name" class="form-label fw-bold text-primary">Tên dãy trọ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold text-primary">Địa chỉ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="district_id" class="form-label fw-bold text-primary">Quận/Huyện <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm @error('district_id') is-invalid @enderror" id="district_id" name="district_id" required>
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
                            <label for="map_embed_url" class="form-label fw-bold text-primary">URL nhúng bản đồ <span class="text-danger">*</span></label>
                            <input type="url" class="form-control shadow-sm @error('map_embed_url') is-invalid @enderror" id="map_embed_url" name="map_embed_url" value="{{ old('map_embed_url') }}" required placeholder="https://maps.google.com/...">
                            @error('map_embed_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                            <textarea class="form-control shadow-sm @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="electricity_fee" class="form-label fw-bold text-primary">Tiền điện (VNĐ/kWh) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('electricity_fee') is-invalid @enderror" id="electricity_fee" name="electricity_fee" value="{{ old('electricity_fee') }}" step="any" min="0" required>
                            @error('electricity_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="water_fee" class="form-label fw-bold text-primary">Tiền nước (VNĐ/m³) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('water_fee') is-invalid @enderror" id="water_fee" name="water_fee" value="{{ old('water_fee') }}" step="any" min="0" required>
                            @error('water_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="parking_fee" class="form-label fw-bold text-primary">Phí giữ xe (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('parking_fee') is-invalid @enderror" id="parking_fee" name="parking_fee" value="{{ old('parking_fee') }}" step="any" min="0" required>
                            @error('parking_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="junk_fee" class="form-label fw-bold text-primary">Phí rác (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('junk_fee') is-invalid @enderror" id="junk_fee" name="junk_fee" value="{{ old('junk_fee') }}" step="any" min="0" required>
                            @error('junk_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="internet_fee" class="form-label fw-bold text-primary">Phí internet (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('internet_fee') is-invalid @enderror" id="internet_fee" name="internet_fee" value="{{ old('internet_fee') }}" step="any" min="0" required>
                            @error('internet_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="service_fee" class="form-label fw-bold text-primary">Phí dịch vụ (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-sm @error('service_fee') is-invalid @enderror" id="service_fee" name="service_fee" value="{{ old('service_fee') }}" step="any" min="0" required>
                            @error('service_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-bold text-primary">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="Hoạt động" {{ old('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="Không hoạt động" {{ old('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="amenities" class="form-label fw-bold text-primary">Tiện ích</label>
                            <div class="row g-2">
                                @if(isset($amenities) && count($amenities) > 0)
                                    @foreach($amenities as $amenity)
                                        @if($amenity->type == 'Nhà trọ')
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input @error('amenities') is-invalid @enderror" id="amenity_{{ $amenity->id }}"
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
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label fw-bold text-primary">Hình ảnh <span class="text-danger">*</span></label>
                            <input type="file" class="filepond form-control shadow-sm @error('images') is-invalid @enderror" name="images[]" multiple accept="image/*" required>
                            <small class="form-text text-muted mb-2 d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                Bạn có thể thêm 1 hình hoặc nhiều hình. Định dạng hỗ trợ: JPG, PNG, GIF, Webp.
                            </small>

                            <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                <label class="form-label fw-bold text-success">
                                    <i class="fas fa-images me-1"></i>
                                    Hình ảnh đã chọn (Click vào ảnh để chọn làm ảnh chính)
                                </label>
                                <div id="imagePreviewGrid" class="row g-2"></div>
                                <input type="hidden" name="main_image_index" id="mainImageIndex" value="0">
                            </div>

                            @error('images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('motels.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                        <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm nhà trọ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Adding FilePond JS and Plugins -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
    <script src="{{ asset('js/motel.js') }}"></script>
@endsection
