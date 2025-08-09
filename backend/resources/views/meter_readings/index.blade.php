@extends('layouts.app')

@section('title', 'Danh sách chỉ số điện nước')

@section('content')
    <style>
        .modal-lg-custom {
            max-width: 90%;
            width: 1200px;
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #28a745, #20c997); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h6 class="mb-0 fw-bold">
                    {{ __('Danh sách chỉ số điện nước') }}
                </h6>
            </div>

            <div class="card-body p-4">
                <!-- Filter Form -->
                <form id="filterForm" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control shadow-sm" name="search"
                            placeholder="Tìm theo tên phòng, nhà trọ..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="month">
                            <option value="">Tất cả tháng</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                    Tháng {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="year">
                            <option value="">Tất cả năm</option>
                            @php
                                $currentYear = now()->year;
                                $startYear = $currentYear - 2; // Default to 2 years ago

                                // Try to get earliest year from existing meter readings
                                if (isset($meterReadings) && $meterReadings->isNotEmpty()) {
                                    $readingYears = $meterReadings->pluck('year')->filter()->min();
                                    if ($readingYears) {
                                        $startYear = min($readingYears, $startYear);
                                    }
                                }
                            @endphp
                            @for ($y = $startYear; $y <= $currentYear; $y++)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="sortOption">
                            <option value="">Sắp xếp</option>
                            <option value="room_asc" {{ request('sortOption') == 'room_asc' ? 'selected' : '' }}>
                                Phòng A-Z
                            </option>
                            <option value="room_desc" {{ request('sortOption') == 'room_desc' ? 'selected' : '' }}>
                                Phòng Z-A
                            </option>
                            <option value="month_desc" {{ request('sortOption') == 'month_desc' ? 'selected' : '' }}>
                                Tháng mới nhất
                            </option>
                            <option value="month_asc" {{ request('sortOption') == 'month_asc' ? 'selected' : '' }}>
                                Tháng cũ nhất
                            </option>
                            <option value="created_at_desc" {{ request('sortOption') == 'created_at_desc' ? 'selected' : '' }}>
                                Ngày tạo mới nhất
                            </option>
                            <option value="created_at_asc" {{ request('sortOption') == 'created_at_asc' ? 'selected' : '' }}>
                                Ngày tạo cũ nhất
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('meter_readings.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-1"></i>
                        </a>
                    </div>
                </form>

                <!-- Display Logic -->
                @php
    $today = now();
    $periodInfo = app('App\Services\MeterReadingService')->getDisplayPeriodInfo();
    $displayMonth = $periodInfo['display_month'];
    $displayYear = $periodInfo['display_year'];
    $isInSpecialPeriod = $periodInfo['is_in_special_period'];
@endphp

@if ($isFiltering)
    <!-- Show filtered results -->
    <div id="displayResults" style="display: block;">
        @include('meter_readings._meter_readings_table', ['meterReadings' => $meterReadings])
    </div>
@elseif($shouldDisplayTable && $rooms && $rooms->isNotEmpty())
    <!-- Show rooms available for meter reading input -->
    <div class="accordion d-block" id="displayIndex">
        @if(isset($displayMode) && $displayMode === 'active_contracts')
            <div class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Có {{ $rooms->flatten()->count() }} phòng có hợp đồng sắp hết hạn (trong 3 ngày) cần nhập chỉ số điện nước.</strong>
                <br><small>Vui lòng nhập chỉ số trước khi hợp đồng hết hạn để tạo hóa đơn thanh toán.</small>
            </div>
        @else
            <div class="alert alert-info mb-3">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>Đang trong thời gian nhập chỉ số điện nước (từ ngày 28 đến ngày 10 tháng sau).</strong>
                <br><small>Có {{ $rooms->flatten()->count() }} phòng cần nhập chỉ số.</small>
            </div>
        @endif

        @forelse ($rooms as $motelId => $groupedRooms)
            <div class="accordion-item">
                <h2 class="accordion-header" id="motelHeading{{ $motelId }}">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#motelCollapse{{ $motelId }}" aria-expanded="true"
                        aria-controls="motelCollapse{{ $motelId }}">
                        🏠 {{ $groupedRooms->first()->motel->name ?? 'Không xác định' }}
                        <small class="ms-2 text-muted">({{ $groupedRooms->count() }} phòng)</small>
                        @if(isset($displayMode) && $displayMode === 'active_contracts')
                            <span class="badge bg-warning ms-2">Sắp hết hạn</span>
                        @endif
                    </button>
                </h2>
                <div id="motelCollapse{{ $motelId }}" class="accordion-collapse collapse show"
                    aria-labelledby="motelHeading{{ $motelId }}" data-bs-parent="#motelAccordion">
                    <div class="accordion-body">
                        <!-- ...existing table code... -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-center" style="width: 5%;">STT</th>
                                        <th class="text-center" style="width: 15%;">Phòng</th>
                                        <th class="text-center" style="width: 12%;">Tháng/Năm</th>
                                        <th class="text-center" style="width: 15%;">Điện (kWh)</th>
                                        <th class="text-center" style="width: 15%;">Nước (m³)</th>
                                        <th class="text-center" style="width: 15%;">Hợp đồng</th>
                                        <th class="text-center" style="width: 18%;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupedRooms as $index => $room)
                                        @php
                                            $electricity = $room->electricity_kwh ?? 0;
                                            $water = $room->water_m3 ?? 0;
                                            $contract = $room->contracts->first();
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">
                                                <strong>{{ $room->name }}</strong>
                                            </td>
                                            <td class="text-center">{{ $displayMonth }}/{{ $displayYear }}</td>
                                            <td class="text-center">{{ number_format($electricity, 2) }} kWh</td>
                                            <td class="text-center">{{ number_format($water, 2) }} m³</td>
                                            <td class="text-center">
                                                @if($contract)
                                                    <small class="text-success">{{ $contract->end_date->format('d/m/Y') }}</small>
                                                    @if(isset($displayMode) && $displayMode === 'active_contracts')
                                                        <br><span class="badge bg-warning text-dark">{{ $contract->end_date->diffInDays(now()) }} ngày</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $roomData = [
                                                        'motel_id' => $motelId,
                                                        'motel_name' => $groupedRooms->first()->motel->name,
                                                        'id' => $room->id,
                                                        'name' => $room->name,
                                                        'electricity_kwh' => $electricity,
                                                        'water_m3' => $water,
                                                        'month' => $displayMonth,
                                                        'year' => $displayYear,
                                                    ];
                                                @endphp
                                                <button class="btn btn-warning btn-sm edit-room"
                                                    data-room='@json($roomData)'>
                                                    <i class="fas fa-plus"></i> Nhập
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-info-circle me-2"></i>
                Không có phòng nào cần cập nhật chỉ số điện nước
            </div>
        @endforelse
    </div>
@else
    <div class="alert alert-info text-center" id="displayIndex">
        <i class="fas fa-info-circle me-2"></i>
        @if(isset($displayMode) && $displayMode === 'active_contracts')
            Không có phòng nào có hợp đồng sắp hết hạn cần nhập chỉ số.
        @else
            Chỉ số điện nước chỉ được cập nhật vào cuối tháng (từ ngày 28 đến ngày 10 tháng sau).
            <br><small>Hoặc khi có phòng với hợp đồng sắp hết hạn trong 3 ngày.</small>
        @endif
    </div>
@endif

<!-- Filtered Results -->
<div id="displayResults" style="display: none;">
    @include('meter_readings._meter_readings_table', ['meterReadings' => $meterReadings])
</div>

                <!-- Pagination -->
                <!-- @if(isset($meterReadings) && $meterReadings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $meterReadings->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif -->
            </div>
        </div>
    </div>

    <!-- Update Meter Reading Modal -->
    <div class="modal fade" id="updateMeterModal" tabindex="-1" aria-labelledby="updateMeterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg-custom">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="updateMeterModalLabel">
                        <i class="fas fa-edit me-2"></i>Cập nhật chỉ số điện nước
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('meter_readings.store') }}" method="POST" id="updateMeterForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Nhà trọ:</strong> <span id="update_motel_name"></span> -
                            <strong>Kỳ:</strong> <span id="update_period"></span>
                        </div>
                        <input type="hidden" name="month" id="modal_month">
                        <input type="hidden" name="year" id="modal_year">
                        <input type="hidden" name="motel_id" id="modal_motel_id">
                        <input type="hidden" name="motel_name" id="modal_motel_name">
                        <div id="bulk_error_message" class="alert alert-danger d-none" role="alert"></div>
                        <div id="room_inputs_container" style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                            <!-- Rooms will be dynamically inserted here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Hủy
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Cập nhật chỉ số
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fix: Check if $errors is an object before calling messages()
        window.readingErrors = @if($errors && is_object($errors)) {!! json_encode($errors->messages()) !!} @else{} @endif;
        window.oldInput = {!! json_encode(old('readings', [])) !!};
        window.motelData = {!! json_encode(session('motel_data', null)) !!};

        document.addEventListener("DOMContentLoaded", function () {
            const updateModal = new bootstrap.Modal(document.getElementById("updateMeterModal"));
            const motelButtons = document.querySelectorAll("[data-motel-button]");
            const editRoomButtons = document.querySelectorAll(".edit-room");
            const motelNameSpan = document.getElementById("update_motel_name");
            const periodSpan = document.getElementById("update_period");
            const roomInputsContainer = document.getElementById("room_inputs_container");
            const filterForm = document.getElementById("filterForm");
            const displayResults = document.getElementById("displayResults");
            const displayIndex = document.getElementById("displayIndex");
            const modalMonthInput = document.getElementById("modal_month");
            const modalYearInput = document.getElementById("modal_year");
            const modalMotelIdInput = document.getElementById("modal_motel_id");
            const modalMotelNameInput = document.getElementById("modal_motel_name");
            const bulkErrorMessage = document.getElementById("bulk_error_message");

            let currentModalData = null;

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function renderModal(data, isSingleRoom = false) {
                console.log('Rendering modal with data:', data);
                console.log('Current errors:', window.readingErrors);
                console.log('Old input:', window.oldInput);

                currentModalData = data;
                motelNameSpan.textContent = data.motel_name || "Unknown";
                periodSpan.textContent = `Tháng ${data.month}/${data.year}`;
                modalMonthInput.value = data.month || '';
                modalYearInput.value = data.year || '';
                modalMotelIdInput.value = data.motel_id || '';
                modalMotelNameInput.value = data.motel_name || '';
                roomInputsContainer.innerHTML = "";
                bulkErrorMessage.classList.add('d-none');

                const rooms = isSingleRoom ? [{
                    id: data.id,
                    name: data.name,
                    electricity_kwh: data.electricity_kwh,
                    water_m3: data.water_m3
                }] : data.rooms;

                const roomsPerGroup = 10;
                const roomGroups = [];
                for (let i = 0; i < rooms.length; i += roomsPerGroup) {
                    roomGroups.push(rooms.slice(i, i + roomsPerGroup));
                }

                roomGroups.forEach((group, groupIndex) => {
                    const hasError = group.some((room, index) => {
                        const globalIndex = groupIndex * roomsPerGroup + index;
                        return window.readingErrors?.[`readings.${globalIndex}.electricity_kwh`] ||
                            window.readingErrors?.[`readings.${globalIndex}.water_m3`];
                    });

                    const expanded = hasError || groupIndex === 0;

                    const groupHtml = `
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading${groupIndex}">
                                            <button class="accordion-button ${!expanded ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${groupIndex}" aria-expanded="${expanded}" aria-controls="collapse${groupIndex}">
                                                Phòng ${group[0].name} - ${group[group.length - 1].name}
                                            </button>
                                        </h2>
                                        <div id="collapse${groupIndex}" class="accordion-collapse collapse ${expanded ? 'show' : ''}" aria-labelledby="heading${groupIndex}">
                                            <div class="accordion-body">
                                                 ${group.map((room, index) => {
                        const globalIndex = groupIndex * roomsPerGroup + index;
                        const electricityError = window.readingErrors?.[`readings.${globalIndex}.electricity_kwh`]?.[0] || "";
                        const waterError = window.readingErrors?.[`readings.${globalIndex}.water_m3`]?.[0] || "";
                        const oldElectricity = window.oldInput[globalIndex]?.electricity_kwh ?? room.electricity_kwh ?? "";
                        const oldWater = window.oldInput[globalIndex]?.water_m3 ?? room.water_m3 ?? "";

                        return `
                                                                        <div class="mb-2">
                                                                            <div class="fw-bold text-primary">${room.name}</div>
                                                                            <input type="hidden" name="readings[${globalIndex}][room_id]" value="${room.id}">
                                                                            <div class="row g-2">
                                                                                <div class="col-md-6">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <span class="input-group-text bg-warning text-dark">
                                                                                            <i class="fas fa-bolt"></i>
                                                                                        </span>
                                                                                        <input type="number" step="0.01" min="0" name="readings[${globalIndex}][electricity_kwh]" class="form-control ${electricityError ? 'is-invalid' : ''}" placeholder="0.00" value="${oldElectricity}" required aria-label="Chỉ số điện cho phòng ${room.name}">
                                                                                    </div>
                                                                                    ${electricityError ? `<div class="invalid-feedback d-block">${electricityError}</div>` : ""}
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <div class="input-group input-group-sm">
                                                                                        <span class="input-group-text bg-info text-white">
                                                                                            <i class="fas fa-tint"></i>
                                                                                        </span>
                                                                                        <input type="number" step="0.01" min="0" name="readings[${globalIndex}][water_m3]" class="form-control ${waterError ? 'is-invalid' : ''}" placeholder="0.00" value="${oldWater}" required aria-label="Chỉ số nước cho phòng ${room.name}">
                                                                                    </div>
                                                                                    ${waterError ? `<div class="invalid-feedback d-block">${waterError}</div>` : ""}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    `;
                    }).join('')}
                                            </div>
                                        </div>
                                    </div>
                                `;
                    roomInputsContainer.insertAdjacentHTML("beforeend", groupHtml);
                });

                updateModal.show();
            }

            // Event listeners
            motelButtons.forEach((button) => {
                button.addEventListener("click", function () {
                    window.readingErrors = {};
                    window.oldInput = [];
                    const data = JSON.parse(this.getAttribute("data-motel-button"));
                    renderModal(data);
                });
            });

            editRoomButtons.forEach((button) => {
                button.addEventListener("click", function () {
                    window.readingErrors = {};
                    window.oldInput = [];
                    const data = JSON.parse(this.getAttribute("data-room"));
                    renderModal(data, true);
                });
            });

            // Modal close handler
            updateModal._element.addEventListener('hidden.bs.modal', function () {
                console.log('Modal closed, resetting state');
                window.readingErrors = {};
                window.oldInput = [];
                currentModalData = null;
                roomInputsContainer.innerHTML = "";
                modalMonthInput.value = "";
                modalYearInput.value = "";
                modalMotelIdInput.value = "";
                modalMotelNameInput.value = "";
                bulkErrorMessage.classList.add('d-none');
            });

            // Reopen modal with validation errors
            if (Object.keys(window.readingErrors).length > 0 && window.motelData && window.oldInput.length > 0) {
                console.log('Reopening modal with validation errors');
                renderModal({
                    motel_id: window.motelData.motel_id,
                    motel_name: window.motelData.motel_name || "Unknown",
                    month: window.motelData.month,
                    year: window.motelData.year,
                    rooms: window.motelData.rooms || []
                });
            }

            // Form submission handler with improved error handling
            document.getElementById("updateMeterForm").addEventListener("submit", debounce(function (e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';

                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            updateModal.hide();
                            // Show success message before reload
                            const successAlert = document.createElement('div');
                            successAlert.className = 'alert alert-success alert-dismissible fade show';
                            successAlert.innerHTML = `
                                        <i class="fas fa-check-circle me-2"></i>
                                        ${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    `;
                            document.querySelector('.container-fluid').prepend(successAlert);

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Handle validation errors
                            window.readingErrors = data.errors || {};
                            window.oldInput = Array.from(formData.entries())
                                .filter(([key]) => key.startsWith('readings'))
                                .reduce((acc, [key, value]) => {
                                    const match = key.match(/readings\[(\d+)\]\[(\w+)\]/);
                                    if (match) {
                                        const index = parseInt(match[1]);
                                        const field = match[2];
                                        acc[index] = acc[index] || {};
                                        acc[index][field] = value;
                                    }
                                    return acc;
                                }, []);
                            window.motelData = currentModalData;
                            renderModal(currentModalData, currentModalData.rooms && currentModalData.rooms.length === 1);
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                        bulkErrorMessage.textContent = 'Đã xảy ra lỗi khi gửi dữ liệu. Vui lòng thử lại.';
                        bulkErrorMessage.classList.remove('d-none');
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Cập nhật chỉ số';
                    });
            }, 500));

            // Filter form submission with improved error handling
            filterForm.addEventListener("submit", function (e) {
                e.preventDefault();
                displayResults.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>';
                displayResults.style.display = "block";
                if (displayIndex) displayIndex.style.display = "none";
                displayIndex.classList.add("d-none");


                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();
                const url = `{{ route('meter_readings.filter') }}?${params}`;

                fetch(url, {
                    method: "GET",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "text/html",
                    },
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then((data) => {
                        displayResults.innerHTML = data;
                        displayResults.style.display = "block";
                        const displayIndex = document.getElementById("displayIndex");
                        if (displayIndex) {
                            displayIndex.style.display = "none";
                            displayIndex.classList.add("d-none"); // thêm class d-none để tránh nháy
                        }
                        bindDynamicEventListeners();
                    })
                    .catch((error) => {
                        console.error("Error filtering:", error);
                        displayResults.innerHTML = '<div class="alert alert-danger">Đã xảy ra lỗi khi lọc dữ liệu. Vui lòng thử lại.</div>';
                    });
            });

            // Function to bind event listeners to dynamically loaded content
            function bindDynamicEventListeners() {
                // Re-bind motel buttons
                document.querySelectorAll("[data-motel-button]").forEach(button => {
                    button.addEventListener("click", function () {
                        window.readingErrors = {};
                        window.oldInput = [];
                        const data = JSON.parse(this.getAttribute("data-motel-button"));
                        renderModal(data);
                    });
                });

                // Re-bind edit room buttons
                document.querySelectorAll(".edit-room").forEach(button => {
                    button.addEventListener("click", function () {
                        window.readingErrors = {};
                        window.oldInput = [];
                        const data = JSON.parse(this.getAttribute("data-room"));
                        renderModal(data, true);
                    });
                });
            }

            // Auto-submit form on filter change
            const filterSelects = filterForm.querySelectorAll('select, input[name="search"]');
            filterSelects.forEach(element => {
                element.addEventListener('change', function () {
                    filterForm.requestSubmit();
                });
            });

            // Debounced search input
            const searchInput = filterForm.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function () {
                    filterForm.requestSubmit();
                }, 500));
            }
        });
    </script>
@endsection
