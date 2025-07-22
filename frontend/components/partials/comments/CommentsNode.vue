<!-- components/CommentNode.vue -->
<template>
  <li v-if="comment">
    <div class="avatar">
     <img
  class="avatar"
 :src="comment.user_id === authStore.user?.id
  ? baseUrl + authStore.user.avatar
  : (comment.user?.avatar ? baseUrl + comment.user.avatar : defaultAvatar)"

/>
    </div>

    <div class="comment-content">
      <div class="arrow-comment"></div>
      <div class="comment-by">
        <template v-if="comment.user_id === authStore.user?.id">
          {{ authStore.user.name }} <span class="badge">Bạn</span>
        </template>
        <template v-else>
          {{ comment.user?.name || 'Người dùng' }}
        </template>

        <span class="date">{{ formatTimeAgo(comment.created_at) }}</span>
        <a href="#" class="reply" @click.prevent="toggleReply">
          <i class="fa fa-reply"></i> Trả lời
        </a>

      </div>
      <p>{{ comment.content }}</p>
    </div>
    <div v-if="showReplay" class="reply-box">
<textarea v-model="ReplayContent" placeholder="Nhập trả lời..."></textarea>
<button
  type="button"
  class="submit button"
  @click="() => HandleReply(comment.blog_id)"
  :disabled="loading"
  style="margin-top: 10px"
>
  <span v-if="loading" class="spinner"></span>
  {{ loading ? ' Đang gửi...' : 'Gửi' }}
</button>

</div>
    <!-- Đệ quy hiển thị comment con -->
    <ul v-if="comment.children && comment.children.length">
      <CommentsNode
        v-for="child in comment.children"
        :key="child.id"
        :comment="child"
        :blog_id="comment.blog_id"
        @refresh="fetchComments"
      />
    </ul>
  </li>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import CommentsNode from './CommentsNode.vue' // quan trọng để đệ quy chính nó
import { formatTimeAgo } from '~/utils/time';
import { ref } from 'vue'
import { useToast } from 'vue-toastification';
const config = useRuntimeConfig()
const baseUrl = config.public.baseUrl
const props = defineProps({
  comment: {
    type: Object,
    required: true,
    default: () => ({})
  }
})
const emit = defineEmits(['refresh'])
const toggleReply = () => {
  showReplay.value = !showReplay.value
}
const showReplay = ref(false)
const loading = ref(false)

const ReplayContent =  ref('')
const toast = useToast()
const { $api } = useNuxtApp()
const authStore = useAuthStore()
const defaultAvatar = '/images/sghood_logo1.png'
const HandleReply = async (blog_id) => {
  if(ReplayContent.value.trim() === '') {
    toast('Vui lòng nhập nội dung bình luận')
    return
  }
  loading.value = true
  try{
    const res = await $api(`/blogs/${blog_id}/send-comment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        content: ReplayContent.value,
        user_id: authStore.user?.id,
        parent_id : props.comment.id,
      })
    })
    console.log('Reply response:', res)
    showReplay.value = false
    ReplayContent.value = ''
    emit('refresh')
  }catch (error) {
    console.error('Error handling reply:', error)
  }finally {
    loading.value = false
  }
}

</script>

<style scoped>
.comment-by {
font-weight:bold ;
}
.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ffffff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 1s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}



</style>