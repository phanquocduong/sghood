@extends('layouts.app')

@section('title', 'Thêm phòng trọ')

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
            <h6 class="mb-0 fw-bold">{{ __('Thêm phòng trọ') }}</h6>
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

            <form action="{{ route('rooms.store') }}" method="POST" enctype="multipart/form-data" id="roomForm" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label for="motel_id" class="form-label fw-bold text-primary">Nhà trọ</label>
                        <input type="hidden" name="motel_id" value="{{ $motel->id }}">
                        <input type="text" class="form-control shadow-sm" value="{{ $motel->name }}" readonly>
                    </div>
                    <div class="col-12">
                        <label for="name" class="form-label fw-bold text-primary">Tên phòng trọ</label>
                        <input type="text" class="form-control shadow-sm" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="price" class="form-label fw-bold text-primary">Giá phòng (VNĐ)</label>
                        <input type="number" class="form-control shadow-sm" id="price" name="price" value="{{ old('price') }}" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="area" class="form-label fw-bold text-primary">Diện tích (m²)</label>
                        <input type="number" class="form-control shadow-sm" id="area" name="area" value="{{ old('area') }}" step="0.01" min="0" required>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold text-primary">Mô tả</label>
                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold text-primary">Trạng thái</label>
                        <select class="form-select shadow-sm" id="status" name="status" required>
                            <option value="Trống" {{ old('status') == 'Trống' ? 'selected' : '' }}>Trống</option>
                            <option value="Đã thuê" {{ old('status') == 'Đã thuê' ? 'selected' : '' }}>Đã thuê</option>
                            <option value="Sửa chữa" {{ old('status') == 'Sửa chữa' ? 'selected' : '' }}>Sửa chữa</option>
                            <option value="Ẩn" {{ old('status') == 'Ẩn' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="note" class="form-label fw-bold text-primary">Ghi chú</label>
                        <input type="text" class="form-control shadow-sm" id="note" name="note" value="{{ old('note') }}">
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
                        <label for="images" class="form-label fw-bold text-primary">Hình ảnh</label>
                        <input type="file" class="form-control shadow-sm" id="images" name="images[]" accept="image/*" multiple required>
                        <div id="image-preview" class="row g-2 mt-3"></div>
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
        max-height: 150px;
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


@endsection