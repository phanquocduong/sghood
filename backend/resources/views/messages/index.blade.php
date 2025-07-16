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
    <!-- Danh sách người dùng -->
    <div style="width: 25%; border-right: 1px solid #ccc; overflow-y: auto;">
        @foreach($users as $user)
            <div style="padding: 10px; border-bottom: 1px solid #eee;">
                <a href="#" class="load-chat"
                data-user-id="{{ $user->id }}"
                style="text-decoration: none; color: inherit;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        {{-- Avatar --}}
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #ccc;"></div>

                        {{-- Tên + badge --}}
                        <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 500; color: {{ $selectedUserId == $user->id ? '#007bff' : '#000' }}">
                                {{ $user->name }}
                            </span>

                            @if($user->unread_count > 0)
                                <span class="unread-badge"
                                    style="background-color: red;
                                            color: white;
                                            font-size: 12px;
                                            border-radius: 12px;
                                            padding: 2px 6px;
                                            min-width: 20px;
                                            display: inline-block;
                                            margin-left: 8px;"
                                    data-user-id="{{ $user->id }}">
                                    {{ $user->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>

            </div>

        @endforeach
</div>

    <!-- Khung chat -->
    <div style="flex: 1; display: flex; flex-direction: column;" id="chatContainer">
       @include('messages.partials.chat_box', ['selectedUser' => $selectedUser, 'messages' => $messages, 'selectedUserId' => $selectedUserId])
    </div>
</div>
@endsection
@section('scripts')
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>
<script>
    firebase.initializeApp({
        apiKey: "AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0",
        authDomain: "tro-viet.firebaseapp.com",
        projectId: "tro-viet",
    });
</script>

<script type="module">
    import { initChat } from '/js/chat.js';
    initChat({{ auth()->id() }});
    listenUnreadMessages();
</script>
<script>
    window.routes = {
        messageIndex: '{{ route("messages.index") }}',
    };
</script>
@endsection
