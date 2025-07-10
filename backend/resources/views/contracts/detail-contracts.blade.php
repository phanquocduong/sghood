@extends('layouts.app')

@section('title', 'Hợp đồng thuê phòng')

@section('content')
    <div class="container-fluid py-5 px-4">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- DIV CHA 1: PHẦN HỢP ĐỒNG -->
        {!! $contract->content ?? '' !!}

        <!-- DIV PHỤ LỤC: PHẦN GIA HẠN HỢP ĐỒNG -->
        @if(isset($contractExtensions) && $contractExtensions->count() > 0)
            <div class="contract-extension-wrapper mt-5">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-gradient text-white py-3 rounded-top-4" style="background: linear-gradient(90deg, #28a745, #20c997);">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-file-contract me-2"></i>PHỤ LỤC GIA HẠN HỢP ĐỒNG
                            <span class="badge bg-light text-success ms-2">{{ $contractExtensions->count() }} gia hạn</span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @foreach($contractExtensions as $index => $extension)
                            <div class="extension-item mb-4 {{ !$loop->last ? 'border-bottom pb-4' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="text-primary fw-bold mb-0">
                                        <i class="fas fa-plus-circle me-2"></i>Phụ lục số {{ $index + 1 }}
                                    </h6>
                                    <div class="text-end">
                                        <div class="badge bg-success py-2 px-3 mb-2">
                                            <i class="fas fa-check-circle me-1"></i>Đã duyệt
                                            {{ $extension->created_at ? $extension->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </div>
                                        <div class="text-muted small">

                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="info-box p-3 bg-light rounded-3">
                                            <strong class="text-primary">
                                                <i class="fas fa-calendar-alt me-1"></i>Ngày kết thúc mới:
                                            </strong>
                                            <div class="mt-1 text-dark fw-semibold">
                                                {{ $extension->new_end_date ? \Carbon\Carbon::parse($extension->new_end_date)->format('d/m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box p-3 bg-light rounded-3">
                                            <strong class="text-success">
                                                <i class="fas fa-money-bill-wave me-1"></i>Giá thuê mới:
                                            </strong>
                                            <div class="mt-1 text-success fw-bold">
                                                {{ number_format($extension->new_rental_price ?? 0, 0, ',', '.') }} VNĐ
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($extension->content)
                                    <div class="extension-content">
                                        <h6 class="text-dark fw-semibold mb-3">
                                            <i class="fas fa-file-alt me-2"></i>Nội dung phụ lục:
                                        </h6>
                                        <div class="content-box p-4 bg-white border rounded-3 shadow-sm">
                                            {!! $extension->content !!}
                                        </div>
                                    </div>
                                @endif

                                @if($extension->file)
                                    <div class="extension-file mt-3">
                                        <h6 class="text-dark fw-semibold mb-2">
                                            <i class="fas fa-paperclip me-2"></i>Tệp đính kèm:
                                        </h6>
                                        <a href="{{ asset('storage/' . $extension->file) }}"
                                           target="_blank"
                                           class="btn btn-outline-primary btn-sm shadow-sm">
                                            <i class="fas fa-download me-1"></i>Tải xuống tệp đính kèm
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- DIV CHA 2: PHẦN QUẢN LÝ TRẠNG THÁI HỢP ĐỒNG -->
        <div class="contract-management-wrapper">
            <div class="card border-0 bg-light rounded-3 p-4">
                <h5 class="text-dark mb-4">
                    <i class="fas fa-edit me-2"></i>QUẢN LÝ TRẠNG THÁI HỢP ĐỒNG
                </h5>

                <!-- Identity Document Display -->
                <div class="card border-0 bg-white shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="fas fa-id-card me-2"></i>Hình ảnh căn cước công dân
                        </h6>
                        @if ($contract->user && $contract->user->identity_document)
                            <div id="identityCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach (explode('|', $contract->user->identity_document) as $index => $imagePath)
                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                            <img src="{{ route('contracts.showIdentityDocument', ['contractId' => $contract->id, 'imagePath' => basename($imagePath)]) }}"
                                                alt="Căn cước công dân {{ $index + 1 }}"
                                                class="d-block w-100 rounded-3 identity-image"
                                                style="max-height: 400px; object-fit: contain;">
                                            <div class="carousel-caption d-none d-md-block">
                                                <button type="button" class="btn btn-sm btn-primary zoom-image"
                                                    data-bs-toggle="modal" data-bs-target="#imageModal"
                                                    data-image="{{ route('contracts.showIdentityDocument', ['contractId' => $contract->id, 'imagePath' => basename($imagePath)]) }}">
                                                    <i class="fas fa-search-plus me-1"></i>Phóng to
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if (count(explode('|', $contract->user->identity_document)) > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#identityCarousel"
                                        data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#identityCarousel"
                                        data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>Không có hình ảnh căn cước công dân.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modal for Image Zoom -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel">Xem ảnh căn cước</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="" id="modalImage" class="img-fluid" style="max-height: 80vh;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Status Display -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-white shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Trạng thái hiện tại
                                </h6>
                                @php
                                    $currentStatus = $contract->status ?? '';
                                    $badgeClass = match ($currentStatus) {
                                        'Chờ xác nhận' => 'warning',
                                        'Đã ký' => 'success',
                                        'Đã hủy' => 'danger',
                                        'Hết hạn' => 'secondary',
                                        default => 'info',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} py-2 px-3 fs-6">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    {{ $currentStatus }}
                                </span>
                                <p class="text-muted mt-2 mb-0">
                                    <small>
                                        <i class="fas fa-clock me-1"></i>
                                        Cập nhật lần cuối:
                                        {{ $contract->updated_at ? $contract->updated_at->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : 'Chưa cập nhật' }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-white shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-calendar-check me-2"></i>Thông tin thời hạn
                                </h6>
                                <p class="mb-1">
                                    <strong>Ngày ký:</strong>
                                    <span class="text-muted">Chưa ký</span>
                                </p>
                                <p class="mb-0">
                                    <strong>Ngày hết hạn:</strong>
                                    <span
                                        class="text-primary">{{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y H:i') : 'Chưa cập nhật' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Update Form -->
                @if ($currentStatus !== 'Đã hủy' && $currentStatus !== 'Hắt hạn')
                    <div class="card border-0 bg-white shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 text-white">
                                <i class="fas fa-sync-alt me-2"></i>Cập nhật trạng thái
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('contracts.updateStatus', $contract->id) }}" method="POST"
                                onsubmit="return confirmStatusChange()">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-tasks me-1"></i>Trạng thái mới
                                        </label>
                                        <select class="form-select shadow-sm" name="status" id="status" required>
                                            <option value="">{{ $contract->status }}</option>
                                            @if ($currentStatus === 'Chờ duyệt')
                                                <option value="Chờ chỉnh sửa">Chờ chỉnh sửa</option>
                                                <option value="Chờ ký">Chờ ký</option>
                                            @elseif($currentStatus === 'Hoạt động')
                                                <option value="Kết thúc">Kết thúc hợp đồng</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                                    </a>
                                    <button type="submit" class="btn btn-primary shadow-sm">
                                        <i class="fas fa-save me-1"></i>Cập nhật trạng thái
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <i class="fas fa-lock me-2"></i>
                        <strong>Hợp đồng này không thể thay đổi trạng thái</strong>
                        <br>
                        <small>Hợp đồng đã {{ strtolower($currentStatus) }} và không thể cập nhật thêm.</small>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        input {
            pointer-events: none;
            background-color: #f8f9fa;
            user-select: none;
        }

        .form-control {
            background-color: rgb(243, 246, 249);
        }

        .img-fluid {
            max-width: 100%;
            height: auto;
        }

        .identity-image {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .identity-image:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            background: rgba(0, 0, 0, 0.3);
        }

        .carousel-control-prev-icon,
        .carousel-control-next {
            background-size: 100%, 100%;
        }

        .carousel-caption {
            bottom: 20px;
        }

        .zoom-image {
            transition: background-color 0.3s ease;
        }

        .zoom-image:hover {
            background-color: #0052cc;
        }
        .contract-document{
            box-shadow: none;
        }
    </style>

    <script>
        function confirmStatusChange() {
            const status = document.getElementById('status').value;
            if (!status) {
                alert('Vui lòng chọn trạng thái mới!');
                return false;
            }
            let message = `Bạn có chắc muốn thay đổi trạng thái hợp đồng thành "${status}"?`;
            return confirm(message);
        }

        // Handle image zoom modal
        document.addEventListener('DOMContentLoaded', function() {
            const zoomButtons = document.querySelectorAll('.zoom-image');
            const modalImage = document.getElementById('modalImage');

            zoomButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const imageUrl = this.getAttribute('data-image');
                    modalImage.src = imageUrl;
                });
            });
        });
    </script>
@endsection
