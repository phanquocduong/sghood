@extends('layouts.app')

@section('title', 'Thay đổi thứ tự tiện ích')

@section('content')
<div class="container-fluid py-5 px-4">
    <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #007bff, #00c6ff); border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h6 class="mb-0 fw-bold">{{ __('Thay đổi thứ tự tiện ích') }}</h6>
            <div>
                <a href="{{ route('amenities.index') }}" class="btn btn-light shadow-sm" style="transition: all 0.3s;">
                    <i class="fas fa-arrow-left me-1"></i>
                    <span class="d-none d-sm-inline">{{ __('Quay lại danh sách') }}</span>
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Form Filter -->
            <div class="mb-4 p-4" style="background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
                <form action="{{ route('amenities.change-order') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="type_filter" class="form-label fw-semibold text-dark">
                                <i class="fas fa-filter me-1"></i>Loại tiện ích
                            </label>
                            <select id="type_filter" name="type" class="form-select shadow-sm" onchange="this.form.submit()">
                                <option value="">Tất cả</option>
                                <option value="Nhà trọ" {{ ($selectedType == 'Nhà trọ') ? 'selected' : '' }}>Nhà trọ</option>
                                <option value="Phòng trọ" {{ ($selectedType == 'Phòng trọ') ? 'selected' : '' }}>Phòng trọ</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="search" class="form-label fw-semibold text-dark">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" id="search" name="search" class="form-control shadow-sm" placeholder="Nhập tên tiện ích..." value="{{ $searchQuery }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success shadow-sm me-2" style="transition: all 0.3s;">
                            <i class="fas fa-filter me-1"></i> Lọc
                        </button>
                        @if($selectedType || $searchQuery)
                            <a href="{{ route('amenities.change-order') }}" class="btn btn-outline-secondary shadow-sm" style="transition: all 0.3s;" title="Xóa bộ lọc">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filter Results Info -->
            @if($selectedType || $searchQuery)
                <div class="alert alert-light border shadow-sm mb-4" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-filter me-2 text-primary"></i>
                        <span class="fw-semibold">Đang lọc:</span>
                        @if($selectedType)
                            <span class="badge bg-primary ms-2">{{ $selectedType }}</span>
                        @endif
                        @if($searchQuery)
                            <span class="badge bg-info ms-2">Tìm kiếm: "{{ $searchQuery }}"</span>
                        @endif
                        <a href="{{ route('amenities.change-order') }}" class="ms-auto text-decoration-none">
                            <small><i class="fas fa-times me-1"></i>Xóa bộ lọc</small>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Instructions -->
            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3 fs-4"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Hướng dẫn sử dụng:</h6>
                        <p class="mb-0">Kéo và thả các tiện ích để thay đổi thứ tự hiển thị. Thứ tự sẽ được lưu tự động.</p>
                    </div>
                </div>
            </div>

            <!-- Amenities List by Type -->
            @forelse($amenitiesByType as $type => $amenities)
                <div class="type-section mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-primary fs-6 py-2 px-3 me-3" style="border-radius: 8px;">
                            <i class="fas fa-tag me-1"></i>{{ $type }}
                        </div>
                        <div class="text-muted">
                            <small>{{ count($amenities) }} tiện ích</small>
                        </div>
                    </div>

                    <div class="amenity-list" data-type="{{ $type }}">
                        @foreach($amenities as $amenity)
                            <div class="amenity-item card border-0 shadow-sm mb-3" data-id="{{ $amenity->id }}" style="border-radius: 10px; cursor: move; transition: all 0.3s;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-grip-vertical text-muted me-3" style="cursor: grab;"></i>
                                            <div>
                                                <h6 class="mb-1 fw-semibold text-dark">{{ $amenity->name }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-sort-numeric-up me-1"></i>
                                                    Thứ tự: {{ $amenity->order ?? 'Chưa đặt' }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $badgeClass = $amenity->status === 'Hoạt động' ? 'bg-success' : 'bg-secondary';
                                                $statusText = $amenity->status === 'Hoạt động' ? 'Hoạt động' : 'Không hoạt động';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} py-1 px-2 me-2">
                                                {{ $statusText }}
                                            </span>
                                            <i class="fas fa-arrows-alt text-primary" style="font-size: 1.2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-box-open text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">Không có tiện ích nào</h5>
                    <p class="text-muted">Hãy thêm một số tiện ích để bắt đầu sắp xếp.</p>
                    <a href="{{ route('amenities.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus me-1"></i> Thêm tiện ích
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
    <style>
        .sortable-ghost {
            opacity: 0.5;
            background: linear-gradient(45deg, #007bff, #00c6ff);
            transform: rotate(5deg);
        }

        .amenity-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }

        .amenity-item.sortable-chosen {
            transform: scale(1.05);
        }

        .amenity-list {
            min-height: 100px;
            padding: 10px;
            border: 2px dashed transparent;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .amenity-list:hover {
            border-color: #007bff;
            background: rgba(0, 123, 255, 0.05);
        }

        .fas.fa-grip-vertical:hover {
            cursor: grab !important;
        }

        .fas.fa-grip-vertical:active {
            cursor: grabbing !important;
        }

        .type-section {
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Sortable for each amenity list
            document.querySelectorAll('.amenity-list').forEach(function (list) {
                new Sortable(list, {
                    group: list.dataset.type,
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    handle: '.fa-grip-vertical',
                    onStart: function (evt) {
                        // Add visual feedback when dragging starts
                        evt.item.style.opacity = '0.8';
                    },
                    onEnd: function (evt) {
                        // Reset opacity
                        evt.item.style.opacity = '1';

                        const type = evt.to.dataset.type;
                        const items = evt.to.querySelectorAll('.amenity-item');
                        const order = Array.from(items).map(item => item.dataset.id);

                        // Show loading state
                        const loadingToast = showLoadingToast();

                        fetch('{{ route('amenities.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ type: type, order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoadingToast(loadingToast);

                            if (data.success) {
                                showSuccessToast('Thứ tự đã được cập nhật thành công!');
                                // Update order display
                                updateOrderDisplay(evt.to);
                            } else {
                                showErrorToast('Đã xảy ra lỗi: ' + (data.error || 'Không thể cập nhật thứ tự'));
                                // Revert the change
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            hideLoadingToast(loadingToast);
                            console.error('Error:', error);
                            showErrorToast('Đã xảy ra lỗi khi cập nhật thứ tự');
                            // Revert the change
                            window.location.reload();
                        });
                    }
                });
            });

            // Helper functions for toast notifications
            function showLoadingToast() {
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-body bg-info text-white d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Đang cập nhật thứ tự...
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                return toast;
            }

            function hideLoadingToast(toast) {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }

            function showSuccessToast(message) {
                showToast(message, 'success');
            }

            function showErrorToast(message) {
                showToast(message, 'danger');
            }

            function showToast(message, type) {
                const toast = document.createElement('div');
                toast.className = 'position-fixed top-0 end-0 p-3';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-body bg-${type} text-white">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                            ${message}
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 3000);
            }

            function updateOrderDisplay(container) {
                const items = container.querySelectorAll('.amenity-item');
                items.forEach((item, index) => {
                    const orderSpan = item.querySelector('small');
                    if (orderSpan) {
                        orderSpan.innerHTML = `<i class="fas fa-sort-numeric-up me-1"></i>Thứ tự: ${index + 1}`;
                    }
                });
            }
        });
    </script>
@endsection
