
<template>
    <ClientOnly>
        <div class="chat-box">
            <!-- Loading nhỏ trong khung chat -->
            <Loading :is-loading="isLoading" />

            <!-- Nội dung chat chỉ hiển thị khi không loading -->
            <template v-if="!isLoading">
                <!-- Header -->
                <div class="chat-header">
                    <span class="chat-title">Hỗ trợ người dùng</span>
                    <button class="chat-close" @click="$emit('close')">✕</button>
                </div>

                <!-- Nội dung chat -->
                <div class="chat-body">
                    <div class="chat-messages" ref="messageContainer">
                        <div
                            v-for="(msg, index) in messages"
                            :key="index"
                            :class="['chat-message-wrapper', msg.from === 'user' ? 'align-right' : 'align-left']"
                        >
                            <img :src="msg.from === 'user' ? avatarUrl : '/images/sghood_logo1.png'" alt="avatar" class="chat-avatar" />

                            <div :class="['chat-message', msg.from === 'user' ? 'from-user' : 'from-admin']">
                                <span class="chat-text">{{ msg.text }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gợi ý câu hỏi -->
                <div v-if="actions.length > 0" class="chat-suggestions">
                    <ul class="suggestion-list">
                        <li v-for="hint in actions" :key="hint" class="suggestion-item" @click="send(hint)">
                            {{ hint }}
                        </li>
                    </ul>
                </div>

                <!-- Nhập tin nhắn -->
                <div class="chat-input">
                    <input type="text" v-model="newMessage" @keyup.enter="sendMessage" placeholder="Nhập tin nhắn..." />
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
import { questionMap } from '~/utils/questionMap';
import { useRouter } from 'vue-router';
const emit = defineEmits(['close', 'unread']);
const authStore = useAuthStore();
const currentUserId = ref(authStore.user?.id || null);
const token = ref(authStore.token || '');
const behavior = useBehaviorStore();
const { $api, $firebaseDb } = useNuxtApp();
const newMessage = ref(behavior.chat || '');
const messages = ref([]);
const AdminId = ref(null);
const messageContainer = ref(null);
let unsubscribe = null; // để dừng listener khi unmount
const { user } = storeToRefs(authStore);
const config = useRuntimeConfig();
const isLoading = ref(false);

let initialized = ref(false);

const route = useRoute();
watch(newMessage, val => {
    behavior.updateChat(val);
});

const actions = computed(() => {
    const path = route.path;
    if (path === '/') return questionMap['/'];

    const Filter = path.split('/').filter(Boolean);
    const matchKey = Object.keys(questionMap).find(k => Filter.includes(k));

    return questionMap[matchKey] || questionMap['default'];
});
const send = text => {
    sendMessage(text);
    console.log(text);
};

const avatarUrl = computed(() => (user.value?.avatar ? config.public.baseUrl + user.value.avatar : '/images/dashboard-avatar.jpg'));

const lastVisitedPage = computed(() => {
    return behavior.visitedPages.at(-1) || '/';
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const initChat = async () => {
    isLoading.value = true;

    try {
        // 1) Gọi trực tiếp API start-chat để backend gán admin
        const res = await $api('/messages/start-chat', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token.value}`,
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                message: 'Xin chào, tôi cần hỗ trợ' // gửi để backend tạo tin nhắn đầu tiên
            }
        });
        if (!res?.status || !res?.admin_id) {
            console.warn('Không lấy được admin từ phản hồi start-chat');
            return;
        }

        AdminId.value = res.admin_id;

        // 2) Lấy lịch sử chat
        const history = await $api(`/messages/history/${AdminId.value}`, {
            headers: {
                Authorization: `Bearer ${token.value}`
            }
        });

        const incoming = history.data.map(msg => ({
            from: msg.sender_id === currentUserId.value ? 'user' : 'admin',
            text: msg.message
        }));
        // Gộp lịch sử với hiện tại
        const all = [...messages.value, ...incoming];
        messages.value = all;
        scrollToBottom();

        // 3) Lắng nghe realtime Firestore
        const chatId = [currentUserId.value, AdminId.value].sort().join('_');
        const msgQuery = query(collection($firebaseDb, 'messages'), where('chatId', '==', chatId), orderBy('createdAt', 'asc'));

        unsubscribe = onSnapshot(msgQuery, snapshot => {
            const changes = snapshot.docChanges().filter(change => change.type === 'added');
            const newMessages = changes.map(change => {
                const d = change.doc.data();
                return {
                    from: d.sender_id === currentUserId.value ? 'user' : 'admin',
                    text: d.text
                };
            });

            if (newMessages.length > 0) {
                // Gộp tin nhắn từ SQL + Firebase nếu cần
                const unique = [...messages.value, ...newMessages];
                messages.value = unique;
                scrollToBottom();

                if (newMessages.some(m => m.from === 'admin')) {
                    emit('unread');
                }
            }
        });
    } catch (error) {
        console.error('initChat error:', error);
    } finally {
        isLoading.value = false;
    }
};

const sendMessage = async (Textover = null) => {
    const Rawtext = typeof Textover === 'string' ? Textover : newMessage.value;

    const text = String(Rawtext).trim();
    if (!text || !AdminId.value) return;

    try {
        const chatId = [currentUserId.value, AdminId.value].sort().join('_');

        scrollToBottom();
        newMessage.value = '';

        // Gửi tin nhắn lên Firestore (realtime)
        await addDoc(collection($firebaseDb, 'messages'), {
            text,
            is_read: false,
            sender_id: currentUserId.value,
            receiver_id: AdminId.value,
            createdAt: serverTimestamp(),
            chatId
        });

        // Optionally: gọi API gửi nữa nếu backend cần lưu
        await $api('/messages/send', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token.value}`,
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                receiver_id: AdminId.value,
                message: text
            }
        });
        behavior.clearChat();

        scrollToBottom();
    } catch (err) {
        console.error('sendMessage error:', err);
    }
};

onMounted(() => {
    initChat();
});

onBeforeUnmount(() => {
    if (unsubscribe) unsubscribe();
});
</script>

<style scoped>
.chat-message-wrapper {
    display: flex;
    align-items: flex-end;
    /* Cho avatar và text thẳng hàng dưới */
    margin-bottom: 10px;
    gap: 8px; /* khoảng cách giữa avatar và tin nhắn */
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

.chat-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
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

/* Header */
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

/* Gợi ý */
.chat-suggestions {
    padding: 8px 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: flex-start;
}

.suggestion-title {
    width: 100%;
    font-weight: 500;
    font-size: 13px;
    margin-bottom: 4px;
    color: #444;
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

/* Tin nhắn */
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
    /* ✅ dùng hướng bình thường */
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

/* Input */
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
</style>
