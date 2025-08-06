@extends('layouts.app')

@section('title', 'Chi tiết Yêu Cầu Sửa Chữa')

@section('content')
<div class="container py-5">
    <div class="card shadow rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h4 class="mb-0">Chi tiết Yêu Cầu Sửa Chữa</h4>
        </div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Người thuê:</label>
                <div class="col-sm-9">{{ $repair->contract->user->name ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Phòng:</label>
                <div class="col-sm-9">{{ $repair->contract->room->name ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Tiêu đề:</label>
                <div class="col-sm-9">{{ $repair->title }}</div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Mô tả:</label>
                <div class="col-sm-9">{{ $repair->description }}</div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Hình ảnh:</label>
                <div class="col-sm-9">
                    @if ($repair->images)
                        <div class="row">
                            @foreach(explode('|', $repair->images) as $image)
                                <div class="col-md-4 col-6 mb-3">
                                    <a href="{{ asset($image) }}" target="_blank">
                                        <img src="{{ asset($image) }}" alt="Hình ảnh" class="img-thumbnail" style="height: 150px; object-fit: cover;">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <em>Không có hình ảnh</em>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Trạng thái:</label>
                <div class="col-sm-9">
                    @php
                        $badgeClass = match ($repair->status) {
                            'Đang thực hiện' => 'warning',
                            'Hoàn thành' => 'success',
                            'Huỷ bỏ' => 'danger',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge bg-{{ $badgeClass }} px-3 py-2">{{ $repair->status }}</span>
                </div>
            </div>

            @if($repair->status == 'Huỷ bỏ' && $repair->cancellation_reason)
            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Lý do huỷ:</label>
                <div class="col-sm-9 text-danger">{{ $repair->cancellation_reason }}</div>
            </div>
            @endif

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Ghi chú thêm:</label>
                <div class="col-sm-9">{{ $repair->note ?? 'Không có' }}</div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Ngày sửa:</label>
                <div class="col-sm-9">
                    {{ $repair->repaired_at ? \Carbon\Carbon::parse($repair->repaired_at)->format('d/m/Y H:i') : 'Chưa sửa' }}
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 fw-bold">Ngày tạo yêu cầu:</label>
                <div class="col-sm-9">
                    {{ \Carbon\Carbon::parse($repair->created_at)->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="row">
                <div class="col text-end">
                    <a href="{{ route('repair_requests.index') }}" class="btn btn-secondary">Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
