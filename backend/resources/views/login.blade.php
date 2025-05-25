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
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form action="{{ route('login') }}" method="POST">
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
@endsection
