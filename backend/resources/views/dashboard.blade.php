@extends('layouts.app')

@section('content')
<!-- Notes Section Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-gradient rounded shadow-sm p-4" style="background: linear-gradient(135deg, #6a11cb, #2575fc);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Ghi chú</h5>
                    <form action="{{ route('notes.index') }}" method="GET">
                        <button type="submit" class="btn btn-link text-decoration-none p-0">Xem tất cả</button>
                    </form>
                </div>
                <ul id="noteList" class="list-group list-group-flush">
                    @isset($notes)
                        @forelse($notes->take(3) as $note)
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-id-user="{{ $note->user_id }}" data-note-id="{{ $note->id }}">
                                <div>
                                    <span>{{ $note->content }}</span>
                                    <small class="d-block text-muted">
                                        <span style="font-weight: bold; color: #ff4500;">[{{ $note->type ?? 'Không xác định' }}]</span>
                                        bởi <span style="font-weight: bold; color: #1e90ff;">{{ $note->user->name ?? 'Người dùng không tồn tại' }}</span> -
                                        {{ $note->created_at ? $note->created_at->format('d/m/Y, h:i A') : 'Chưa có thời gian' }}
                                    </small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">Chưa có ghi chú nào.</li>
                        @endforelse
                    @else
                        <li class="list-group-item text-center text-muted">Chưa tải được ghi chú: {{ session('error') ?? 'Lỗi không xác định' }}</li>
                    @endisset
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Notes Section End -->

<!-- Sale & Revenue Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-line fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Doanh thu hôm nay</p>
                    <h6 class="mb-0">{{ number_format(1250000, 0, ',', '.') }} VNĐ</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-bar fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Doanh thu tháng này</p>
                    <h6 class="mb-0">{{ number_format(32500000, 0, ',', '.') }} VNĐ</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-area fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Doanh thu năm nay</p>
                    <h6 class="mb-0">{{ number_format(285000000, 0, ',', '.') }} VNĐ</h6>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                <i class="fa fa-chart-pie fa-3x text-primary"></i>
                <div class="ms-3">
                    <p class="mb-2">Tổng doanh thu</p>
                    <h6 class="mb-0">{{ number_format(456000000, 0, ',', '.') }} VNĐ</h6>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Sale & Revenue End -->

<!-- Revenue Chart Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0">Biểu đồ doanh thu theo tháng</h6>
                </div>
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0">Biểu đồ doanh thu theo năm</h6>
                </div>
                <canvas id="yearlyRevenueChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu theo tháng
const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyRevenueChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [18500000, 22000000, 19800000, 25600000, 28200000, 31000000, 29500000, 33200000, 27800000, 30500000, 32500000, 35200000],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo năm
const yearlyCtx = document.getElementById('yearlyRevenueChart').getContext('2d');
const yearlyRevenueChart = new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: ['2022', '2023', '2024'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [245000000, 315000000, 285000000],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 205, 86, 0.2)'
            ],
            borderColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        }
    }
});
</script>
<!-- Revenue Chart End -->

<!-- check-in check-out Start -->
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0">Lịch Check-in Sắp Tới</h6>
                    <a href="">Xem Tất Cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table text-start align-middle table-hover mb-0">
                        <thead>
                            <tr class="text-dark">
                                <th scope="col">Khách Hàng</th>
                                <th scope="col">Ngày Check-in</th>
                                <th scope="col">Phòng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nguyễn Văn A</td>
                                <td>25/12/2024</td>
                                <td>P101</td>
                            </tr>
                            <tr>
                                <td>Trần Thị B</td>
                                <td>26/12/2024</td>
                                <td>P202</td>
                            </tr>
                            <tr>
                                <td>Lê Văn C</td>
                                <td>27/12/2024</td>
                                <td>P305</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light text-center rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h6 class="mb-0">Lịch Check-out Sắp Tới</h6>
                    <a href="">Xem Tất Cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table text-start align-middle table-hover mb-0">
                        <thead>
                            <tr class="text-dark">
                                <th scope="col">Khách Hàng</th>
                                <th scope="col">Ngày Check-out</th>
                                <th scope="col">Phòng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Phạm Văn D</td>
                                <td>24/12/2024</td>
                                <td>P401</td>
                            </tr>
                            <tr>
                                <td>Hoàng Thị E</td>
                                <td>25/12/2024</td>
                                <td>P503</td>
                            </tr>
                            <tr>
                                <td>Đỗ Văn F</td>
                                <td>26/12/2024</td>
                                <td>P204</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- check-in check-out end -->

<!-- Fix Request Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Danh sách yêu cầu sửa chữa</h6>
            <a href="">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col"><input class="form-check-input" type="checkbox"></th>
                        <th scope="col">Ngày yêu cầu</th>
                        <th scope="col">Mã yêu cầu</th>
                        <th scope="col">Khách hàng</th>
                        <th scope="col">Phòng</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>24/12/2024</td>
                        <td>REP-001</td>
                        <td>Nguyễn Văn A</td>
                        <td>P101</td>
                        <td><span class="badge bg-warning">Đang xử lý</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>24/12/2024</td>
                        <td>REP-002</td>
                        <td>Trần Thị B</td>
                        <td>P202</td>
                        <td><span class="badge bg-success">Hoàn thành</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>23/12/2024</td>
                        <td>REP-003</td>
                        <td>Lê Văn C</td>
                        <td>P305</td>
                        <td><span class="badge bg-danger">Chưa xử lý</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>23/12/2024</td>
                        <td>REP-004</td>
                        <td>Phạm Văn D</td>
                        <td>P401</td>
                        <td><span class="badge bg-warning">Đang xử lý</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>22/12/2024</td>
                        <td>REP-005</td>
                        <td>Hoàng Thị E</td>
                        <td>P503</td>
                        <td><span class="badge bg-success">Hoàn thành</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Fix Request End -->

<!-- List users Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Danh sách người đang thuê trọ</h6>
            <a href="">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Họ và tên</th>
                        <th scope="col">Phòng</th>
                        <th scope="col">Số điện thoại</th>
                        <th scope="col">Ngày thuê</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-3" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <span>Nguyễn Văn A</span>
                            </div>
                        </td>
                        <td>P101</td>
                        <td>0123456789</td>
                        <td>01/01/2024</td>
                        <td><span class="badge bg-success">Đang thuê</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-3" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <span>Trần Thị B</span>
                            </div>
                        </td>
                        <td>P202</td>
                        <td>0987654321</td>
                        <td>15/02/2024</td>
                        <td><span class="badge bg-success">Đang thuê</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-3" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <span>Lê Văn C</span>
                            </div>
                        </td>
                        <td>P305</td>
                        <td>0912345678</td>
                        <td>20/03/2024</td>
                        <td><span class="badge bg-warning">Sắp hết hạn</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center"></div>
                                <img class="rounded-circle me-3" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <span>Phạm Văn D</span>
                            </div>
                        </td>
                        <td>P401</td>
                        <td>0934567890</td>
                        <td>10/04/2024</td>
                        <td><span class="badge bg-success">Đang thuê</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img class="rounded-circle me-3" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                <span>Hoàng Thị E</span>
                            </div>
                        </td>
                        <td>P503</td>
                        <td>0945678901</td>
                        <td>05/05/2024</td>
                        <td><span class="badge bg-success">Đang thuê</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Chi tiết</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- List users End -->

<!-- List new notification -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Thông báo mới</h6>
            <a href="">Xem tất cả</a>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Thông báo 1: Hệ thống bảo trì vào ngày mai.</span>
                <small class="text-muted">10:00 AM, 24/12/2024</small>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Thông báo 2: Cập nhật chính sách mới.</span>
                <small class="text-muted">11:30 AM, 24/12/2024</small>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Thông báo 3: Cuộc họp vào lúc 2:00 PM.</span>
                <small class="text-muted">12:00 PM, 24/12/2024</small>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Thông báo 4: Thay đổi giờ làm việc vào ngày lễ.</span>
                <small class="text-muted">01:00 PM, 24/12/2024</small>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Thông báo 5: Đào tạo nhân viên mới vào ngày mai.</span>
                <small class="text-muted">02:00 PM, 24/12/2024</small>
            </li>
        </ul>
    </div>
</div>
<!-- List new notification End -->

<!-- Available Rooms Count  -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <h6 class="mb-4">Số lượng phòng trống theo từng dãy</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Dãy A</h6>
                        <span class="badge bg-success" style="font-size: 1.5rem;">5 Phòng</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Dãy B</h6>
                        <span class="badge bg-success" style="font-size: 1.5rem;">3 Phòng</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Dãy C</h6>
                        <span class="badge bg-warning" style="font-size: 1.5rem;">2 Phòng</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Dãy D</h6>
                        <span class="badge bg-danger" style="font-size: 1.5rem;">0 Phòng</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <small class="text-muted">Tổng: <strong>10 phòng trống</strong></small>
        </div>
    </div>
</div>
<!-- Available Rooms Count End -->

<!-- Unpaid Invoices Start -->
<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0">Unpaid Invoices</h6>
            <a href="">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table text-start align-middle table-bordered table-hover mb-0">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">Invoice Date</th>
                        <th scope="col">Invoice Code</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Room</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Due Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>01/12/2024</td>
                        <td>INV-001</td>
                        <td>Nguyen Van A</td>
                        <td>P101</td>
                        <td>$500</td>
                        <td>15/12/2024</td>
                        <td><span class="badge bg-danger">Overdue</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Details</a></td>
                    </tr>
                    <tr>
                        <td>05/12/2024</td>
                        <td>INV-002</td>
                        <td>Tran Thi B</td>
                        <td>P202</td>
                        <td>$450</td>
                        <td>20/12/2024</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Details</a></td>
                    </tr>
                    <tr>
                        <td>10/12/2024</td>
                        <td>INV-003</td>
                        <td>Le Van C</td>
                        <td>P305</td>
                        <td>$600</td>
                        <td>25/12/2024</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td><a class="btn btn-sm btn-primary" href="">Details</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Unpaid Invoices End -->
@endsection
