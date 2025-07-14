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

    const chatBox = document.querySelector('.chat-box');
    if (chatBox) chatBox.innerHTML = '';

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
        const imageInput = document.getElementById('imageInput');
        const message = messageInput.value.trim();
        const imageFile = imageInput.files[0];

        if (!message && !imageFile) return;

        let imageUrl = null;

        // Upload ảnh lên Firebase Storage nếu có
        if (imageFile) {
            const storageRef = firebase.storage().ref(`images/${Date.now()}_${imageFile.name}`);
            await storageRef.put(imageFile);
            imageUrl = await storageRef.getDownloadURL();
            console.log('Image uploaded to Storage with URL:', imageUrl);
        }

        // Gửi dữ liệu lên Firestore
        const db = firebase.firestore();
        const messageData = {
            chatId: chatKey,
            sender_id: currentUserId,
            receiver_id: chatUserId,
            text: message || '', // Văn bản có thể rỗng nếu chỉ gửi hình
            content: imageUrl || message, // Sử dụng content cho imageUrl hoặc text
            type: imageUrl ? 'image' : 'text',
            is_read: false,
            createdAt: firebase.firestore.FieldValue.serverTimestamp()
        };

        await db.collection('messages').add(messageData);

        // Reset form
        messageInput.value = '';
        imageInput.value = '';
    });
}

function listenToFirestore() {
    if (unsubscribe) unsubscribe();

    const db = firebase.firestore();
    const chatQuery = db.collection('messages')
        .where('chatId', '==', chatKey)
        .orderBy('createdAt', 'asc');

    unsubscribe = chatQuery.onSnapshot(snapshot => {
        snapshot.docChanges().forEach(change => {
            if (change.type === 'added') {
                const msg = change.doc.data();
                const msgId = change.doc.id;
                const chatBox = document.querySelector('.chat-box');

                // Tránh nhân đôi
                if (chatBox.querySelector(`[data-msg-id="${msgId}"]`)) return;

                console.log(`Message ${msgId} data:`, msg); // Debug toàn bộ dữ liệu

                // Căn chỉnh và màu sắc
                const align = msg.sender_id === currentUserId ? 'right' : 'left';
                const bg = msg.sender_id === currentUserId ? '#6c63ff' : '#e2e3e5';
                const color = msg.sender_id === currentUserId ? '#fff' : '#000';

                // Tạo HTML cho tin nhắn
                let msgHtml = `
                    <div style="text-align: ${align}; margin: 5px 0;">
                        <div data-msg-id="${msgId}" style="display: inline-block; max-width: 60%;">
                `;

                // Thêm văn bản nếu có
                if (msg.text) {
                    msgHtml += `
                        <span style="background: ${bg}; color: ${color}; padding: 8px 12px; border-radius: 10px; display: inline-block; word-break: break-word;">
                            ${msg.text}
                        </span>
                    `;
                }

                // Thêm hình ảnh nếu có
                if (msg.type === 'image' && msg.content) {
                    msgHtml += `
                        <img src="${msg.content}" style="max-width: 100%; border-radius: 10px; margin-top: 5px;" alt="Hình ảnh">
                    `;
                }

                msgHtml += `</div></div>`;
                chatBox.innerHTML += msgHtml;
                chatBox.scrollTop = chatBox.scrollHeight;
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

