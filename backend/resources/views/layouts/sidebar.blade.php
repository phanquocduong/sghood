<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">
        <a href="{{ route('dashboard') }}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary"><i class="fa fa-home me-2"></i>SGHood</h3>
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="{{ Auth::user()->avatar ?? asset('img/user.jpg') }}" alt="" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <span>Quản trị viên</span>
            </div>
        </div>

        <div class="navbar-nav w-100">
            <!-- Quản lý tổng quan -->
            <a href="{{ route('dashboard') }}" class="nav-item nav-link {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}"><i class="fa fa-tachometer-alt me-2"></i>Tổng quan</a>
            <a href="{{ route('statistics') }}" class="nav-item nav-link {{ request()->is('/') || request()->is('statistics') ? 'active' : '' }}"><i class="fa fa-chart-line me-2"></i>Thống kê</a>
            <a href="{{ route('notifications.index') }}" class="nav-item nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="fa fa-bell me-2"></i>Thông báo</a>


            <!-- Quản lý đặt phòng & hợp đồng -->
            <div class="nav-item dropdown {{ request()->routeIs('schedules.*') || request()->routeIs('contracts.*') || request()->routeIs('bookings.*') ? 'show' : '' }}">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('schedules.*') || request()->routeIs('contracts.*') || request()->routeIs('bookings.*') ? 'active' : '' }}" data-bs-toggle="dropdown"><i class="fa fa-calendar-check me-2"></i>Đặt & Hợp đồng</a>
                <div class="dropdown-menu bg-transparent border-0 {{ request()->routeIs('schedules.*') || request()->routeIs('contracts.*') || request()->routeIs('bookings.*') ? 'show' : '' }}">
                    <a href="{{ route('schedules.index') }}" class="dropdown-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">Lịch xem phòng</a>
                    <a href="{{ route('bookings.index') }}" class="dropdown-item {{ request()->routeIs('bookings.*') ? 'active' : '' }}">Đặt phòng</a>
                    <a href="{{ route('contracts.index') }}" class="dropdown-item {{ request()->routeIs('contracts.*') ? 'active' : '' }}">Hợp đồng</a>
                </div>
            </div>

            <!-- Quản lý tài chính -->
            <div class="nav-item dropdown {{ request()->routeIs('invoices.*') || request()->routeIs('transactions.*') || request()->routeIs('meter_readings.*') ? 'show' : '' }}">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('invoices.*') || request()->routeIs('transactions.*') || request()->routeIs('meter_readings.*') ? 'active' : '' }}" data-bs-toggle="dropdown"><i class="fa fa-money-bill me-2"></i>Tài chính</a>
                <div class="dropdown-menu bg-transparent border-0 {{ request()->routeIs('invoices.*') || request()->routeIs('transactions.*') || request()->routeIs('meter_readings.*') ? 'show' : '' }}">
                    <a href="{{ route('meter_readings.index') }}" class="dropdown-item {{ request()->routeIs('meter_readings.*') ? 'active' : '' }}">Chỉ số điện nước</a>
                    <a href="{{ route('invoices.index') }}" class="dropdown-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">Hoá đơn</a>
                    <a href="{{ route('transactions.index') }}" class="dropdown-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">Lịch sử giao dịch</a>
                </div>
            </div>

            {{-- Quản lý nhà trọ --}}
            <a href="{{ route('motels.index') }}" class="nav-item nav-link {{ request()->routeIs('motels.*') || request()->routeIs('rooms.*') ? 'active' : '' }}"><i class="fa fa-building me-2"></i>Nhà trọ</a>

            <!-- Quản lý hệ thống -->
            <div class="nav-item dropdown {{ request()->routeIs('refunds.*') || request()->routeIs('checkouts.*') ? 'show' : '' }}">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('refunds.*') || request()->routeIs('checkouts.*') ? 'active' : '' }}" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Trả phòng</a>
                <div class="dropdown-menu bg-transparent border-0 {{ request()->routeIs('checkouts.*') || request()->routeIs('refunds.*') ? 'show' : '' }}">
                    <a href="{{ route('checkouts.index') }}" class="dropdown-item {{ request()->routeIs('checkouts.*') ? 'active' : '' }}">Yêu cầu trả phòng</a>
                    <a href="{{ route('refunds.index') }}" class="dropdown-item {{ request()->routeIs('refunds.*') ? 'active' : '' }}">Yêu cầu hoàn tiền</a>
                </div>
            </div>

            <!-- Quản lý vận hành -->
            <a href="{{ route('repair_requests.index') }}" class="nav-item nav-link {{ request()->routeIs('repair_requests.*') ? 'active' : '' }}"><i class="fa fa-tools me-2"></i>Bảo trì</a>

            <!-- Quản lý hệ thống -->
            <div class="nav-item dropdown {{ request()->routeIs('districts.*') || request()->routeIs('amenities.*') || request()->routeIs('configs.*') ? 'show' : '' }}">
                <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('districts.*') || request()->routeIs('amenities.*') || request()->routeIs('configs.*') ? 'active' : '' }}" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Hệ thống</a>
                <div class="dropdown-menu bg-transparent border-0 {{ request()->routeIs('districts.*') || request()->routeIs('amenities.*') || request()->routeIs('configs.*') ? 'show' : '' }}">
                    <a href="{{ route('configs.index') }}" class="dropdown-item {{ request()->routeIs('configs.*') ? 'active' : '' }}">Cấu hình</a>
                    <a href="{{ route('districts.index') }}" class="dropdown-item {{ request()->routeIs('districts.*') ? 'active' : '' }}">Khu vực</a>
                   <a href="{{ route('amenities.index') }}" class="dropdown-item {{ request()->routeIs('amenities.*') ? 'active' : '' }}">Tiện ích</a>
                </div>
            </div>

            <!-- Quản lý người dùng -->
            <a href="{{ route('users.user') }}" class="nav-item nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fa fa-users me-2"></i>Người dùng</a>
            <!-- Quản lý tin nhắn -->
            <a href="{{ route('messages.index') }}" class="nav-item nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="fa fa-envelope me-2"></i>
                Tin nhắn
            </a>
            <!-- Quản lý blog -->
            <a href="{{ route('blogs.index') }}" class="nav-item nav-link {{ request()->routeIs('blogs.*') ? 'active' : '' }}"><i class="fa fa-newspaper me-2"></i>Blog</a>
            {{-- <!-- Quản lý bình luận -->
            <a href="{{ route('comments.index') }}" class="nav-item nav-link {{ request()->routeIs('comments.*') ? 'active' : '' }}"><i class="fa fa-comments me-2"></i>Bình luận</a> --}}

        </div>
    </nav>
</div>
<!-- Sidebar End -->
