@extends('layouts.app')

@section('title', 'Chi tiết phòng trọ')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded-top p-4">
        <h6 class="mb-4">Chi tiết phòng trọ: {{ $room->name }}</h6>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $room->name }}</h5>
                <p><strong>Nhà trọ:</strong>
                    <a href="{{ route('motels.show', $room->motel_id) }}">{{ $room->motel->name }}</a>
                </p>
                <p><strong>Giá phòng:</strong> {{ number_format($room->price, 0, ',', '.') }} VNĐ</p>
                <p><strong>Diện tích:</strong> {{ $room->area }} m²</p>
                <p><strong>Trạng thái:</strong>
                    @php
                        $badgeClass = 'bg-secondary';
                        $statusText = 'Không xác định';
                        switch($room->status) {
                            case 'Trống':
                                $badgeClass = 'bg-success';
                                $statusText = 'Trống';
                                break;
                            case 'Đã thuê':
                                $badgeClass = 'bg-primary';
                                $statusText = 'Đã thuê';
                                break;
                            case 'Sửa chữa':
                                $badgeClass = 'bg-warning text-dark';
                                $statusText = 'Sửa chữa';
                                break;
                            case 'Ẩn':
                                $badgeClass = 'bg-secondary';
                                $statusText = 'Ẩn';
                                break;
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                </p>
                <p><strong>Mô tả:</strong> {{ $room->description ?? 'Không có mô tả.' }}</p>
                <p><strong>Ghi chú:</strong> {{ $room->note ?? 'Không có ghi chú.' }}</p>

                <h6>Tiện nghi</h6>
                <ul>
                    @forelse($room->amenities as $amenity)
                        <li>{{ $amenity->name }}</li>
                    @empty
                        <li>Không có tiện nghi.</li>
                    @endforelse
                </ul>

                <h6>Ảnh phòng trọ</h6>
                <div class="row">
                    @forelse($room->images as $image)
                        <div class="col-md-3 mb-3">
                            <img src="{{ asset($image->image_url) }}" class="img-fluid" alt="Room Image" style="max-height: 200px;">
                        </div>
                    @empty
                        <p class="text-muted">Không có ảnh nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('rooms.index', ['motel_id' => $room->motel_id]) }}" class="btn btn-secondary">Quay lại</a>
            <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-warning">Sửa</a>
            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
            </form>
        </div>
    </div>
</div>
@endsection
