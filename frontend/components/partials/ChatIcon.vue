<template>
  <div class="chat-icon" @click="$emit('toggle')">
    <MessageSquare class="icon" />
  </div>
</template>

<script setup>
import { MessageSquare } from 'lucide-vue-next';
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
</style>
