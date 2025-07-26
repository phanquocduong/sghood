<!-- Navbar Start -->
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
    <a href="{{ route('dashboard') }}" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>
    <div class="navbar-nav align-items-center ms-auto">
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" id="messages-toggle">
                <div class="position-relative d-inline-block">
                    <i class="fa fa-message me-lg-2"></i>

                    @if ($unreadMessageCount > 0)
                        <span id="messages-badge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadMessageCount }}
                        </span>
                    @endif
                </div>

                <span class="d-none d-lg-inline-flex">Messages</span>
            </a>


            <div id="messages-dropdown"
                class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                @forelse ($latestMessages as $message)
                    <a href="{{ route('messages.index') }}"
                        class="dropdown-item {{ $message['is_read'] === false ? 'fw-bold' : '' }}">
                        <h6 class="mb-0">
                            {{ $message['text'] ?? '[Không có nội dung]' }}
                        </h6>
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($message['createdAt'])->diffForHumans() }}
                        </small>
                    </a>
                    <hr class="dropdown-divider">
                @empty
                    <div class="dropdown-item text-muted">Không có tin nhắn mới</div>
                @endforelse

                <a href="{{ route('messages.index') }}" class="dropdown-item text-center fw-bold text-primary">
                    Xem tất cả tin nhắn
                </a>
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



<script>
    function fetchHeaderData(type) {
        const route = type === 'messages'
            ? '{{ route('messages.header') }}'
            : '{{ route('notifications.header') }}';

        fetch(route)
            .then(res => res.json())
            .then(data => {
                // Badge update
                let countBadge = document.querySelector(`#${type}-badge`);
                const toggleBtn = document.getElementById(`${type}-toggle`) || document.querySelector(`[data-bs-toggle="dropdown"]`);

                // Nếu chưa có badge trong HTML thì tạo mới
                if (!countBadge) {
                    const badge = document.createElement('span');
                    badge.id = `${type}-badge`;
                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    badge.style.display = 'none';

                    // Gắn vào trong icon container (phải có position-relative)
                    if (toggleBtn?.querySelector('.position-relative')) {
                        toggleBtn.querySelector('.position-relative').appendChild(badge);
                        countBadge = badge;
                    }
                }

                // Cập nhật số badge
                if (data.unread_count > 0 && countBadge) {
                    countBadge.textContent = data.unread_count;
                    countBadge.style.display = 'inline-block';
                } else if (countBadge) {
                    countBadge.style.display = 'none';
                }

                // Dropdown update
                const dropdown = document.getElementById(`${type}-dropdown`);
                if (!dropdown) return;

                dropdown.innerHTML = '';

                if (data.latest.length > 0) {
                    data.latest.forEach((item, i) => {
                        dropdown.innerHTML += `
                            <a href="${item.url}" class="dropdown-item ${item.is_read === false || item.status === 'Chưa đọc' ? 'fw-bold' : ''}">
                                <h6 class="mb-0">${item.title || item.message}</h6>
                                <small class="text-muted">${item.created_at}</small>
                            </a>
                            ${i < data.latest.length - 1 ? '<hr class="dropdown-divider">' : ''}
                        `;
                    });
                } else {
                    dropdown.innerHTML += `
                        <div class="dropdown-item text-muted text-center">
                            Không có ${type === 'messages' ? 'tin nhắn' : 'thông báo'}
                        </div>`;
                }

                dropdown.innerHTML += `
                    <a href="${data.latest[0]?.url || '#'}" class="dropdown-item text-center fw-bold text-primary">
                        Xem tất cả ${type === 'messages' ? 'tin nhắn' : 'thông báo'}
                    </a>`;
            });
    }

    // Gọi khi trang load và mỗi 10s
    document.addEventListener('DOMContentLoaded', () => {
        fetchHeaderData('notifications');
        fetchHeaderData('messages');

        setInterval(() => {
            fetchHeaderData('notifications');
            fetchHeaderData('messages');
        }, 10000);
    });
</script>



<!-- Navbar End -->
