<template>
    <ClientOnly>
        <!-- Chỉ hiển thị khung chat khi isOpen = true -->
        <div v-show="isOpen" class="chat-box">
            <!-- Hiển thị loading khi đang tải dữ liệu -->
            <Loading :is-loading="isLoading" />

            <!-- Nội dung chat chỉ hiển thị khi không ở trạng thái loading -->
            <template v-if="!isLoading">
                <!-- Âm thanh thông báo khi có tin nhắn mới -->
                <audio ref="notiSound" src="/sounds/notification.mp3" preload="auto"></audio>

                <!-- Phần header của khung chat -->
                <div class="chat-header">
                    <span class="chat-title">Hỗ trợ người dùng</span>
                    <!-- Nút đóng khung chat, emit sự kiện 'close' khi click -->
                    <button class="chat-close" @click="$emit('close')">✕</button>
                </div>

                <!-- Phần thân khung chat, hiển thị danh sách tin nhắn -->
                <div class="chat-body">
                    <div class="chat-messages" ref="messageContainer">
                        <!-- Lặp qua danh sách tin nhắn để hiển thị -->
                        <div
                            v-for="(msg, index) in messages"
                            :key="index"
                            :class="['chat-message-wrapper', msg.from === 'user' ? 'align-right' : 'align-left']"
                        >
                            <!-- Hiển thị avatar của người gửi (người dùng hoặc admin) -->
                            <img :src="msg.from === 'user' ? avatarUrl : '/images/sghood_logo1.png'" alt="avatar" class="chat-avatar" />

                            <!-- Hiển thị nội dung tin nhắn -->
                            <div :class="['chat-message', msg.from === 'user' ? 'from-user' : 'from-admin']">
                                <!-- Nếu tin nhắn là hình ảnh -->
                                <template v-if="msg.type === 'image'">
                                    <img :src="msg.content" class="chat-image" />
                                </template>
                                <!-- Nếu tin nhắn là văn bản -->
                                <template v-else>
                                    <span class="chat-text">{{ msg.text }}</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phần gợi ý câu hỏi -->
                <div v-if="rawAction.length > 0" class="chat-suggestions">
                    <ul class="suggestion-list">
                        <!-- Lặp qua danh sách gợi ý câu hỏi -->
                        <li v-for="(hint, index) in rawAction" :key="index" class="suggestion-item" @click="handleClick(hint, index)">
                            {{ hint }}
                        </li>
                    </ul>
                </div>

                <!-- Phần nhập tin nhắn -->
                <div class="chat-input">
                    <!-- Ô nhập văn bản tin nhắn, gửi khi nhấn Enter -->
                    <input type="text" v-model="newMessage" @keyup.enter="sendMessage" placeholder="Nhập tin nhắn..." />
                    <!-- Nút chọn file để gửi hình ảnh -->
                    <div class="dropzone-button" @click="selectFile">
                        <i class="fa fas fa-camera"></i>
                        <!-- Input ẩn để chọn file hình ảnh -->
                        <input ref="fileInput" type="file" accept="image/*" style="display: none" @change="handleFileUpload" />
                    </div>

                    <!-- Nút gửi tin nhắn -->
                    <button @click="sendMessage()">Gửi</button>
                </div>
            </template>
        </div>
    </ClientOnly>
</template>

<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue';
import { useAuthStore } from '~/stores/auth';
import { useCookie } from '#app';
import { collection, query, where, orderBy, onSnapshot, addDoc, serverTimestamp } from 'firebase/firestore';
import { useBehaviorStore } from '~/stores/behavior';
import { uploadImageToFirebase } from '~/utils/uploadImage';
import { useAppToast } from '~/composables/useToast';

// Khởi tạo các biến và hàm từ Nuxt
const { $firebaseStorage } = useNuxtApp();
const emit = defineEmits(['close', 'unread']); // Định nghĩa các sự kiện emit
const authStore = useAuthStore(); // Store quản lý thông tin người dùng
const { user } = storeToRefs(authStore); // Lấy thông tin người dùng từ store
const toast = useAppToast(); // Hàm hiển thị thông báo toast
const currentUserId = ref(authStore.user?.id || null); // ID người dùng hiện tại
const token = ref(authStore.token || ''); // Token xác thực
const behavior = useBehaviorStore(); // Store quản lý hành vi người dùng
const { $api, $firebaseDb } = useNuxtApp(); // API và Firestore từ Nuxt
const newMessage = ref(behavior.chat || ''); // Nội dung tin nhắn mới
const messages = ref([]); // Danh sách tin nhắn
const notiSound = ref(null); // Ref cho âm thanh thông báo
const AdminId = ref(null); // ID của admin được gán
const messageContainer = ref(null); // Ref cho container tin nhắn
let unsubscribe = null; // Biến lưu hàm unsubscribe của Firestore listener
const config = useRuntimeConfig(); // Cấu hình runtime của Nuxt
const isLoading = ref(false); // Trạng thái loading
const rawAction = ref([]); // Danh sách gợi ý câu hỏi
const lastRealtime = ref(Date.now()); // Thời gian cập nhật tin nhắn realtime
const fileInput = ref(null); // Ref cho input file
const MAX_SIZE_MB = 2; // Dung lượng tối đa của file (MB)
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024; // Dung lượng tối đa (bytes)
const props = defineProps({
    isOpen: Boolean // Prop kiểm soát trạng thái mở/đóng khung chat
});

// Hàm mở input chọn file
const selectFile = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};

// Hàm xử lý khi người dùng chọn file để gửi
const handleFileUpload = async e => {
    const file = e.target.files[0];
    if (!file) return;

    // Kiểm tra file có hợp lệ không
    if (!file || file.size === undefined) {
        toast.error('File không hợp lệ');
        return;
    }

    // Kiểm tra định dạng file (chỉ chấp nhận hình ảnh)
    if (!file.type.startsWith('image/')) {
        toast.error('Chỉ được gửi ảnh');
        return;
    }

    // Kiểm tra kích thước file
    if (file.size > MAX_SIZE_BYTES) {
        toast.error(`Chỉ gửi được ảnh dưới ${MAX_SIZE_MB}MB`);
        return;
    }

    try {
        // Tải ảnh lên Firebase Storage và lấy URL
        const imageUrl = await uploadImageToFirebase(file, $firebaseStorage);

        // Gửi tin nhắn chứa URL ảnh
        await sendMessage({
            content: imageUrl,
            type: 'image'
        });
    } catch (err) {
        console.error('Upload image error:', err);
        toast.error('Gửi ảnh thất bại');
    }
};

// Theo dõi trạng thái mở/đóng khung chat
watch(
    () => props.isOpen,
    open => {
        if (open) {
            markMessagesAsRead(); // Đánh dấu tin nhắn đã đọc khi mở khung chat
        }
    }
);

// Key lưu trữ gợi ý đã sử dụng
const local_hint_key = computed(() => `usedHints_${currentUserId.value}`);

// Lấy danh sách gợi ý đã sử dụng từ localStorage
const getUserHint = () => {
    const raw = localStorage.getItem(local_hint_key.value);
    try {
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        return [];
    }
};

// Lưu gợi ý đã sử dụng vào localStorage
const saveUserHint = hint => {
    const used = getUserHint();
    if (!used.includes(hint)) {
        used.push(hint);
        localStorage.setItem(local_hint_key.value, JSON.stringify(used));
    }
};

const route = useRoute();
// Theo dõi thay đổi nội dung tin nhắn để lưu vào behavior store
watch(newMessage, val => {
    behavior.updateChat(val);
});

// Khởi tạo danh sách gợi ý câu hỏi dựa trên đường dẫn hiện tại
const initActions = () => {
    const configs = useState('configs')?.value;
    let questionMap = {};

    try {
        const rawMap = configs?.question_map;
        questionMap = typeof rawMap === 'string' ? JSON.parse(rawMap) : rawMap || {};
    } catch (err) {
        console.error('Lỗi parse question_map:', err);
        questionMap = {};
    }

    const path = route.path;
    const segments = path.split('/').filter(Boolean);
    const matchedKey = Object.keys(questionMap).find(key => segments.includes(key));
    const origin = matchedKey ? questionMap[matchedKey] : questionMap['default'] || [];
    const usedHints = getUserHint();

    // Lọc các gợi ý chưa được sử dụng
    const filtered = origin.filter(hint => !usedHints.includes(hint));
    rawAction.value = filtered;
};

// Xử lý khi người dùng click vào gợi ý câu hỏi
const handleClick = (text, index) => {
    sendMessage({ type: 'text', content: text });
    saveUserHint(text);
    rawAction.value.splice(index, 1); // Xóa gợi ý đã chọn
};

// Hàm gửi tin nhắn từ gợi ý
const send = text => {
    sendMessage(text);
};

// Tính toán URL avatar của người dùng
const avatarUrl = computed(() => (user.value?.avatar ? config.public.baseUrl + user.value.avatar : '/images/default-avatar.webp'));

// Lấy trang cuối cùng người dùng đã truy cập
const lastVisitedPage = computed(() => {
    return behavior.visitedPages.at(-1) || '/';
});

// Cuộn khung chat xuống dưới cùng
const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

// Khởi tạo khung chat
const initChat = async () => {
    isLoading.value = true;

    try {
        // Gọi API để bắt đầu phiên chat và lấy admin ID
        const res = await $api('/messages/start-chat', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token.value}`,
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                message: 'Xin chào, tôi cần hỗ trợ'
            }
        });

        if (!res?.status || !res?.admin_id) {
            console.warn('Không lấy được admin từ phản hồi start-chat');
            return;
        }

        AdminId.value = res.admin_id;
        scrollToBottom();

        // Lắng nghe tin nhắn mới từ Firestore
        const chatId = `${AdminId.value}_${currentUserId.value}`;
        const msgQuery = query(collection($firebaseDb, 'messages'), where('chatId', '==', chatId), orderBy('createdAt', 'asc'));

        unsubscribe = onSnapshot(msgQuery, snapshot => {
            const changes = snapshot.docChanges().filter(change => change.type === 'added');

            const newMessages = changes.map(change => {
                const d = change.doc.data();
                return {
                    id: change.doc.id,
                    from: d.sender_id === currentUserId.value ? 'user' : 'admin',
                    text: d.text,
                    type: d.type || 'text',
                    content: d.content || '',
                    createdAt: d.createdAt?.toMillis?.() || Date.now()
                };
            });

            if (newMessages.length > 0) {
                const isDuplicate = (msg, list) => list.some(m => m.id === msg.id);
                const newUniqueMessages = newMessages.filter(m => !isDuplicate(m, messages.value));

                messages.value = [...messages.value, ...newUniqueMessages];
                scrollToBottom();
                const hasAdmin = newUniqueMessages.some(m => {
                    return m.from === 'admin' && m.createdAt > lastRealtime.value;
                });

                if (hasAdmin) {
                    lastRealtime.value = Date.now();
                    localStorage.setItem('lastRealtime', lastRealtime.value.toString());

                    emit('unread'); // Phát sự kiện khi có tin nhắn chưa đọc
                    const audio = notiSound.value;
                    if (audio) {
                        audio.pause();
                        audio.currentTime = 0;
                        audio.play().catch(err => {
                            console.warn('Không thể phát âm thanh', err);
                        });
                    }
                }
            }
        });
    } catch (error) {
        console.error('initChat error:', error);
    } finally {
        isLoading.value = false;
    }
};

// Hàm gửi tin nhắn
const sendMessage = async (payload = null) => {
    const type = payload?.type || 'text';
    const Rawtext = payload?.content || newMessage.value;
    const text = String(Rawtext).trim();
    if (!text || !AdminId.value) return;

    try {
        const chatId = `${AdminId.value}_${currentUserId.value}`;

        scrollToBottom();
        newMessage.value = '';

        // Gửi tin nhắn lên Firestore
        await addDoc(collection($firebaseDb, 'messages'), {
            text: type === 'image' ? '' : text,
            content: type === 'image' ? Rawtext : '',
            type,
            sender_id: currentUserId.value,
            receiver_id: AdminId.value,
            createdAt: serverTimestamp(),
            is_read: false,
            chatId
        });

        if (type === 'text') behavior.clearChat(); // Xóa nội dung chat sau khi gửi

        scrollToBottom();
    } catch (err) {
        console.error('sendMessage error:', err);
    }
};

// Khởi tạo khi component được mount
onMounted(async () => {
    const storeRealtime = localStorage.getItem('lastRealtime');
    if (!storeRealtime) {
        lastRealtime.value = 0;
    } else {
        lastRealtime.value = parseInt(storeRealtime);
    }
    await initChat();
    await nextTick(async () => {
        const question = initActions();
        if (question) {
            sendMessage({ type: 'text', text: question });
            saveUserHint(question);
        }
    });
});

// Hủy listener Firestore khi component bị hủy
onBeforeUnmount(() => {
    if (unsubscribe) unsubscribe();
});

// Đánh dấu tin nhắn đã đọc
const markMessagesAsRead = () => {
    lastRealtime.value = Date.now();
    localStorage.setItem('lastRealtime', lastRealtime.value.toString());
};
</script>

<style scoped>
/* Các style cho khung chat */
.chat-message-wrapper {
    display: flex;
    align-items: flex-end;
    margin-bottom: 10px;
    gap: 8px;
}

.align-right {
    justify-content: flex-end;
    flex-direction: row-reverse;
}

.align-left {
    justify-content: flex-start;
    flex-direction: row;
}

.chat-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    align-self: flex-start;
    transform: translateY(4px);
}

.chat-message {
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 12px;
    word-break: break-word;
    background-color: #f1f1f1;
    color: #333;
    display: inline-block;
}

.chat-box {
    position: fixed;
    bottom: 160px;
    right: 24px;
    width: 400px;
    max-height: 600px;
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    z-index: 10001;
    height: 500px;
}

/* Style cho header */
.chat-header {
    background-color: #e53935;
    color: white;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-title {
    font-weight: bold;
    font-size: 16px;
}

.chat-close {
    background: transparent;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Style cho gợi ý câu hỏi */
.chat-suggestions {
    padding: 8px 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: flex-start;
}

.suggestion-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.suggestion-item {
    background-color: #f1f1f1;
    color: #333;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 13px;
    cursor: pointer;
    max-width: 100%;
    word-break: break-word;
    transition: background-color 0.2s;
}

.suggestion-item:hover {
    background-color: #ddd;
}

/* Style cho phần tin nhắn */
.chat-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-messages {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 12px;
    overflow-y: auto;
    background-color: #f8f8f8;
}

.chat-message {
    margin-bottom: 10px;
    max-width: 80%;
    word-break: break-word;
    padding: 8px 12px;
    border-radius: 12px;
}

.from-user {
    background-color: #f1f1f1;
    align-self: flex-end;
    margin-left: auto;
    color: #333;
}

.from-admin {
    align-self: flex-start;
    margin-right: auto;
    background-color: #e6f4ff;
    color: #0b5394;
}

/* Style cho phần nhập tin nhắn */
.chat-input {
    display: flex;
    border-top: 1px solid #ddd;
    padding: 8px 16px;
    background: #fff;
    align-items: center;
    height: 60px;
}

.chat-input input {
    flex: 1;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 8px;
    margin-right: 8px;
    height: 40px;
    margin-top: 15px;
}

.chat-input button {
    background-color: #e53935;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    height: 40px;
}

.chat-input button:hover {
    background-color: #d32f2f;
}

/* Style cho loading */
.loading-overlay {
    position: absolute;
    inset: 0;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Style cho nút chọn file hình ảnh */
.dropzone-button {
    width: 40px;
    height: 40px;
    background-color: #e53935;
    border-radius: 8px;
    margin-right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.dropzone-button:hover {
    background-color: #d32f2f;
}

.dropzone-button i {
    color: white;
    font-size: 15px;
}

/* Style cho hình ảnh trong tin nhắn */
.chat-image {
    max-width: 200px;
    border-radius: 8px;
}

/* Responsive cho màn hình nhỏ */
@media (max-width: 480px) {
    .chat-box {
        width: 100%;
        height: 80vh;
        position: fixed;
        bottom: 0;
        right: 0;
        z-index: 999;
    }
    .chat-input {
        padding: 1px 20px 1px 20px;
        margin-bottom: 10px;
    }
}
</style>
