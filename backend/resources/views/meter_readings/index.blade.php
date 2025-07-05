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
                    <!-- <span class="badge bg-light text-success ms-2">{{ $rooms->count() }} bản ghi</span> -->
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
                            @for ($y = 2023; $y <= now()->year; $y++)
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
                    <!-- nút reset -->
                    <div class="col-md-1">
                        <button type="reset" class="btn btn-outline-secondary w-100"
                            onclick="location.href='{{ route('meter_readings.index') }}'">
                            <i class="fas fa-undo me-1"></i>Đặt lại
                        </button>
                    </div>
                </form>



                <!-- Danh sách mặc định khi vào trang -->
                <!-- Meter Readings Table -->
                @php
                    $today = now();
                    $day = $today->day;
                    $month = $today->month;
                    $year = $today->year;

                    // Tính toán kỳ hiển thị: nếu đang từ 28 tháng này → 5 tháng sau thì kỳ = tháng sau
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
                                @forelse ($rooms as $index => $room)
                                    @php
                                        $electricity = $room->electricity_kwh ?? 0;
                                        $water = $room->water_m3 ?? 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ $room->name }}: id {{ $room->id }}</td>
                                        <td class="text-center">{{ $displayMonth }}/{{ $displayYear }}</td>
                                        <td class="text-center">{{ number_format($electricity, 2) }} kWh</td>
                                        <td class="text-center">{{ number_format($water, 2) }} m³</td>
                                        <td class="text-center">{{ now()->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" onclick='openUpdateModal(
                                            {{ json_encode($room->id) }},
                                            {{ json_encode($room->name) }},
                                            {{ json_encode($displayMonth) }},
                                            {{ json_encode($displayYear) }},
                                            {{ json_encode($room->electricity_kwh) }},
                                            {{ json_encode($room->water_m3) }}
                                            )'>
                                                <i class="fas fa-edit"></i> Cập nhật
                                            </button>
                                        </td>
                                    </tr>
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
                            <i class="fas fa-info-circle me-2"></i>
                            <input type="hidden" name="room_id" id="update_room_id" value="{{ old('room_id') }}">
                            <input type="hidden" name="month" id="month_meterReading" value="{{ old('month') }}">
                            <input type="hidden" name="year" id="year_meterReading" value="{{ old('year') }}">
                            <strong>Phòng:</strong> <span id="update_room_name"></span> -
                            <strong>Kỳ:</strong> <span id="update_period"></span>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="update_electricity" class="form-label fw-bold">Chỉ số điện hiện tại (kWh)
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-warning text-dark">
                                            <i class="fas fa-bolt"></i>
                                        </span>
                                        <input type="number" name="electricity_kwh" class="form-control"
                                            id="update_electricity" value="{{ old('electricity_kwh') }}" step="0.01" min="0"
                                            placeholder="0.00" required>
                                        @error('electricity_kwh')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">Chỉ số cũ: <span id="old_electricity"
                                            class="fw-bold text-muted"></span> kWh</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="update_water" class="form-label fw-bold">Chỉ số nước hiện tại (m³) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white">
                                            <i class="fas fa-tint"></i>
                                        </span>
                                        <input type="number" name="water_m3" class="form-control" id="update_water"
                                            value="{{ old('water_m3') }}" step="0.01" min="0" placeholder="0.00" required>
                                        @error('water_m3')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-text">Chỉ số cũ: <span id="old_water"
                                            class="fw-bold text-muted"></span> m³</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-warning">
                                            <i class="fas fa-bolt me-1"></i>Tiêu thụ điện
                                        </h6>
                                        <h4 class="text-warning mb-0" id="electricity_consumption">0.00 kWh</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-info">
                                            <i class="fas fa-tint me-1"></i>Tiêu thụ nước
                                        </h6>
                                        <h4 class="text-info mb-0" id="water_consumption">0.00 m³</h4>
                                    </div>
                                </div>
                            </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            const displayIndex = document.getElementById('displayIndex');
            const displayResults = document.getElementById('displayResults');

            // Gửi Ajax khi submit form lọc
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(form);
                const params = new URLSearchParams(formData).toString();

                fetch("{{ route('meter_readings.filter') }}?" + params, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        displayIndex.style.display = 'none'; // ẩn bảng mặc định
                        displayResults.innerHTML = html;     // gán nội dung mới
                        displayResults.style.display = 'block'; // luôn hiển thị
                        bindUpdateButtons(); // gán lại nút cập nhật nếu có
                    })
                    .catch(error => {
                        console.error('Lỗi khi lọc dữ liệu:', error);
                    });
            });

            // Tự động submit form khi thay đổi select lọc
            const filterSelects = document.querySelectorAll('select[name="room_id"], select[name="month"], select[name="year"], select[name="sortOption"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function () {
                    form.requestSubmit(); // dùng requestSubmit để gọi event submit chuẩn
                });
            });

            // Gán sự kiện input cho ô nhập để tính tiêu thụ
            const updateElectricity = document.getElementById('update_electricity');
            const updateWater = document.getElementById('update_water');
            if (updateElectricity && updateWater) {
                updateElectricity.addEventListener('input', calculateConsumption);
                updateWater.addEventListener('input', calculateConsumption);
            }

            // Mở lại modal nếu có lỗi validate và session báo mở lại
            @if ($errors->any() && session('open_update_modal'))
                const modal = new bootstrap.Modal(document.getElementById('updateMeterModal'));
                modal.show();
            @endif

            // Lần đầu bind các nút cập nhật (ở displayIndex)
            bindUpdateButtons();
        });

        // Gán sự kiện onclick cho các nút cập nhật (sau khi render hoặc Ajax load)
        function bindUpdateButtons() {
            const buttons = document.querySelectorAll('[data-update-button]');
            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const data = JSON.parse(this.dataset.updateButton);
                    openUpdateModal(data.id, data.name, data.month, data.year, data.electricity, data.water);
                });
            });
        }

        // Mở modal cập nhật
        function openUpdateModal(id, roomName, month, year, currentElectricity, currentWater) {
            document.getElementById('update_room_id').value = id;
            document.getElementById('month_meterReading').value = month;
            document.getElementById('year_meterReading').value = year;

            document.getElementById('update_room_name').textContent = roomName;
            document.getElementById('update_period').textContent = `${month}/${year}`;

            document.getElementById('update_electricity').value = currentElectricity;
            document.getElementById('update_water').value = currentWater;

            document.getElementById('old_electricity').textContent = currentElectricity;
            document.getElementById('old_water').textContent = currentWater;

            calculateConsumption();

            const modal = new bootstrap.Modal(document.getElementById('updateMeterModal'));
            modal.show();
        }

        // Tính tiêu thụ
        function calculateConsumption() {
            const currentElectricity = parseFloat(document.getElementById('update_electricity').value) || 0;
            const currentWater = parseFloat(document.getElementById('update_water').value) || 0;
            const oldElectricity = parseFloat(document.getElementById('old_electricity').textContent) || 0;
            const oldWater = parseFloat(document.getElementById('old_water').textContent) || 0;

            const electricityConsumption = Math.max(0, currentElectricity - oldElectricity);
            const waterConsumption = Math.max(0, currentWater - oldWater);

            document.getElementById('electricity_consumption').textContent = electricityConsumption.toFixed(2) + ' kWh';
            document.getElementById('water_consumption').textContent = waterConsumption.toFixed(2) + ' m³';
        }
    </script>


@endsection
