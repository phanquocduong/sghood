@extends('layouts.app')

@section('title', 'Danh sách chỉ số điện nước')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container-fluid py-5 px-4">
        <div class="card shadow-lg border-0" style="border-radius: 15px; background: #fff;">
            <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center"
                style="background: linear-gradient(90deg, #28a745, #20c997); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold">{{ __('Danh sách chỉ số điện nước') }}
                        <span class="badge bg-light text-success ms-2">15 bản ghi</span>
                    </h6>
                </div>
                <div>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Filter Form -->
                <div class="mb-4">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="room_id">
                                <option value="">Tất cả phòng</option>
                                <option value="1">Phòng 101</option>
                                <option value="2">Phòng 102</option>
                                <option value="3">Phòng 201</option>
                                <option value="4">Phòng 202</option>
                                <option value="5">Phòng 301</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="month">
                                <option value="">Tất cả tháng</option>
                                <option value="1">Tháng 1</option>
                                <option value="2">Tháng 2</option>
                                <option value="3">Tháng 3</option>
                                <option value="4">Tháng 4</option>
                                <option value="5">Tháng 5</option>
                                <option value="6">Tháng 6</option>
                                <option value="7">Tháng 7</option>
                                <option value="8">Tháng 8</option>
                                <option value="9">Tháng 9</option>
                                <option value="10">Tháng 10</option>
                                <option value="11">Tháng 11</option>
                                <option value="12">Tháng 12</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select shadow-sm" name="year">
                                <option value="">Tất cả năm</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select shadow-sm" name="sortOption">
                                <option value="">Sắp xếp</option>
                                <option value="room_asc">Phòng A-Z</option>
                                <option value="room_desc">Phòng Z-A</option>
                                <option value="month_desc">Tháng mới nhất</option>
                                <option value="month_asc">Tháng cũ nhất</option>
                                <option value="created_at_desc">Ngày tạo mới nhất</option>
                                <option value="created_at_asc">Ngày tạo cũ nhất</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-filter me-1"></i>Lọc
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Alert Messages -->
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert"
                    style="display: none;">
                    <i class="fas fa-check-circle me-2"></i>Thao tác thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <!-- Meter Readings Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-success">
                            <tr>
                                <th scope="col" style="width: 5%;" class="text-center">STT</th>
                                <th scope="col" style="width: 15%;" class="text-center">Phòng</th>
                                <th scope="col" style="width: 12%;" class="text-center">Tháng/Năm</th>
                                <th scope="col" style="width: 15%;" class="text-center">Điện (kWh)</th>
                                <th scope="col" style="width: 15%;" class="text-center">Nước (m³)</th>
                                <th scope="col" style="width: 15%;" class="text-center">Ngày ghi</th>
                                <th scope="col" style="width: 18%;" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!isset($rooms) || $rooms->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @else
                                @foreach ($rooms as $index => $room)
                                    @php
                                        $month = now()->month;
                                        $year = now()->year;
                                        if (isset($room->electricity_kwh) && isset($room->water_m3)) {
                                            $electricity = $room->electricity_kwh;
                                            $water = $room->water_m3;
                                        } else {
                                            $electricity = 0;
                                            $water = 0;
                                        }
                                    @endphp
                                    <tr class="table-row">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ $room->name }}</td>
                                        <td class="text-center">{{ $month }}/{{ $year }}</td>
                                        <td class="text-center">{{number_format($electricity, 2) }} kWh</td>
                                        <td class="text-center">{{ number_format($water, 2) }} m³</td>
                                        <td class="text-center">{{ now()->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm"
                                                onclick='openUpdateModal(
                                                                                                                                                    {{ json_encode($room->id) }},
                                                                                                                                                    {{ json_encode($room->name) }},
                                                                                                                                                    {{ json_encode($month) }},
                                                                                                                                                    {{ json_encode($year) }},
                                                                                                                                                    {{ json_encode($room->electricity_kwh) }},
                                                                                                                                                    {{ json_encode($room->water_m3) }}
                                                                                                                                                )'>

                                                <i class="fas fa-edit"></i> Cập nhật
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Trước</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">3</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Sau</a>
                            </li>
                        </ul>
                    </nav>
                </div>
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
            // Auto-submit form when filter changes
            const filterSelects = document.querySelectorAll('select[name="room_id"], select[name="month"], select[name="year"], select[name="sortOption"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function () {
                    this.form.submit();
                });
            });

            // Calculate consumption when values change
            const updateElectricity = document.getElementById('update_electricity');
            const updateWater = document.getElementById('update_water');

            updateElectricity.addEventListener('input', calculateConsumption);
            updateWater.addEventListener('input', calculateConsumption);
        });

        document.addEventListener('DOMContentLoaded', function () {
            @if ($errors->any() && session('open_update_modal'))
                const modal = new bootstrap.Modal(document.getElementById('updateMeterModal'));
                modal.show();
            @endif
        });

        function openUpdateModal(id, roomName, month, year, currentElectricity, currentWater) {
            const form = document.getElementById('updateMeterForm');
            // form.action = `meter_readings.store`;

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

    <style>
        .breadcrumb {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "›";
            font-weight: bold;
            color: #6c757d;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .table-row:hover {
            background-color: #f8f9fa;
        }

        .table-success {
            background-color: #d1e7dd;
        }

        .table-success th {
            border-color: #badbcc;
            color: #0f5132;
        }

        .badge {
            font-size: 0.9em;
        }
    </style>

@endsection