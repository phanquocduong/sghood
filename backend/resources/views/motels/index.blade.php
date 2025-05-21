@extends('layouts.app')

@section('title', 'Danh sách nhà trọ')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded-top p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">Danh sách nhà trọ</h6>
                <div>
                    <!-- <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#filterForm" aria-expanded="false" aria-controls="filterForm">
                        Lọc
                    </button> -->
                    <a href="{{ route('motels.create') }}" class="btn btn-primary">Thêm nhà trọ</a>
                </div>
            </div>

            <!-- Filter Form (Hidden by Default) -->
            <div class="mb-4">
                <form action="{{ route('motels.index') }}" method="GET">
                    <div class="d-flex">
                        <div class="input-group me-2">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm nhà trọ..."
                                value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                        </div>
                        <div class="input-group" style="width: 250px;">
                            <select class="form-select" name="area" aria-label="Select area" onchange="this.form.submit()">
                                <option value="">Chọn khu vực</option>
                                <option value="district_1" {{ request('area') == 'district_1' ? 'selected' : '' }}>Quận 1</option>
                                <option value="district_2" {{ request('area') == 'district_2' ? 'selected' : '' }}>Quận 2</option>
                                <option value="district_3" {{ request('area') == 'district_3' ? 'selected' : '' }}>Quận 3</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            @if (session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table text-center table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Stt</th>
                            <th scope="col">Ảnh</th>
                            <th scope="col">Tên nhà trọ</th>
                            <th scope="col">Mô tả</th>
                            <th scope="col">Số lượng phòng</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($motels as $index => $motel)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if ($motel->images && count($motel->images) > 0)
                                        <img src="{{ asset('storage/' . $motel->images[0]->image_url) }}" alt="Motel Image" width="100">
                                    @else
                                        <img src="https://via.placeholder.com/100" alt="No Image" width="100">
                                    @endif
                                </td>
                                <td><a href="{{ route('motels.show', $motel->id) }}">{{ $motel->name }}</a></td>
                                <td>{{ Str::limit($motel->description ?? 'Không có mô tả', 50) }}</td>
                                <td><a href="{{ route('rooms.index', ['motel_id' => $motel->id]) }}">Đang có: {{ $motel->room_count ?? 0 }}</a></td>
                                <td>
                                    <span class="badge {{ $motel->status == 'available' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $motel->status == 'available' ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('motels.edit', $motel->id) }}" class="btn btn-sm btn-primary">Sửa</a>
                                    <form action="{{ route('motels.destroy', $motel->id) }}" method="POST"
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
                                <td colspan="7">Không có nhà trọ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $motels->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
