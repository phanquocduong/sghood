<template>
  <div class="chat-icon" @click="$emit('toggle')">
    <MessageSquare class="icon" />
    <span v-if="unreadMessages > 0 " class="unread-badge">
      {{ unreadMessages > 9 ? '9+':unreadMessages }}
    </span>
  </div>
</template>

<script setup>
import { MessageSquare } from 'lucide-vue-next';

defineProps({
  unreadMessages:Number
})
</script>

<style scoped>
.chat-icon {
  position: fixed;
  bottom: 90px; /* nằm trên #backtotop */
  right: 24px;
  z-index: 10000; /* đảm bảo luôn nằm trên */
  background-color: #f91942;
  color: white;
  border-radius: 50%;
  width: 46px;
  height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  transition: transform 0.2s ease;
  margin-right: 2px;
}
.chat-icon:hover {
  transform: scale(1.1);
}
.icon {
  width: 28px;
  height: 28px;
}
.unread-badge-number {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: #ff3b30;
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 4px;
  border-radius: 12px;
  box-shadow: 0 0 0 1px white;
}
@keyframes pulse {
  0% { transform: scale(1); opacity: 1 }
  50% { transform: scale(1.3); opacity: 0.7 }
  100% { transform: scale(1); opacity: 1 }
}
</style>
