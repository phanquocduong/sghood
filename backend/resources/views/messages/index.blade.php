@extends('layouts.app')
@section('title', 'Trang tin nhắn')
@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div style="display: flex; height: 80vh;">
    <!-- Danh sách khách hàng -->
    <div style="width: 25%; border-right: 1px solid #ccc;">
        @foreach($users as $user)
            <div style="padding: 10px; border-bottom: 1px solid #eee;">
                <a href="{{ route('messages.index', ['user_id' => $user->id]) }}">
                    {{ $user->name }}
                </a>
            </div>
        @endforeach
    </div>

    <!-- Khung chat -->
    <div style="flex: 1; display: flex; flex-direction: column;">
        @if ($selectedUser)
            <div style="background: #007bff; color: white; padding: 10px;">
                Đang chat với: {{ $selectedUser->name }}
            </div>

            <div style="flex: 1; overflow-y: auto; padding: 10px; background: #f8f9fa;">
                @foreach($messages as $msg)
                    <div style="text-align: {{ $msg->sender_id === auth()->id() ? 'right' : 'left' }}; margin: 5px 0;">
                        <span style="display: inline-block; padding: 8px 12px; border-radius: 10px;
                                     background: {{ $msg->sender_id === auth()->id() ? '#007bff' : '#e2e3e5' }};
                                     color: {{ $msg->sender_id === auth()->id() ? '#fff' : '#000' }}">
                            {{ $msg->message }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Form gửi tin nhắn -->
            <form method="POST" action="{{ route('messages.send') }}" style="display: flex; border-top: 1px solid #ccc;">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $selectedUserId }}">
                <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                <button type="submit" class="btn btn-primary">Gửi</button>
            </form>
        @else
            <div style="padding: 20px;">Chọn một khách hàng để bắt đầu trò chuyện.</div>
        @endif
    </div>
</div>
@endsection


