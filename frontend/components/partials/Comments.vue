<template>
  <section class="comments">
    <h4 class="headline margin-bottom-35">
      Comments <span class="comments-amount">({{ comments.length }})</span>
    </h4>

    <!-- Form bình luận -->
    <div class="comment-form margin-bottom-35" v-if="authStore.user">
      <input
        v-model="newComment.user"
        readonly
        class="form-input"
        placeholder="Tên của bạn"
      />
      <textarea
        v-model="newComment.content"
        placeholder="Viết bình luận..."
        class="form-textarea"
      ></textarea>
      <button @click="submitComment" class="button">Gửi bình luận</button>
    </div>
    <div v-else>
      <p>Vui lòng đăng nhập để bình luận.</p>
    </div>

    <!-- Danh sách bình luận -->
    <ul>
      <CommentItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        @reply="setReply"
      />
    </ul>
  </section>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useCookie } from '#imports';
import { useNuxtApp } from '#app';
import { useAuthStore } from '~/stores/auth'; // hoặc đường dẫn đúng


const { $api } = useNuxtApp();
const route = useRoute();
const authStore = useAuthStore();
const comments = ref([]);

const newComment = ref({
  user: '',        // tên người dùng
  content: '',     // nội dung bình luận
  parent_id: null, // nếu là reply
});

const fetchComments = async () => {
    const blog_id = route.params.blog
  try {
    const response = await $api(`/blogs/${blog_id}/comments`,{
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
           'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
        },
    });
    console.log('Comments fetched:', response.data);
    comments.value = response.data;
  } catch (error) {
    console.error('Error fetching comments:', error);
  }
};

onMounted(() => {
  fetchComments();
});

</script>

<style scoped>

</style>