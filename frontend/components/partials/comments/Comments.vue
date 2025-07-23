<template>
  
  <section class="comments">
    <h4 class="headline margin-bottom-35">
      Comments <span class="comments-amount">({{ comments.length }})</span>
    </h4>
    <p v-if="comments.length === 0" class="text-gray-400">Chưa có bình luận nào. Nếu muốn bình luận hãy đăng nhập nhé!</p>
    <ul>
      <template v-for="comment in comments" :key="comment.id" >
        <CommentsNode v-if="comment" :comment="comment" :blog_id="comment.blog_id" @refresh=" fetchComments" />
      </template> 
      <div id="add-review" class="add-review-box" v-if="authStore.user " >
 
         <!-- Add Review -->
         <h3 class="listing-desc-headline margin-bottom-35">Add Review</h3>
   
         <!-- Review Comment -->
         <form id="add-comment" class="add-comment">
           <fieldset>
 
             <div class="row">
               <div class="col-md-6">
                 <label >Name:</label>
                 <input type="text" v-model="name"/>
               </div>
                 
               <div class="col-md-6">
                 <label >Email:</label>
                 <input type="text" v-model="email"/>
               </div>
             </div>
 
             <div>
               <label>Comment:</label>
               <textarea cols="40" rows="3" v-model="ReplayContent"></textarea>
             </div>
 
           </fieldset>
 
           <button class="button" @click.prevent="AddReplay(blog_id.value)" type="submit"
                           id="submit"
                           value="Gửi tin nhắn"
                           :disabled="loading"
                           style="margin-bottom: 10px; margin-top: -10px"
                       >
                           <span v-if="loading" class="spinner"></span>
                           {{ loading ? ' Đang gửi...' : 'Gửi đi' }}>Gửi</button>
         </form>
 
       </div>
    </ul>
  </section>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import CommentsNode from './CommentsNode.vue'
import { useRoute } from 'vue-router'
import { useToast } from 'vue-toastification';
const comments = ref([])
const {$api} = useNuxtApp()
const name = ref('')
const showReplay = ref(false)
const email = ref('')
const toast = useToast()
const loading = ref(false);
const ReplayContent =  ref('')
const blog_id = ref(null)
const route = useRoute()
const slug = computed(() => route.params.slug)
const authStore = useAuthStore()

const fetchComments = async () => {
  try {
    const res = await $api(`/blogs/${slug.value}/comments`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      },
    })
    comments.value = res.data || []
    if(res.data.length > 0){
      blog_id.value = res.data[0].blog_id
    }
    console.log('Comments:', res.data)
  } catch (error) {
    console.error('Lỗi khi fetch bình luận:', error)
  }
}
const AddReplay = async (blog_id) => {
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

      })
    })
    console.log('Reply response:', res)
    showReplay.value = false
    ReplayContent.value = ''
    await fetchComments()
    toast.success('Bình luận đã được gửi thành công!')
  }catch (error) {
    console.error('Error handling reply:', error)
    toast.error('Có lỗi xảy ra khi gửi bình luận.')
  }finally {
    loading.value = false
  }
}
// 👀 Theo dõi slug thay đổi
watch(slug, (s) => {
  if (s) fetchComments()
}, { immediate: true })
onMounted(() => {
    if (authStore.user) {
        name.value = authStore.user.name || '';
        email.value = authStore.user.email || '';
    }
});
</script>

<style scoped>
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
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


</style>