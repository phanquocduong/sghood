<template>
  
  <section class="comments">
    <h4 class="headline margin-bottom-35">
      Comments <span class="comments-amount">({{ comments.length }})</span>
    </h4>
    <p v-if="comments.length === 0" class="text-gray-400">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
    <ul>
      <template v-for="comment in comments" :key="comment.id" >
        <CommentsNode v-if="comment" :comment="comment" :blog_id="comment.blog_id" @refresh="fetchComments" />
      </template> 
    </ul>
  </section>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import CommentsNode from './CommentsNode.vue'
import { useRoute } from 'vue-router'

const comments = ref([])
const {$api} = useNuxtApp()
const route = useRoute()
const slug = computed(() => route.params.slug)

const fetchComments = async () => {
  try {
    const res = await $api(`/blogs/${slug.value}/comments`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    })
    comments.value = res.data || []
    console.log('Comments:', res.data)
  } catch (error) {
    console.error('Lỗi khi fetch bình luận:', error)
  }
}

// 👀 Theo dõi slug thay đổi
watch(slug, (s) => {
  if (s) fetchComments()
}, { immediate: true })
</script>

