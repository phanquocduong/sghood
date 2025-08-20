@extends('layouts.app')

@section('title', 'Thêm phòng trọ')

@section('content')
<!-- Adding FilePond CSS -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<!-- Adding FilePond Image Preview CSS -->
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/create-room.css') }}">

<div class="container-fluid py-5 px-4">
    <!-- Display session flash messages -->
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Thêm phòng trọ') }}</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('rooms.store') }}" method="POST" enctype="multipart/form-data" id="roomForm" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="motel_id" class="form-label fw-bold text-primary">Nhà trọ <span class="text-danger">*</span></label>
                        <input type="hidden" name="motel_id" value="{{ $motel->id }}">
                        <input type="text" class="form-control shadow-sm" value="{{ $motel->name }}" readonly>
                    </div>
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên phòng trọ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-bold text-primary">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" min="0" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="area" class="form-label fw-bold text-primary">Diện tích (m²) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area') }}" step="0.01" min="0" required>
                        @error('area')
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
                    <div class="col-12">
                        <label for="max_occupants" class="form-label fw-bold text-primary">Số người ở <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('max_occupants') is-invalid @enderror" id="max_occupants" name="max_occupants" value="{{ old('max_occupants') }}" min="1" required>
                        @error('max_occupants')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="Trống" {{ old('status') == 'Trống' ? 'selected' : '' }}>Trống</option>
                            <option value="Đã thuê" {{ old('status') == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="Sửa chữa" {{ old('status') == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                            <option value="Ẩn" {{ old('status') == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="note" class="form-label fw-bold text-primary">Ghi chú</label>
                        <input type="text" class="form-control shadow-sm @error('note') is-invalid @enderror" id="note" name="note" value="{{ old('note') }}">
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="amenities" class="form-label fw-bold text-primary">Tiện nghi</label>
                        <div class="row g-2">
                            @forelse($amenities as $amenity)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="amenity_{{ $amenity->id }}"
                                            name="amenities[]" value="{{ $amenity->id }}"
                                            {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="amenity_{{ $amenity->id }}">{{ $amenity->name }}</label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted">Không có tiện nghi nào.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="images" class="form-label fw-bold text-primary">Hình ảnh <span class="text-danger">*</span></label>
                        <!-- FilePond input -->
                        <input type="file" class="filepond form-control shadow-sm @error('images') is-invalid @enderror" name="images[]" multiple accept="image/*" required>
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Bạn có thể thêm 1 hình hoặc nhiều hình. Định dạng hỗ trợ: JPG, PNG, GIF, Webp.
                        </small>

                        <!-- Preview container cho selected images với main image selector -->
                        <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-images me-1"></i>
                                Hình ảnh đã chọn (Click vào ảnh để chọn làm ảnh chính)
                            </label>
                            <div id="imagePreviewGrid" class="row g-2"></div>
                            <input type="hidden" name="main_image_index" id="mainImageIndex" value="0">
                        </div>

                        @error('images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('rooms.index', ['motel_id' => $motel->id]) }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Thêm phòng trọ</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Adding FilePond JS and Plugins -->
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
<script src="{{ asset('js/room.js') }}"></script>
@endsection
