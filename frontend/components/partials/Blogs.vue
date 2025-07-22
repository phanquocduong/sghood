<template>
  <div class="col-md-12">
    <div class="row">
      <div
        v-for="post in blogPosts"
        :key="post.id"  
        class="col-md-4 col-sm-12 mb-4"
      >
        <div class="blog-post">
          <!-- Ảnh bài viết -->
          <NuxtLink :to="post.url" class="post-img">
            <img :src="post.thumbnail" :alt="post.title" />
            <span class="hover-icon"><i class="fa fa-eye"></i></span>
          </NuxtLink>

          <!-- Ngày tạo bài viết -->
          <ul class="blog-post-tags">
            <li>{{ post.created_at }}</li>
          </ul>

          <!-- Nội dung bài viết -->
          <div class="post-content">
            <h3 class="post-title">
              <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
            </h3>

            <ul class="post-meta">
              <li>{{ post.date }}</li>
              <li><a href="#">Chia sẻ kinh nghiệm</a></li>
            </ul>

            <div class="post-excerpt">
              <p v-html="post.excerpt"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { onMounted, ref } from 'vue'

const loading = ref(false)
const { $api } = useNuxtApp()
const blogPosts = ref([])
const baseUrl = useRuntimeConfig().public.baseUrl

// ✅ Định nghĩa trước khi dùng
function formatDate(dateStr = '') {
  const date = new Date(dateStr)
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

function stripHtml(html = '') {
  return html.replace(/<[^>]*>/g, '')
}

const fetchBlogs = async () => {
  loading.value = true
  try {
    const res = await $api(`/blogs`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
    console.log('fetch blogs page', res)
    blogPosts.value = res.data
      .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
      .slice(0, 3)
      .map((g) => ({
        id: g.id,
        title: g.title,
        thumbnail: g.thumbnail?.startsWith('/storage')
          ? baseUrl + g.thumbnail
          : g.thumbnail,
        excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
        url: `/chia-se-kinh-nghiem/${g.slug}`,
        created_at: formatDate(g.created_at), // ✅ đã dùng
      }))
  } catch (e) {
    console.log('sai o dau do', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBlogs()
})
</script>


<style  scoped>
.blog-post-tags {
  list-style: none;
  padding: 0 20px;
  margin: 10px 0 0;
  font-size: 13px;
  color: #888;
}
.post-title {
    display: -webkit-box;
  -webkit-line-clamp: 1; /* hoặc 2 hoặc 4 tuỳ bạn */
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.post-content{
    padding: 15px;
}
.blog-post {
  position: relative;
  overflow: hidden;
  transition: transform 0.3s ease;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.blog-post:hover {
  transform: translateY(-5px);
}
.post-img {
  position: relative;
  overflow: hidden;
  border-radius: 8px 8px 0 0;
  height: 250px;
}
/* Ẩn icon mặc định */


/* Icon mới */
.hover-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 24px;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.custom-hover-icon:hover .hover-icon {
  opacity: 1;
}
</style>