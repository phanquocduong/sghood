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
                        <input type="text" class="form-control shadow-sm" name="room_id" placeholder="Tìm theo tên phòng"
                            value="{{ request('room_id') }}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="month">
                            <option value="">Tất cả tháng</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>Tháng {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="year">
                            <option value="">Tất cả năm</option>
                            @php
                                $currentYear = now()->year;
                                $startYear = $currentYear;

                                if (isset($meterReadings) && $meterReadings->isNotEmpty()) {
                                    $readingYears = $meterReadings->pluck('year')->filter()->min();
                                    if ($readingYears) {
                                        $startYear = min($readingYears, $startYear);
                                    }
                                }
                            @endphp
                            @for ($y = $startYear; $y <= $currentYear; $y++)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select shadow-sm" name="sortOption">
                            <option value="">Sắp xếp</option>
                            <option value="room_asc" {{ request('sortOption') == 'room_asc' ? 'selected' : '' }}>Phòng A-Z
                            </option>
                            <option value="room_desc" {{ request('sortOption') == 'room_desc' ? 'selected' : '' }}>Phòng Z-A
                            </option>
                            <option value="month_desc" {{ request('sortOption') == 'month_desc' ? 'selected' : '' }}>Tháng mới
                                nhất</option>
                            <option value="month_asc" {{ request('sortOption') == 'month_asc' ? 'selected' : '' }}>Tháng cũ
                                nhất</option>
                            <option value="created_at_desc" {{ request('sortOption') == 'created_at_desc' ? 'selected' : '' }}>Ngày tạo mới nhất</option>
                            <option value="created_at_asc" {{ request('sortOption') == 'created_at_asc' ? 'selected' : '' }}>
                                Ngày tạo cũ nhất</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="fas fa-filter me-1"></i>Lọc
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('meter_readings.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-1"></i>Đặt lại
                        </a>
                    </div>
                </form>

                <!-- Meter Readings Table -->
                @php
                    $today = now();
                    $month = $today->month;
                    $year = $today->year;

                    // Khoảng thời gian đặc biệt: từ ngày 28 tháng hiện tại đến ngày 5 tháng tiếp theo
                    $startDate = $today->copy()->day(24);
                    $endDate = $today->copy()->addMonthNoOverflow()->day(5)->endOfDay();

                    $shouldDisplayTable = $today->between($startDate, $endDate);

                    if ($shouldDisplayTable) {
                        if ($today->day >= 24 && $today->day <= 31) {
                            // Nếu đang trong khoảng 28-31 tháng hiện tại -> hiển thị tháng hiện tại
                            $displayMonth = $today->month;
                            $displayYear = $today->year;
                        } else {
                            // Nếu đang trong 5 ngày đầu tháng tiếp theo -> hiển thị tháng đó - 1
                            $previousMonth = $today->copy()->subMonthNoOverflow();
                            $displayMonth = $previousMonth->month;
                            $displayYear = $previousMonth->year;
                        }
                    } else {
                        // Ngoài khoảng đặc biệt -> hiển thị tháng hiện tại
                        $displayMonth = $month;
                        $displayYear = $year;
                    }
                @endphp

                @if ($isFiltering || $shouldDisplayTable)
                    <div class="accordion" id="motelAccordion" style="display: block;" id="displayIndex">
                        @forelse ($rooms as $motelId => $groupedRooms)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="motelHeading{{ $motelId }}">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#motelCollapse{{ $motelId }}" aria-expanded="true"
                                        aria-controls="motelCollapse{{ $motelId }}">
                                        🏠 {{ $groupedRooms->first()->motel->name ?? 'Không xác định' }}
                                    </button>
                                </h2>
                                <div id="motelCollapse{{ $motelId }}" class="accordion-collapse collapse show"
                                    aria-labelledby="motelHeading{{ $motelId }}" data-bs-parent="#motelAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            @php
                                                $motelData = [
                                                    'motel_id' => $motelId,
                                                    'motel_name' => $groupedRooms->first()->motel->name,
                                                    'month' => $displayMonth,
                                                    'year' => $displayYear,
                                                    'rooms' => collect($groupedRooms)->map(function ($r) {
                                                        return [
                                                            'id' => $r->id,
                                                            'name' => $r->name,
                                                            'electricity_kwh' => $r->electricity_kwh,
                                                            'water_m3' => $r->water_m3
                                                        ];
                                                    })->values()
                                                ];
                                            @endphp
                                            <button class="btn btn-warning btn-sm" data-motel-button='@json($motelData)'>
                                                <i class="fas fa-edit"></i> Cập nhật tất cả
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered align-middle">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th class="text-center" style="width: 5%;">STT</th>
                                                        <th class="text-center" style="width: 15%;">Phòng</th>
                                                        <th class="text-center" style="width: 12%;">Tháng/Năm</th>
                                                        <th class="text-center" style="width: 15%;">Điện (kWh)</th>
                                                        <th class="text-center" style="width: 15%;">Nước (m³)</th>
                                                        <th class="text-center" style="width: 15%;">Ngày ghi</th>
                                                        <th class="text-center" style="width: 18%;">Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($groupedRooms as $index => $room)
                                                        @php
                                                            $electricity = $room->electricity_kwh ?? 0;
                                                            $water = $room->water_m3 ?? 0;
                                                        @endphp
                                                        <tr>
                                                            <td class="text-center">{{ $index + 1 }}</td>
                                                            <td class="text-center">{{ $room->name }}</td>
                                                            <td class="text-center">{{ $displayMonth }}/{{ $displayYear }}</td>
                                                            <td class="text-center">{{ number_format($electricity, 2) }} kWh</td>
                                                            <td class="text-center">{{ number_format($water, 2) }} m³</td>
                                                            <td class="text-center">{{ now()->format('d/m/Y') }}</td>
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
                                                                    <i class="fas fa-plus"></i>
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
                            <div class="text-center text-muted">Không có dữ liệu</div>
                        @endforelse
                    </div>
                @else
                    <div class="alert alert-info text-center" style="display: block;" id="displayIndex">
                        <i class="fas fa-info-circle me-2"></i>
                        Chỉ số điện nước chỉ được cập nhật vào cuối tháng.
                        Vui lòng kiểm tra sau ngày 27.
                    </div>
                @endif

                <!-- Kết quả sau khi lọc -->
                <div id="displayResults" style="display: none;">
                    @include('meter_readings._meter_readings_table', ['meterReadings' => $meterReadings])
                </div>

                <!-- Pagination -->
                @if(isset($meterReadings) && $meterReadings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-4">
                        {{ $meterReadings->links('pagination::bootstrap-4') }}
                    </div>
                @endif
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
                        <div class="mb-3 row g-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                    </div>
                                </div>
                            </div>
                            <div id="room_inputs_container"
                                style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
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
        window.readingErrors = {!! json_encode($errors->messages()) !!};
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
                                                                            <input type="number" step="0.01" min="0" max="2000" name="readings[${globalIndex}][electricity_kwh]" class="form-control" placeholder="0.00" value="${oldElectricity}" required aria-label="Chỉ số điện cho phòng ${room.name}" >
                                                                        </div>
                                                                        ${electricityError ? `<div class="text-danger small mt-1">${electricityError}</div>` : ""}
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="input-group input-group-sm">
                                                                            <span class="input-group-text bg-info text-white">
                                                                                <i class="fas fa-tint"></i>
                                                                            </span>
                                                                            <input type="number" step="0.01" min="0" max="200" name="readings[${globalIndex}][water_m3]" class="form-control" placeholder="0.00" value="${oldWater}" required aria-label="Chỉ số nước cho phòng ${room.name}">
                                                                        </div>
                                                                        ${waterError ? `<div class="text-danger small mt-1">${waterError}</div>` : ""}
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
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
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
                            renderModal(currentModalData, currentModalData.rooms.length === 1);
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-save me-1"></i>Cập nhật chỉ số';
                    });
            }, 500));

            filterForm.addEventListener("submit", function (e) {
                e.preventDefault();
                displayResults.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
                displayResults.style.display = "block";
                displayIndex.style.display = "none";

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
                    })
                    .catch((error) => {
                        console.error("Error filtering:", error);
                        displayResults.innerHTML = '<div class="alert alert-danger">Đã xảy ra lỗi khi lọc dữ liệu.</div>';
                    });
            });

            filterForm.addEventListener("reset", function () {
                displayResults.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
                displayResults.style.display = "block";
                displayIndex.style.display = "none";

                fetch("{{ route('meter_readings.index') }}", {
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
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(data, "text/html");
                        const newDisplayIndex = doc.getElementById("displayIndex");
                        const newDisplayResults = doc.getElementById("displayResults");

                        displayIndex.innerHTML = newDisplayIndex.innerHTML;
                        displayIndex.style.display = "block";
                        displayResults.innerHTML = newDisplayResults.innerHTML;
                        displayResults.style.display = "none";

                        filterForm.reset();
                    })
                    .catch((error) => {
                        console.error("Error resetting:", error);
                        window.location.href = "{{ route('meter_readings.index') }}";
                    });
            });
        });
    </script>
@endsection
