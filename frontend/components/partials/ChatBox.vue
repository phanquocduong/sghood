<template>
  <ClientOnly>
    <div class="chat-box">
      <!-- Header Chat -->
      <div class="chat-header">
        <span class="chat-title">Hỗ trợ người dùng</span>
        <button class="chat-close" @click="$emit('close')">✕</button>
      </div>
  
      <!-- Nội dung tin nhắn -->
      <div class="chat-messages" ref="messageContainer">
        <div
          v-for="(msg, index) in messages"
          :key="index"
          :class="['chat-message', msg.from === 'user' ? 'from-user' : 'from-admin']"
        >
          <span class="chat-text">{{ msg.text }}</span>
        </div>
      </div>
  
      <!-- Nhập tin nhắn -->
      <div class="chat-input">
        <input
          type="text"
          v-model="newMessage"
          @keyup.enter="sendMessage"
          placeholder="Nhập tin nhắn..."
        />
        <button @click="sendMessage">Gửi</button>
      </div>
    </div>
  </ClientOnly>
</template>


<script setup>
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '~/stores/auth'
import { useCookie } from '#app'
import { collection, query, where, orderBy, onSnapshot, addDoc, serverTimestamp } from 'firebase/firestore'

const authStore = useAuthStore()
const currentUserId = ref(authStore.user?.id || null)
const token = ref(authStore.token || '')
const { $api, $firebaseDb } = useNuxtApp()
const newMessage = ref('')
const messages = ref([])
const AdminId = ref(null)
const messageContainer = ref(null)
let unsubscribe = null // để dừng listener khi unmount

const scrollToBottom = () => {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight
    }
  })
}

const initChat = async () => {
  try {
    const res = await $api('/users/admins', {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    const admins = res.data || []
    if (admins.length === 0) return

    const random = Math.floor(Math.random() * admins.length)
    const admin = admins[random]
    AdminId.value = admin.id
    const chatId = [currentUserId.value, admin.id].sort().join('_')

    // 1. Gọi API start-chat
    await $api('/messages/start-chat', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`,
        'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
      },
      body: {
        receiver_id: admin.id,
        sender_id: currentUserId.value
      }
    })

    // 2. Gọi API lịch sử
    const history = await $api(`/messages/history/${admin.id}`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })
    if (Array.isArray(history.data)) {
      messages.value = history.data.map(msg => ({
        from: msg.sender_id === currentUserId.value ? 'user' : 'admin',
        text: msg.message
      }))
    }
    console.log(currentUserId)
    // 3. Lắng nghe Firestore realtime
    const msgQuery = query(
      collection($firebaseDb, 'messages'),
      where('chatId', '==', chatId),
      orderBy('createdAt', 'asc')
    )

    unsubscribe = onSnapshot(msgQuery, snapshot => {
      const newMessages = snapshot.docs.map(doc => {
        const data = doc.data()
        return {
          from: data.sender_id === currentUserId.value ? 'user' : 'admin',
          text: data.text
        }
      })
      messages.value = [...messages.value, ...newMessages]
      scrollToBottom()
    })

  } catch (error) {
    console.error('initChat error:', error)
  }
}


const sendMessage = async () => {
  const text = newMessage.value.trim()
  if (!text || !AdminId.value) return

  try {
    const chatId = [currentUserId.value, AdminId.value].sort().join('_')
    // Gửi tin nhắn lên Firestore (realtime)
 await addDoc(collection($firebaseDb, 'messages'), {
  text,
  sender_id: currentUserId.value,
  receiver_id: AdminId.value,
  createdAt: serverTimestamp(),
  chatId: [currentUserId.value, AdminId.value].sort().join('_')
})


    // Optionally: gọi API gửi nữa nếu backend cần lưu
    await $api('/messages/send', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
      },
      body: {
        receiver_id: AdminId.value,
        message: text
      }
    })

    newMessage.value = ''
    scrollToBottom()
  } catch (err) {
    console.error('sendMessage error:', err)
  }
}

onMounted(() => {
  initChat()
})

onBeforeUnmount(() => {
  if (unsubscribe) unsubscribe()
})

</script>


<style scoped>
.chat-box {
  position: fixed;
  bottom: 160px; /* nằm trên nút backtotop */
  right: 24px;
  width: 340px;
  max-height: 500px;
  display: flex;
  flex-direction: column;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  z-index: 10001;
   height: 400px;
}

/* Header */
.chat-header {
  background-color: #e53935; /* đỏ chủ đạo */
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

/* Vùng tin nhắn */
.chat-messages {
  flex: 1;
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
   background-color: #e6f4ff; /* xanh nhạt */
  color: #0b5394;            /* xanh chữ */
}

/* Input nhắn tin */
.chat-input {
  display: flex;
  border-top: 1px solid #ddd;
  padding: 8px 16px;
  background: #fff;
  align-items: center;
  height: auto;
}
.chat-input input {
  flex: 1;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 8px;
  padding-top: 5px;
  margin-right: 8px;
  margin-bottom:5px ;
  height: 40px;
}
.chat-input button {
  background-color: #e53935;
  color: white;
  border: none;
  padding: 4px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s;
  height: 40px;
  margin-top: -5px;
}
.chat-input button:hover {
  background-color: #d32f2f;
}
</style>
