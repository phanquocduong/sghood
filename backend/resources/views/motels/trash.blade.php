@extends('layouts.app')

@section('title', 'Thùng rác')

@section('content')
    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #ff7e5f, #feb47b); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">{{ __('Thùng rác') }}</h6>
                <div>
                    <a href="{{ route('motels.index') }}" class="btn btn-light text-dark shadow-sm"
                        style="transition: all 0.3s;">
                        <i class="fas fa-arrow-left me-1"></i> {{ __('Quay lại') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                @if (session('message'))
                    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn"
                        role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" style="width: 5%;">Stt</th>
                                <th scope="col" style="width: 15%;">Ảnh</th>
                                <th scope="col">Tên nhà trọ</th>
                                <th scope="col">Mô tả</th>
                                <th scope="col">Số lượng phòng</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" style="width: 20%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($motels as $index => $motel)
                                <tr class="table-row">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @php
                                            $mainImage =
                                                $motel->images && $motel->images->count() > 0
                                                    ? $motel->images->where('is_main', 1)->first() ??
                                                        $motel->images->first()
                                                    : null;
                                        @endphp
                                        @if ($mainImage)
                                            <img src="{{ $mainImage->image_url }}" alt="{{ $mainImage->image_url }}"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @else
                                            <img src="https://via.placeholder.com/100?text=Không+có+ảnh" alt="No Image"
                                                class="img-fluid rounded motel-image"
                                                style="max-height: 80px; object-fit: cover; transition: transform 0.3s;">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('motels.show', $motel->id) }}"
                                            class="text-primary fw-bold text-decoration-none"
                                            style="transition: color 0.3s;">
                                            {{ $motel->name }}
                                        </a>
                                    </td>
                                    <td class="text-muted">{{ Str::limit($motel->description ?? 'Không có mô tả', 50) }}
                                    </td>
                                    <td>
                                        <a href="#"
                                            class="text-success text-decoration-none">{{ $motel->room_count ?? 0 }}
                                            phòng</a>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $motel->status == 'Hoạt động' ? 'bg-success' : 'bg-danger' }} py-2 px-3">
                                            {{ $motel->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <form action="{{ route('motels.restore', $motel->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-success me-2 action-btn action-icon"
                                                    onclick="return confirm('Bạn có chắc chắn muốn khôi phục?')"
                                                    style="transition: all 0.3s;">
                                                    <i class="fas fa-trash-restore me-1"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('motels.forceDelete', $motel->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger action-btn action-icon"
                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn không?')"
                                                    style="transition: all 0.3s;">
                                                    <i class="fas fa-trash me-1"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Không có nhà trọ nào trong thùng
                                        rác.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $motels->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .table-row:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .motel-image:hover {
            transform: scale(1.1);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            border-left: 5px solid #28a745;
        }

        .text-primary:hover {
            color: #ff7e5f !important;
        }

        @media (max-width: 576px) {

            /* Nút hành động trên mobile chỉ là icon tròn */
            .action-icon {
                padding: 6px 8px;
                /* Nhỏ gọn */
                border-radius: 50%;
                /* Bo tròn */
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center
            }

            /* Icon căn giữa */
            .action-icon i {
                margin: 0 !important;
                font-size: 14px;
            }

            .card-header .btn {
                font-size: 14px;
                padding: 6px 8px;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }
    </style>

    @section('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @endsection
@endsection
