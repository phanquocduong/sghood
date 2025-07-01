@extends('layouts.app')

@section('title', 'Sửa phòng trọ')

@section('content')
<!-- Adding FilePond CSS -->
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<!-- Adding FilePond Image Preview CSS -->
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('css/edit-room.css') }}">
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <div class="d-flex align-items-center">
                <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-light btn-sm me-3 shadow-sm" style="transition: all 0.3s;" title="Quay lại danh sách phòng trọ">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
                </a>
                <h6 class="mb-0 fw-bold">{{ __('Sửa phòng trọ') }}</h6>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data" id="roomForm" novalidate>
                @csrf
                @method('PATCH')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="motel_id" class="form-label fw-bold text-primary">Chọn nhà trọ <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm @error('motel_id') is-invalid @enderror" id="motel_id" name="motel_id" required>
                            <option value="">Chọn nhà trọ</option>
                            @foreach ($motels as $motel)
                                <option value="{{ $motel->id }}" {{ (old('motel_id', $room->motel_id) == $motel->id) ? 'selected' : '' }}>
                                    {{ $motel->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('motel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên phòng trọ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $room->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-bold text-primary">Giá phòng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $room->price) }}" min="0" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="area" class="form-label fw-bold text-primary">Diện tích (m²) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area', $room->area) }}" step="0.01" min="0" required>
                        @error('area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <textarea class="form-control shadow-sm @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $room->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="Trống" {{ old('status', $room->status) == 'Trống' ? 'selected' : '' }}>Trống</option>
                            <option value="Đã thuê" {{ old('status', $room->status) == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="Sửa chữa" {{ old('status', $room->status) == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                            <option value="Ẩn" {{ old('status', $room->status) == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="note" class="form-label fw-bold text-primary">Ghi chú</label>
                        <input type="text" class="form-control shadow-sm @error('note') is-invalid @enderror" id="note" name="note" value="{{ old('note', $room->note) }}">
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
                                            {{ in_array($amenity->id, old('amenities', $room->amenities->pluck('id')->toArray())) ? 'checked' : '' }}>
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

                    <!-- Hình ảnh hiện tại -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-primary">Hình ảnh hiện tại</label>
                        @if(isset($room->images) && $room->images->count() > 0)
                            <div class="existing-images-section">
                                <div class="existing-images-grid">
                                    @foreach($room->images as $image)
                                        <div class="existing-image-item" data-image-id="{{ $image->id }}">
                                            <div class="image-wrapper">
                                                <img src="{{ asset($image->image_url) }}"
                                                     class="existing-image"
                                                     alt="Room image">
                                                <div class="main-image-selector">
                                                    <input type="radio"
                                                           class="form-check-input main-image-radio"
                                                           id="main_image_{{ $image->id }}"
                                                           name="is_main"
                                                           value="{{ $image->id }}"
                                                           {{ ($image->is_main == 1) ? 'checked' : '' }}>
                                                    <label class="main-image-label" for="main_image_{{ $image->id }}">
                                                        <i class="fas fa-star"></i>
                                                    </label>
                                                </div>
                                                <button type="button"
                                                        class="delete-existing-btn"
                                                        data-image-id="{{ $image->id }}"
                                                        data-room-id="{{ $room->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @if($image->is_main == 1)
                                                    <div class="main-image-badge">
                                                        <i class="fas fa-crown"></i>
                                                        <span>Ảnh chính</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="no-images-alert">
                                <i class="fas fa-info-circle me-2"></i>
                                Chưa có hình ảnh nào cho phòng trọ này.
                            </div>
                        @endif
                    </div>

                    <!-- Thêm hình ảnh mới với FilePond -->
                    <div class="col-12">
                        <label for="images" class="form-label fw-bold text-primary">Thêm hình ảnh mới</label>
                        <!-- FilePond input -->
                        <input type="file" class="filepond form-control shadow-sm @error('images') is-invalid @enderror" name="images[]" multiple accept="image/*">
                        <small class="form-text text-muted mb-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            Bạn có thể thêm hình ảnh mới hoặc giữ nguyên hình ảnh hiện tại. Định dạng hỗ trợ: JPG, PNG, GIF, Webp.
                        </small>

                        <!-- Preview container cho new images với main image selector -->
                        <div id="newImagePreviewContainer" class="mt-3" style="display: none;">
                            <label class="form-label fw-bold text-success">
                                <i class="fas fa-images me-1"></i>
                                Hình ảnh mới (Click vào ảnh để chọn làm ảnh chính)
                            </label>
                            <div id="newImagePreviewGrid" class="row g-2"></div>
                        </div>

                        @error('images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">
                        <i class="fas fa-times me-1"></i>Hủy
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">
                        <i class="fas fa-save me-1"></i>Cập nhật phòng trọ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom CSS for edit form -->
<style>
/* Existing Images Section */

</style>

<!-- Adding FilePond JS and Plugins -->
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
<script src="{{ asset('js/room-edit.js') }}"></script>
@endsection
