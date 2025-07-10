@extends('layouts.auth')

@section('content')
<div class="container-fluid">
    <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh">
        <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
            <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i>SGHood</h3>
                    <h3>Đăng nhập</h3>
                </div>
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" id="floatingInput" value="{{ old('username') }}" required placeholder="+84.../abc@gmail.com" />
                        <label for="floatingInput">SĐT hoặc Email</label>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" name="password" class="form-control" id="floatingPassword" required placeholder="Mật khẩu" />
                        <label for="floatingPassword">Mật khẩu</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember" {{ old('remember') ? 'checked' : '' }} />
                            <label class="form-check-label" for="rememberMe">Ghi nhớ tôi</label>
                        </div>
                    </div>
                    <button class="btn btn-primary py-3 w-100 mb-4">Đăng nhập</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
<script>
    // Firebase configuration
    const firebaseConfig = {
        apiKey: 'AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0',
        authDomain: 'tro-viet.firebaseapp.com',
        projectId: 'tro-viet',
        storageBucket: 'tro-viet.firebasestorage.app',
        messagingSenderId: '1000506063285',
        appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
        measurementId: 'G-LRB092W6Y5'
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    // Hàm lấy FCM token
    async function getFcmToken() {
        if (!messaging || !('PushManager' in window)) return null;
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return null;
        const token = await messaging.getToken({
            vapidKey: 'BIwo8BokWVVEkQusRhenQkeVXDESe5Hfev8clWdC4BAcN1Onj6Ic2W6WOyFBrQKMMHIHQI2lloDVsn2F6lxOyxo'
        });
        return token || null;
    }

    // Hàm làm mới CSRF token
    async function refreshCsrfToken() {
        const response = await fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        document.querySelector('meta[name="csrf-token"]').content = result.csrf_token;
        return result.csrf_token;
    }

    // Hàm gửi FCM token lên server
    async function saveFcmToken(token) {
        const csrfToken = await refreshCsrfToken();
        if (!csrfToken) return false;

        try {
            const response = await fetch('{{ route("save-fcm-token") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ fcm_token: token })
            });
            const result = await response.json();
            return result.success;
        } catch (error) {
            return false;
        }
    }

    // Xử lý form submit
    document.addEventListener('DOMContentLoaded', function () {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) return;

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            // Xóa các thông báo cũ trước khi submit
            const alerts = loginForm.querySelectorAll('.alert');
            alerts.forEach(alert => alert.remove());

            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const token = await getFcmToken();
                    if (token) {
                        await saveFcmToken(token);
                    }
                    window.location.href = '{{ route("dashboard") }}';
                } else {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    errorDiv.textContent = result.message;
                    loginForm.prepend(errorDiv);
                }
            } catch (error) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                loginForm.prepend(errorDiv);
            }
        });
    });
</script>
@endpush
@endsection
