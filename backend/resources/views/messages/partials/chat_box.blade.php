@if ($selectedUser)
    <div style="background: #007bff; color: white; padding: 10px;">
        Đang trò chuyện với: {{ $selectedUser->name }}
    </div>

    <div class="chat-box" style="flex: 1; overflow-y: auto; padding: 10px; background: #f8f9fa;" id="chatBox">
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

    <form id="sendMessageForm" style="display: flex; border-top: 1px solid #ccc; padding: 10px;">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $selectedUserId }}">
        <input type="text" name="message" id="messageInput" class="form-control me-2" placeholder="Nhập tin nhắn..." required>
        <button type="submit" class="btn btn-primary" id="sendBtn">Gửi</button>
    </form>
@else
    <div style="padding: 20px;">Chọn một người dùng để bắt đầu trò chuyện.</div>
@endif

