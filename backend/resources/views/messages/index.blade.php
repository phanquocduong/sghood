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
                <a href="{{ route('messages.index', ['user_id' => $user->id]) }}"
                   style="text-decoration: none; color: {{ $selectedUserId == $user->id ? '#007bff' : '#000' }};">
                    {{ $user->name }}
                </a>
            </div>
        @endforeach
    </div>

    <!-- Khung chat -->
    <div style="flex: 1; display: flex; flex-direction: column;">
        @if ($selectedUser)
            <div style="background: #007bff; color: white; padding: 10px;">
                Đang trò chuyện với: {{ $selectedUser->name }}
            </div>

            <div class="chat-box" style="flex: 1; overflow-y: auto; padding: 10px; background: #f8f9fa;">
                @foreach($messages as $msg)
                    <div style="text-align: {{ $msg->sender_id === auth()->id() ? 'right' : 'left' }}; margin: 5px 0;">
                        <span style="display: inline-block; padding: 8px 12px; border-radius: 10px;
                                     background: {{ $msg->sender_id === auth()->id() ? '#007bff' : '#e2e3e5' }};
                                     color: {{ $msg->sender_id === auth()->id() ? '#fff' : '#000' }};">
                            {{ $msg->message }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Form gửi tin nhắn -->
            <form id="sendMessageForm" style="display: flex; border-top: 1px solid #ccc; padding: 10px;">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $selectedUserId }}">
                <input type="text" name="message" id="messageInput" class="form-control me-2" placeholder="Nhập tin nhắn..." required>
                <button type="submit" class="btn btn-primary" id="sendBtn">Gửi</button>
            </form>
        @else
            <div style="padding: 20px;">Chọn một người dùng để bắt đầu trò chuyện.</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Firebase compat (dùng kiểu v8 nhưng tương thích v9) -->
<!-- Firebase SDK dùng phiên bản compat để hoạt động với script thường -->
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-database.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-firestore.js"></script>


<script>
    const firebaseConfig = {
        apiKey: "AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0",
        authDomain: "tro-viet.firebaseapp.com",
        databaseURL: "https://tro-viet-default-rtdb.firebaseio.com",
        projectId: "tro-viet",
        storageBucket: "tro-viet.appspot.com",
        messagingSenderId: "1000506063285",
        appId: "1:1000506063285:web:47e80b8489d09c8ce8c1fc",
    };
    firebase.initializeApp(firebaseConfig);
    console.log("✅ Firebase đã khởi tạo thành công");
</script>

<script>
    const currentUserId = {{ auth()->id() }};
    const chatUserId = {{ $selectedUserId }};
    const chatKey = currentUserId < chatUserId
        ? `${currentUserId}_${chatUserId}`
        : `${chatUserId}_${currentUserId}`;

    console.log("👤 currentUserId:", currentUserId);
    console.log("💬 chatUserId:", chatUserId);
    console.log("📦 Chat key đang lắng:", chatKey);

    const db = firebase.firestore();
const chatQuery = db.collection('messages')
  .where('chatId', '==', chatKey)
  .orderBy('createdAt', 'asc');

chatQuery.onSnapshot(snapshot => {
  snapshot.docChanges().forEach(change => {
    if (change.type === 'added') {
      const msg = change.doc.data();
      if (msg.sender_id !== currentUserId) {
        const chatBox = document.querySelector('.chat-box');
        const msgHtml = `
          <div style="text-align: left; margin: 5px 0;">
              <span style="background: #e2e3e5; color: #000; padding: 8px 12px; border-radius: 10px;">
                  ${msg.text}
              </span>
          </div>
        `;
        chatBox.innerHTML += msgHtml;
        chatBox.scrollTop = chatBox.scrollHeight;
      }
    }
  });
});
</script>
<script>
document.getElementById('sendMessageForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    if (!message) return;

    const formData = new FormData(this);

    // Gửi API lưu DB
    const response = await fetch("{{ route('messages.send') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    });

    const result = await response.json();
    if (result.status) {
        // Gửi Firebase (Firestore)
        const db = firebase.firestore();
        await db.collection('messages').add({
            chatId: chatKey,
            sender_id: currentUserId,
            receiver_id: chatUserId,
            text: message,
            createdAt: firebase.firestore.FieldValue.serverTimestamp()
        });

        // Append ra UI ngay lập tức
        const chatBox = document.querySelector('.chat-box');
        const msgHtml = `
          <div style="text-align: right; margin: 5px 0;">
              <span style="background: #007bff; color: #fff; padding: 8px 12px; border-radius: 10px;">
                  ${message}
              </span>
          </div>
        `;
        chatBox.innerHTML += msgHtml;
        chatBox.scrollTop = chatBox.scrollHeight;

        // Xoá input
        messageInput.value = '';
    } else {
        alert("Gửi tin nhắn thất bại");
    }
});
</script>

@endsection
