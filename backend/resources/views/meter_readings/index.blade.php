@extends('layouts.app')

@section('title', 'Danh sách chỉ số điện nước')

@section('content')
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
                    $day = $today->day;
                    $month = $today->month;
                    $year = $today->year;
                    $startDate = now()->copy()->day(1);
                    $endDate = now()->copy()->addMonthNoOverflow()->day(5);
                    $shouldDisplayTable = $today->between($startDate, $endDate);
                    $displayMonth = $shouldDisplayTable ? $today->copy()->addMonthNoOverflow()->month : $month;
                    $displayYear = $shouldDisplayTable ? $today->copy()->addMonthNoOverflow()->year : $year;
                @endphp

                @if ($isFiltering || $shouldDisplayTable)
                    <div class="table-responsive" style="display: block;" id="displayIndex">
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
                                @forelse ($rooms as $motelId => $groupedRooms)
                                    <tr class="table-primary">
                                        <th colspan="6">🏠 {{ $groupedRooms->first()->motel->name ?? 'Không xác định' }}</th>
                                        <th class="text-center">
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
                                        </th>
                                    </tr>
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
                                                <button class="btn btn-warning btn-sm edit-room" data-room='@json($roomData)'>
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="updateMeterModalLabel">
                        <i class="fas fa-edit me-2"></i>Cập nhật chỉ số điện nước
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('meter_readings.store') }}" method="POST" id="updateMeterForm" novalidate>
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
                        <div id="room_inputs_container"></div>
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

            let currentModalData = null;

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

                const rooms = isSingleRoom ? [{
                    id: data.id,
                    name: data.name,
                    electricity_kwh: data.electricity_kwh,
                    water_m3: data.water_m3
                }] : data.rooms;

                rooms.forEach((room, index) => {
                    const electricityError = window.readingErrors?.[`readings.${index}.electricity_kwh`]?.[0] || "";
                    const waterError = window.readingErrors?.[`readings.${index}.water_m3`]?.[0] || "";
                    const oldElectricity = window.oldInput[index]?.electricity_kwh ?? room.electricity_kwh ?? "";
                    const oldWater = window.oldInput[index]?.water_m3 ?? room.water_m3 ?? "";

                    console.log(`Room ${index}:`, { id: room.id, electricityError, waterError, oldElectricity, oldWater });

                    const roomHtml = `
                            <div class="mb-2 mt-3 fw-bold text-primary">${room.name}</div>
                            <input type="hidden" name="readings[${index}][room_id]" value="${room.id}">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Chỉ số điện (kWh)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-warning text-dark">
                                                <i class="fas fa-bolt"></i>
                                            </span>
                                            <input type="number" step="0.01" min="0" name="readings[${index}][electricity_kwh]" class="form-control" placeholder="0.00" value="${oldElectricity}" required>
                                        </div>
                                        ${electricityError ? `<div class="text-danger small mt-1">${electricityError}</div>` : ""}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Chỉ số nước (m³)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-info text-white">
                                                <i class="fas fa-tint"></i>
                                            </span>
                                            <input type="number" step="0.01" min="0" name="readings[${index}][water_m3]" class="form-control" placeholder="0.00" value="${oldWater}" required>
                                        </div>
                                        ${waterError ? `<div class="text-danger small mt-1">${waterError}</div>` : ""}
                                    </div>
                                </div>
                            </div>
                        `;
                    roomInputsContainer.insertAdjacentHTML("beforeend", roomHtml);
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
