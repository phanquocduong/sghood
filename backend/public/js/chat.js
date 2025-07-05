let currentUserId = null;
let chatUserId = null;
let chatKey = null;
let unsubscribe = null; // dùng để huỷ listener Firebase cũ

export function initChat(_currentUserId) {
    currentUserId = _currentUserId;

    $(document).on('click', '.load-chat', function (e) {
        e.preventDefault();
        const userId = $(this).data('user-id');

        $.ajax({
            url: '/messages/chat-box', // route này cần có
            method: 'GET',
            data: { user_id: userId },
            success: function (response) {
                $('#chatContainer').html(response);
                setTimeout(() => {
                setupChatRealtime(); // ✅ đảm bảo DOM đã render xong input[receiver_id]
            }, 0);
            },
            error: function () {
                alert('Không thể tải nội dung chat');
            }
        });
    });
    listenUnreadMessages();
    setupChatRealtime();
}

function setupChatRealtime() {
    const receiverInput = document.querySelector('input[name="receiver_id"]');
    if (!receiverInput) return;

    chatUserId = parseInt(receiverInput.value);
    chatKey = currentUserId < chatUserId
        ? `${currentUserId}_${chatUserId}`
        : `${chatUserId}_${currentUserId}`;

    bindSendMessageForm();
    listenToFirestore();
    markMessagesAsRead();
}

function bindSendMessageForm() {
    const form = document.getElementById('sendMessageForm');
    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        if (!message) return;

        const formData = new FormData(form);

        // Gửi API về server
        const response = await fetch("/messages/send", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        });

        const result = await response.json();
        if (result.status) {
            // Push Firestore
            const db = firebase.firestore();
            await db.collection('messages').add({
                chatId: chatKey,
                sender_id: currentUserId,
                receiver_id: chatUserId,
                text: message,
                is_read: false,
                createdAt: firebase.firestore.FieldValue.serverTimestamp()
            });

            // Thêm UI ngay
            const chatBox = document.querySelector('.chat-box');
            const msgHtml = `
                <div style="text-align: right; margin: 5px 0;">
                    <span style="display:inline-block;padding:10px 14px;border-radius:10px;background:#007bff;color:#fff;max-width:60%;word-break:break-word;">
                        ${message}
                    </span>
                </div>`;
            chatBox.innerHTML += msgHtml;
            chatBox.scrollTop = chatBox.scrollHeight;
            messageInput.value = '';
        } else {
            alert("Gửi tin nhắn thất bại");
        }
    });
}

function listenToFirestore() {
    // ✅ Huỷ listener cũ nếu có
    if (unsubscribe) unsubscribe();

    const db = firebase.firestore();
    const chatQuery = db.collection('messages')
        .where('chatId', '==', chatKey)
        .orderBy('createdAt', 'asc');

    unsubscribe = chatQuery.onSnapshot(snapshot => {
        snapshot.docChanges().forEach(change => {
            if (change.type === 'added') {
                const msg = change.doc.data();
                if (msg.sender_id !== currentUserId) {
                    const chatBox = document.querySelector('.chat-box');

                    // ⚠️ Tránh nhân đôi
                    const exists = [...chatBox.querySelectorAll('span')]
                        .some(el => el.textContent === msg.text);
                    if (exists) return;

                    const msgHtml = `
                        <div style="text-align: left; margin: 5px 0;">
                            <span style="background: #e2e3e5; color: #000; padding: 8px 12px; border-radius: 10px; display: inline-block;">
                                ${msg.text}
                            </span>
                        </div>`;
                    chatBox.innerHTML += msgHtml;
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }
        });
    });
}


function reloadUserList() {
    $.get(window.routes.messageIndex, function (responseHtml) {
        const temp = document.createElement('div');
        temp.innerHTML = responseHtml;

        const newUserList = temp.querySelector('div[style*="width: 25%"]');
        if (newUserList) {
            $('div[style*="width: 25%"]').replaceWith(newUserList);
        }
    });
}


function listenUnreadMessages() {
    const db = firebase.firestore();

    db.collection('messages')
        .where('receiver_id', '==', currentUserId)
        .where('is_read', '==', false)
        .onSnapshot(snapshot => {
            const unreadCountMap = {};

            snapshot.forEach(doc => {
                const data = doc.data();
                const senderId = data.sender_id;
                unreadCountMap[senderId] = (unreadCountMap[senderId] || 0) + 1;
            });

            // Cập nhật badge từng user
            document.querySelectorAll('.unread-badge').forEach(badge => {
                const userId = parseInt(badge.dataset.userId);
                const count = unreadCountMap[userId] || 0;

                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            });

            // Gắn badge nếu chưa có
            document.querySelectorAll('.load-chat').forEach(link => {
                const userId = parseInt(link.dataset.userId);
                const count = unreadCountMap[userId] || 0;

                const container = link.querySelector('div[style*="justify-content: space-between"]');
                if (count > 0 && !container.querySelector('.unread-badge')) {
                    const badge = document.createElement('span');
                    badge.className = 'unread-badge';
                    badge.dataset.userId = userId;
                    badge.textContent = count;
                    badge.style.cssText = `
                        background-color: red;
                        color: white;
                        font-size: 12px;
                        border-radius: 12px;
                        padding: 2px 6px;
                        min-width: 20px;
                        display: inline-block;
                        margin-left: 8px;
                    `;
                    container.appendChild(badge);
                }
            });

            // ✅ Cập nhật tổng số unread trên navbar
            const totalUnread = Object.values(unreadCountMap).reduce((sum, count) => sum + count, 0);
            const navbarBadge = document.getElementById('navbarUnreadTotal');
            if (navbarBadge) {
                if (totalUnread > 0) {
                    navbarBadge.textContent = totalUnread;
                    navbarBadge.style.display = 'inline-block';
                } else {
                    navbarBadge.style.display = 'none';
                }
            }
        });
}


function markMessagesAsRead() {
    const db = firebase.firestore();

    db.collection('messages')
        .where('chatId', '==', chatKey)
        .where('receiver_id', '==', currentUserId)
        .where('is_read', '==', false)
        .get()
        .then(querySnapshot => {
            const batch = db.batch();
            querySnapshot.forEach(doc => {
                batch.update(doc.ref, { is_read: true });
            });
            return batch.commit();
        })
        .then(() => {
            reloadUserList(); // ✅ cập nhật lại badge
            listenUnreadMessages(); // ✅ đảm bảo UI được cập nhật

            // 🔥 Gọi API Laravel để update DB MySQL
            fetch('/messages/mark-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: chatUserId })
            });
        });
}






