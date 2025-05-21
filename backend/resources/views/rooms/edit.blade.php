@extends('layouts.app')

@section('title', 'Sửa phòng trọ')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4">
            <h6 class="mb-4">Sửa phòng trọ</h6>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data" id="roomEditForm">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label for="motel_id" class="form-label">Chọn nhà trọ</label>
                    <select class="form-control" id="motel_id" name="motel_id" required>
                        <option value="">Chọn nhà trọ</option>
                        @foreach ($motels as $motel)
                            <option value="{{ $motel->id }}" {{ (old('motel_id', $room->motel_id) == $motel->id) ? 'selected' : '' }}>
                                {{ $motel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Tên phòng trọ</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $room->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Giá phòng (VNĐ)</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $room->price) }}"
                        min="0" required>
                </div>
                <div class="mb-3">
                    <label for="area" class="form-label">Diện tích (m²)</label>
                    <input type="number" class="form-control" id="area" name="area" value="{{ old('area', $room->area) }}"
                        step="0.01" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="3">{{ old('description', $room->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="Trống" {{ old('status', $room->status) == 'Trống' ? 'selected' : '' }}>Trống</option>
                        <option value="Đã thuê" {{ old('status', $room->status) == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                        <option value="Sửa chữa" {{ old('status', $room->status) == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                        <option value="Ẩn" {{ old('status', $room->status) == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <input type="text" class="form-control" id="note" name="note" value="{{ old('note', $room->note) }}">
                </div>
                <div class="mb-3">
                    <label for="amenities" class="form-label">Tiện nghi</label>
                    <div class="row">
                        @forelse($amenities as $amenity)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                        id="amenity_{{ $amenity->id }}"
                                        name="amenities[]"
                                        value="{{ $amenity->id }}"
                                        {{ in_array($amenity->id, old('amenities', $room->amenities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenity_{{ $amenity->id }}">
                                        {{ $amenity->name }}
                                    </label>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted">Không có tiện nghi nào.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Hình ảnh</label>
                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                    <div id="image-preview" class="row mt-3"></div>
                    @if (session('success'))
                        <div class="alert alert-success mt-2">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(isset($room->images) && $room->images->count() > 0)
                        <div class="row mt-3">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            @foreach($room->images as $image)
                                <div class="col-md-3 mb-3 position-relative" data-image-id="{{ $image->id }}">
                                    <div class="image-container" style="height: 200px; overflow: hidden;">
                                        <img src="{{ asset($image->image_url) }}"
                                            class="img-thumbnail"
                                            alt="Room image"
                                            style="width: 100%; height: 100%; object-fit: cover;">
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
                                        style="position: absolute; top: 5px; right: 5px; z-index: 10;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật phòng trọ</button>
                <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
@endsection
