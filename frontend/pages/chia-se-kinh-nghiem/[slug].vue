<template>
  <div>
    <!-- Titlebar -->
    <div id="titlebar" class="gradient">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h2>Bài viết</h2><span>Tin mới</span>
            <nav id="breadcrumbs">
              <ul>
                <li><a href="/">Trang chủ</a></li>
                <li>Bài viết</li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="container blog-page" v-if="!loading">
      <div class="row">
      
       <!-- Main Post Content -->
<div class="col-lg-9 col-md-8 padding-right-30">
  <!-- Kết quả tìm kiếm -->
  <div v-if="searchKeyword">
    <h4 class="headline margin-top-25">Kết quả tìm kiếm cho "{{ searchKeyword }}"</h4>

    <div v-if="blogList.length > 0" class="row">
      <div class="col-md-12" v-for="item in blogList" :key="item.id">
        <NuxtLink :to="item.url" class="blog-compact-item-container">
          <div class="blog-compact-item">
            <img :src="item.thumbnail" alt="">
            <span class="blog-item-tag">Tìm thấy</span>
            <div class="blog-compact-item-content">
              <h3>{{ item.title }}</h3>
              <p>{{ item.excerpt }}</p>
            </div>
          </div>
        </NuxtLink>
      </div>
    </div>

    <div v-else>
      <p class="text-gray-500">Không tìm thấy bài viết phù hợp với từ khóa "{{ searchKeyword }}"</p>
    </div>
  </div>

  <!-- Bài viết chi tiết -->
  <div v-else>
    <div class="blog-post single-post" v-if="blog">
      <img class="post-img" :src="blog.thumbnail" alt="">
      <div class="post-content">
        <h3>{{ blog.title }}</h3>
        <ul class="post-meta">
          <li>{{ blog.created_at }}</li>
          <li><a href="#">{{ blog.category || 'Chưa phân loại' }}</a></li>
          <li><a href="#">{{ blog.comments || 0 }} bình luận</a></li>
        </ul>
        <p v-html="blog.content"></p>

        <!-- Share Buttons -->
        <ul class="share-buttons margin-top-40">
          <li><a href="#"><i class="fa fa-facebook"></i> Share</a></li>
          <li><a href="#"><i class="fa fa-twitter"></i> Tweet</a></li>
        </ul>
      </div>
    </div>

    <!-- Related Posts -->
    <h4 class="headline margin-top-25">Bài viết liên quan</h4>
    <div class="row">
      <div class="col-md-6" v-for="item in relatedPosts" :key="item.id">
        <a :href="item.url" class="blog-compact-item-container">
          <div class="blog-compact-item">
            <img :src="item.thumbnail" alt="">
            <span class="blog-item-tag">{{ item.category }}</span>
            <div class="blog-compact-item-content">
              <h3>{{ item.title }}</h3>
            </div>
          </div>
        </a>
      </div>
    </div>
    
    <Comments :key="commentsKey"/>
   
  </div>
</div>


        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4">
          <div class="sidebar right">

            <div class="widget margin-top-40">
              <h3>Bài viết phổ biến</h3>
              <ul class="widget-tabs">
                <li v-for="post in popularPosts" :key="post.id">
                  <div class="widget-content">
                    <div class="widget-thumb">
                      <NuxtLink :to="post.url">
                        <img :src="post.thumbnail" :alt="post.title" />
                      </NuxtLink>
                    </div>
                    <div class="widget-text">
                      <h5><NuxtLink :to="post.url">{{ post.title }}</NuxtLink></h5>
                      <span>{{ post.date }}</span>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                </li>
              </ul>
            </div>

            <div class="widget margin-top-40">
              <h3>Kết nối</h3>
              <ul class="social-icons rounded">
                <li><a class="facebook" href="#"><i class="icon-facebook"></i></a></li>
                <li><a class="twitter" href="#"><i class="icon-twitter"></i></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="container py-10 text-center text-gray-600">Đang tải dữ liệu bài viết...</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNuxtApp, useRuntimeConfig } from '#app'
import Comments from '~/components/partials/comments/Comments.vue'
const route = useRoute()
const blog = ref((null))
const loading = ref(false)
const {$api} = useNuxtApp()
const relatedPosts = ref([])
const blogList = ref([])
const popularPosts = ref([])
const baseUrl = useRuntimeConfig().public.baseUrl
const hasIncreasedView = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
function formatDate(dateStr = '') {
  if (!dateStr) return 'Không rõ ngày';

  // Xử lý dạng dd-MM-yyyy
  if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
    const [day, month, year] = dateStr.split('-');
    dateStr = `${year}-${month}-${day}`; // Đổi sang yyyy-MM-dd
  }

  const date = new Date(dateStr);
  if (isNaN(date.getTime())) return 'Ngày không hợp lệ';

  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
}
const fetchBlogs = async(slug)=>{
  loading.value = true
  try{
    if(!slug) slug = route.params.slug
    if(!slug) return
    const res = await $api(`/show/${slug}`,{
      method:'GET',
      headers:{
        'Content-Type': 'application/json',
      },
    })
    const fixedContent = (res.data.content || ' ')
    .replace(/src=['"]\/storage/g,`src="${baseUrl}/storage/`)
    blog.value = {
        id: res.data.id,
      title: res.data.title,
      thumbnail: res.data.thumbnail?.startsWith('/storage') ? baseUrl + res.data.thumbnail : res.data.thumbnail,
      content: fixedContent,
      date: res.data.created_at,
      category: res.data.category || 'Tin tức',
      created_at: formatDate(res.data.created_at)
    }
    
    console.log('fetchblogs',res)
    if(!hasIncreasedView.value){
        await $api(`/blogs/${res.data.id}/increase-view`,{
        method:'POST',
        headers:{
          'Content-Type': 'application/json',
        }

      })
      hasIncreasedView.value = true
    }
    relatedPosts.value = (res.data.related || []).map( g => ({
       id: g.id,
      title: g.title,
      thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
      excerpt: g.excerpt || (typeof g.content === 'string' ? g.content.slice(0, 100) + '...' : ''),
      url: `/chia-se-kinh-nghiem/${g.slug}`,
      date: g.created_at
    }))
     
  }catch(e){
    console.log('sai o dau do', e)
  }finally{
    loading.value = false
  }
}
const FetchPopularPosts = async ()=>{ 

  try{
    const res = await $api(`/blogs/popular`,{
      method:'GET',
      headers:{
        'Content-Type': 'application/json',
      },
      
    })
    
    popularPosts.value = res.map( g => ({
      id : g.id,
      title :g.title,
     thumbnail: g.thumbnail?.startsWith('/storage')
    ? baseUrl + g.thumbnail
    : g.thumbnail,
      excerpt:g.excerpt || stripHtml(g.content).slice(0 , 100) + '...',
      url: `/chia-se-kinh-nghiem/${g.slug}`,
    }))
  }catch(e){
    console.log('sai o dau do', e)
  }
}
const fetchRelatedPosts = async(id)=>{
  
  try{
    const res = await $api(`blogs/${id}/related`,{
      method:'GET',
      headers:{
        'Content-Type': 'application/json',
      },
    })
    console.log('relatedres',res)
    relatedPosts.value = res.slice(0,2).map( g => ({
      id: g.id,
      category:g.category,
      title: g.title,
      thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
      excerpt: g.excerpt || (typeof g.content === 'string' ? g.content.slice(0, 100) + '...' : ''),
      url: `/chia-se-kinh-nghiem/${g.slug}`,
      date: g.created_at
    }))
    
  }catch(e){
    console.log('sai o dau do', e)
  }
}

onMounted(async()=>{
   await fetchBlogs()
   await FetchPopularPosts()
   if (blog.value && blog.value.id) {
      await fetchRelatedPosts(blog.value.id)
        await nextTick() 
    }
  }
)
function stripHtml(html = '') {
  return html.replace(/<[^>]*>/g, '')
}
</script>

<style scoped>

</style>
