@if ($selectedUser)
    <div style="background: #007bff; color: white; padding: 10px;">
        Đang trò chuyện với: {{ $selectedUser->name }}
    </div>

    <div class="chat-box" style="flex: 1; overflow-y: auto; padding: 10px; background: #f8f9fa;" id="chatBox">
        @foreach($messages as $msg)
            <div style="text-align: {{ $msg['sender_id'] === auth()->id() ? 'right' : 'left' }}; margin: 5px 0;">
                <div style="display: inline-block; padding: 8px 12px; border-radius: 10px;
                            background: {{ $msg['sender_id'] === auth()->id() ? '#007bff' : '#e2e3e5' }};
                            color: {{ $msg['sender_id'] === auth()->id() ? '#fff' : '#000' }}; max-width: 60%;">

                    @if (!empty($msg['type']) && $msg['type'] === 'image' && !empty($msg['imageUrl']))
                        <img src="{{ $msg['imageUrl'] }}" alt="Hình ảnh" style="max-width: 200px; border-radius: 8px;" onerror="this.style.display='none'; console.error('Failed to load image:', '{{ $msg['imageUrl'] }}');">
                    @elseif (!empty($msg['message']))
                        {{ $msg['message'] }}
                    @endif

                </div>
            </div>
        @endforeach
    </div>

    <form id="sendMessageForm" style="display: flex; border-top: 1px solid #ccc; padding: 10px;" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $selectedUserId }}">
        <input type="text" name="message" id="messageInput" class="form-control me-2" placeholder="Nhập tin nhắn..." required>
        <input type="file" id="imageInput" name="image" accept="image/*" style="display: none;">
        <button type="button" class="btn btn-secondary" id="uploadImageBtn">Chọn ảnh</button>
        <button type="submit" class="btn btn-primary" id="sendBtn">Gửi</button>
    </form>
@else
    <div style="padding: 20px;">Chọn một người dùng để bắt đầu trò chuyện.</div>
@endif

<script>
    // Khởi tạo Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0",
        authDomain: "tro-viet.firebaseapp.com",
        projectId: "tro-viet",
        storageBucket: 'tro-viet.firebasestorage.app',
        messagingSenderId: '1000506063285',
        appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
        measurementId: 'G-LRB092W6Y5'
    };
    firebase.initializeApp(firebaseConfig);
    const storage = firebase.storage();

    // Xử lý chọn ảnh
    document.getElementById('uploadImageBtn').addEventListener('click', () => {
        document.getElementById('imageInput').click();
    });

    // Xử lý gửi tin nhắn
    document.getElementById('sendMessageForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const messageInput = document.getElementById('messageInput');
        const imageInput = document.getElementById('imageInput');
        const receiverId = formData.get('receiver_id');
        const message = messageInput.value.trim();
        let imageUrl = null;

        // Upload hình ảnh nếu có
        if (imageInput.files.length > 0) {
            const file = imageInput.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('File quá lớn! Vui lòng chọn hình ảnh dưới 5MB.');
                return;
            }

            const storageRef = storage.ref(`images/${Date.now()}_${file.name}`);
            await storageRef.put(file);
            imageUrl = await storageRef.getDownloadURL();
            console.log('Image uploaded with URL:', imageUrl);
        }

        // Gửi request tới backend
        const response = await fetch('/messages/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                receiver_id: receiverId,
                message: message || null,
                image: imageUrl || null,
            }),
        });

        const result = await response.json();
        if (result.status) {
            messageInput.value = '';
            imageInput.value = ''; // Xóa file đã chọn
            // Cập nhật chat box (có thể dùng WebSocket hoặc reload)
            location.reload(); // Reload tạm thời, thay bằng WebSocket nếu cần
        } else {
            alert(result.message);
        }
    });
</script>
