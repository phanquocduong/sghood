@extends('layouts.app')

@section('title', 'Danh sách phòng trọ')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">Danh sách phòng trọ (Nhà trọ ID: {{ $motelId }})</h6>
                <div>
                    <a href="{{ route('rooms.create', ['motel_id' => $motelId]) }}" class="btn btn-primary">Thêm phòng trọ</a>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="mb-4">
                <form action="{{ route('rooms.index') }}" method="GET">
                    <input type="hidden" name="motel_id" value="{{ $motelId }}">
                    <div class="row">
                        <!-- Tìm kiếm -->
                        <div class="col-md-8 mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" name="query" placeholder="Tìm kiếm phòng trọ..."
                                    value="{{ $querySearch }}">
                                <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                            </div>
                        </div>

                        <!-- Bộ lọc trạng thái -->
                        <div class="col-md-2 mb-2">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="">Trạng thái</option>
                                <option value="empty" {{ $status == 'empty' ? 'selected' : '' }}>Trống</option>
                                <option value="rented" {{ $status == 'rented' ? 'selected' : '' }}>Đã thuê</option>
                                <option value="maintenance" {{ $status == 'maintenance' ? 'selected' : '' }}>Sửa chữa</option>
                                <option value="hidden" {{ $status == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                            </select>
                        </div>

                        <!-- Sắp xếp -->
                        <div class="col-md-2 mb-2">
                            <select class="form-select" name="sortOption" onchange="this.form.submit()">
                                <option value="">Sắp xếp</option>
                                <option value="name_asc" {{ $sortOption == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                                <option value="name_desc" {{ $sortOption == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                                <option value="created_at_asc" {{ $sortOption == 'created_at_asc' ? 'selected' : '' }}>Cũ nhất</option>
                                <option value="created_at_desc" {{ $sortOption == 'created_at_desc' ? 'selected' : '' }}>Mới nhất</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table text-center table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Stt</th>
                            <th scope="col">Ảnh</th>
                            <th scope="col">Tên phòng trọ</th>
                            <th scope="col">Ghi chú</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $index => $room)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if ($room->main_image)
                                        <img src="{{ asset($room->main_image->image_url) }}" alt="Room Image" width="100" class="img-thumbnail">
                                    @endif
                                </td>
                                <td><a href="{{ route('rooms.show', $room->id) }}">{{ $room->name }}</a></td>
                                <td>{{ Str::limit($room->note ?? 'Không có ghi chú', 50) }}</td>
                                <td>
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
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('rooms.destroy', $room->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Không có phòng trọ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $rooms->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
