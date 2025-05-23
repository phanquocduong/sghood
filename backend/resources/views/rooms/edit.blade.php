@extends('layouts.app')

@section('title', 'Sửa phòng trọ')

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
            <h6 class="mb-0 fw-bold">{{ __('Sửa phòng trọ') }}</h6>
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

            <form action="{{ route('rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data" id="roomEditForm" novalidate>
                @csrf
                @method('PATCH')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="motel_id" class="form-label fw-bold text-primary">Chọn nhà trọ</label>
                        <select class="form-select shadow-sm" id="motel_id" name="motel_id" required>
                            <option value="">Chọn nhà trọ</option>
                            @foreach ($motels as $motel)
                                <option value="{{ $motel->id }}" {{ (old('motel_id', $room->motel_id) == $motel->id) ? 'selected' : '' }}>
                                    {{ $motel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên phòng trọ</label>
                        <input type="text" class="form-control shadow-sm" id="name" name="name" value="{{ old('name', $room->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-bold text-primary">Giá phòng (VNĐ)</label>
                        <input type="number" class="form-control shadow-sm" id="price" name="price" value="{{ old('price', $room->price) }}" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="area" class="form-label fw-bold text-primary">Diện tích (m²)</label>
                        <input type="number" class="form-control shadow-sm" id="area" name="area" value="{{ old('area', $room->area) }}" step="0.01" min="0" required>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3">{{ old('description', $room->description) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                        <select class="form-select shadow-sm" id="status" name="status" required>
                            <option value="Trống" {{ old('status', $room->status) == 'Trống' ? 'selected' : '' }}>Trống</option>
                            <option value="Đã thuê" {{ old('status', $room->status) == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="Sửa chữa" {{ old('status', $room->status) == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                            <option value="Ẩn" {{ old('status', $room->status) == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="note" class="form-label fw-bold text-primary">Ghi chú</label>
                        <input type="text" class="form-control shadow-sm" id="note" name="note" value="{{ old('note', $room->note) }}">
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
                    <div class="col-12">
                        <label for="images" class="form-label fw-bold text-primary">Hình ảnh hiện tại</label>
                        @if(isset($room->images) && $room->images->count() > 0)
                            <div class="row g-2 mt-3">
                                @foreach($room->images as $image)
                                    <div class="col-md-3 mb-2 position-relative" data-image-id="{{ $image->id }}">
                                        <div class="image-container" style="height: 150px; overflow: hidden;">
                                            <img src="{{ asset($image->image_url) }}" class="img-fluid rounded shadow-sm existing-image" alt="Room image" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                                        </div>
                                        <div class="position-absolute" style="top: 5px; left: 5px; z-index: 10;">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input main-image-radio"
                                                    id="main_image_{{ $image->id }}"
                                                    name="is_main"
                                                    value="{{ $image->id }}"
                                                    {{ (isset($image->is_main) && $image->is_main == 1) ? 'checked' : '' }}>
                                                <label class="form-check-label bg-white px-1 rounded" for="main_image_{{ $image->id }}">
                                                    Ảnh chính
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                            data-image-id="{{ $image->id }}"
                                            data-room-id="{{ $room->id }}"
                                            style="position: absolute; top: 5px; right: 5px; z-index: 10; transition: all 0.3s;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-3">Chưa có hình ảnh nào.</p>
                        @endif
                        <label for="images" class="form-label fw-bold text-primary">Thêm hình ảnh mới</label>
                        <input type="file" class="form-control shadow-sm" id="images" name="images[]" accept="image/*" multiple>
                        <div id="image-preview" class="row g-2 mt-3"></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">Hủy</a>
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">Cập nhật phòng trọ</button>
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

    .btn:hover, .delete-image-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    #image-preview img, .existing-image {
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 10px;
        transition: transform 0.3s;
    }

    #image-preview img:hover, .existing-image:hover {
        transform: scale(1.1);
    }

    .alert-success, .alert-danger {
        border-left: 5px solid #28a745;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }
</style>

@endsection