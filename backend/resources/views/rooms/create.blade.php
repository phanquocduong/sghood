@extends('layouts.app')

@section('title', 'Thêm phòng trọ')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4">
            <h6 class="mb-4">Thêm phòng trọ</h6>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('rooms.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="motel_id" class="form-label">Nhà trọ</label>
                    <input type="hidden" name="motel_id" value="{{ $motel->id }}">
                    <input type="text" class="form-control" value="{{ $motel->name }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Tên phòng trọ</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Giá phòng (VNĐ)</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}"
                        min="0" required>
                </div>
                <div class="mb-3">
                    <label for="area" class="form-label">Diện tích (m²)</label>
                    <input type="number" class="form-control" id="area" name="area" value="{{ old('area') }}"
                        step="0.01" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="Trống" {{ old('status') == 'Trống' ? 'selected' : '' }}>Trống</option>
                        <option value="Đã thuê" {{ old('status') == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                        <option value="Sửa chữa" {{ old('status') == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                        <option value="Ẩn" {{ old('status') == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Ghi chú</label>
                    <input type="text" class="form-control" id="note" name="note" value="{{ old('note') }}">
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
                                        {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}>
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
                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple required>
                    <div id="image-preview" class="row mt-3"></div>
                </div>
                <button type="submit" class="btn btn-primary">Thêm phòng trọ</button>
                <a href="{{ route('rooms.index', ['motel_id' => $motel->id]) }}" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
@endsection
