<!-- Navbar Start -->
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-3 py-0">
    <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>
    <div class="navbar-nav align-items-center ms-auto">
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa fa-message me-lg-2 position-relative">
                    <span id="messages-badge"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unreadMessageCount }}
                    </span>
                </i>
                <span class="d-none d-lg-inline-flex">Messages</span>
            </a>
            <div id="messages-dropdown"
                class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                {{-- Mặc định render từ blade, nhưng JS sẽ update lại --}}
                {{-- @foreach ($latestMessages as $message)
                    <a href="{{ route('messages.index') }}"
                        class="dropdown-item {{ !empty($message['is_read']) && $message['is_read'] == false ? 'fw-bold' : '' }}">
                        <h6 class="mb-0">{{ $message['message'] }}</h6>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($message['created_at'])->diffForHumans() }}
                        </small>
                    </a>
                    <hr class="dropdown-divider">
                @endforeach --}}
                <a href="{{ route('messages.index') }}" class="dropdown-item text-center fw-bold text-primary">Xem tất cả tin nhắn</a>
            </div>
        </div>

        <div class="navbar-nav align-items-center ms-auto">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-bell me-lg-2 position-relative">
                        <span id="notifications-badge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadCount }}
                        </span>
                    </i>
                    <span class="d-none d-lg-inline-flex">Thông báo</span>
                </a>

                <div id="notifications-dropdown"
                    class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                    {{-- Mặc định render từ blade, nhưng JS sẽ update lại --}}
                    {{-- @foreach ($latestNotifications as $notification)
                        <a href="{{ route('notifications.index') }}"
                            class="dropdown-item {{ $notification->status == 'Chưa đọc' ? 'fw-bold' : '' }}">
                            <h6 class="mb-0">{{ $notification->title }}</h6>
                            <small
                                class="text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                        </a>
                        <hr class="dropdown-divider">
                    @endforeach --}}
                    <a href="{{ route('notifications.index') }}"
                        class="dropdown-item text-center fw-bold text-primary">Xem
                        tất cả thông báo</a>
                </div>
            </div>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img class="rounded-circle me-lg-2" src="{{ Auth::user()->avatar ?? asset('img/user.jpg') }}"
                        alt="Avatar" style="width: 40px; height: 40px;">
                    <span class="d-none d-lg-inline-flex">{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
</nav>
