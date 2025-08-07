@extends('layouts.app')

@section('title', 'Thêm tiện ích')

@section('content')
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Thêm tiện ích') }}</h6>
        </div>
        <div class="card-body p-4">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('amenities.store') }}" method="POST" novalidate>
                @csrf
                <div class="row g-3">
                    <!-- Tên tiện ích -->
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">{{ __('Tên tiện ích') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Nhập tên tiện ích..." required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Loại -->
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold">{{ __('Loại') }} <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Chọn loại</option>
                            <option value="Nhà trọ" {{ old('type') == 'Nhà trọ' ? 'selected' : '' }}>Nhà trọ</option>
                            <option value="Phòng trọ" {{ old('type') == 'Phòng trọ' ? 'selected' : '' }}>Phòng trọ</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Thứ tự sắp xếp -->
                    {{-- <div class="col-md-6">
                        <label for="order" class="form-label fw-bold">{{ __('Thứ tự sắp xếp') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control shadow-sm @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order') }}" placeholder="Nhập thứ tự sắp xếp..." min="0" step="1" required>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div> --}}

                    <!-- Trạng thái -->
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">{{ __('Trạng thái') }} <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm @error('status') is-invalid @enderror" id="status" name="status" required>
                            {{-- <option value="">Chọn trạng thái</option> --}}
                            <option value="Hoạt động" {{ old('status') == 'Hoạt động' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="Không hoạt động" {{ old('status') == 'Không hoạt động' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary shadow-sm" style="transition: all 0.3s;">
                        <i class="fas fa-save me-1"></i> {{ __('Lưu') }}
                    </button>
                    <a href="{{ route('amenities.index') }}" class="btn btn-secondary shadow-sm" style="transition: all 0.3s;">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
