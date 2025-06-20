<template>
  <div class="chat-box">
    <!-- Header Chat -->
    <div class="chat-header">
      <span class="chat-title">Hỗ trợ khách thuê</span>
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
</template>


<script setup>
import { ref,nextTick } from 'vue';

const newMessage = ref('')
const messages = ref([
    {from:'admin',text:'Chào bạn! Bạn cần hỗ trợ gì'}
])
// auto Keo
const messageContainer = ref(null)
const scrollToBottom = ()=>{
    nextTick(()=>{
        if(messageContainer.value){
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight
        }
    })
}
// tin nhan gia lap
const sendMessage = () =>{
    const text= newMessage.value.trim()
    if(!text)return
    
    //them tin nhan user
    messages.value.push({from:'user',text})
    newMessage.value=''
    scrollToBottom()
    
    // gia lap phan hoi trong 1s

    setTimeout(()=>{
        messages.value.push({from:'admin', text:'Cảm ơn bạn! Chúng tôi sẽ phản hồi sớm nhất nhé.'})
        scrollToBottom();
    }, 1000)

}
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
  padding: 5px;
  background: #fff;

}
.chat-input input {
  flex: 1;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 8px;
  padding-top: 5px;
  margin-right: 8px;
  margin-bottom:5px ;
}
.chat-input button {
  background-color: #e53935;
  color: white;
  border: none;
  padding: 4px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s;
  height: 50px;

}
.chat-input button:hover {
  background-color: #d32f2f;
}
</style>
