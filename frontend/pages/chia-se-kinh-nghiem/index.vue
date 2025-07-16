<template>
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
  <div class="container">
    <div class="blog-page">
      <div class="row">
        <!-- Blog Posts -->
        <div class="col-lg-9 col-md-8 padding-right-30">
          <div v-if="loading" class="text-center p-5">
            <p>Đang tải bài viết...</p>
          </div>

          <div v-else>
            <div v-for="post in blogPosts" :key="post.id" class="blog-post">
              <NuxtLink :to="post.url" class="post-img">
                <img :src="post.thumbnail" :alt="post.title" />
              </NuxtLink>

              <div class="post-content">
                <h3>
                  <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
                </h3>

                <ul class="post-meta">
                  <li>{{ post.date }}</li>
                  <li><a href="#">Chia sẻ kinh nghiệm</a></li>
                </ul>

                <p v-html="post.excerpt"></p>
                <NuxtLink :to="post.url" class="read-more">
                  Xem thêm <i class="fa fa-angle-right"></i>
                </NuxtLink>
              </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-container margin-bottom-40" v-if="totalPages > 1">
              <nav class="pagination">
                <ul>
                  <li v-if="currentPage > 1">
                    <a href="#" @click.prevent="goToPage(currentPage - 1)">
                      <i class="sl sl-icon-arrow-left"></i>
                    </a>
                  </li>

                  <li v-for="page in totalPages" :key="page">
                    <a
                      href="#"
                      :class="{ 'current-page': page === currentPage }"
                      @click.prevent="goToPage(page)"
                    >
                      {{ page }}
                    </a>
                  </li>

                  <li v-if="currentPage < totalPages">
                    <a href="#" @click.prevent="goToPage(currentPage + 1)">
                      <i class="sl sl-icon-arrow-right"></i>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4">
          <div class="sidebar right">
            <!-- Popular Posts -->
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

            <!-- Social -->
            <div class="widget margin-top-40">
              <h3 class="margin-bottom-25">Kết nối mạng xã hội</h3>
              <ul class="social-icons rounded">
                <li v-for="link in socialLinks" :key="link.id">
                  <a :class="link.name" :href="link.url"><i :class="link.icon"></i></a>
                </li>
              </ul>
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
const {$api} = useNuxtApp()
const blogPosts = ref([])
const popularPosts=ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const baseUrl = useRuntimeConfig().public.baseUrl;
const goToPage = (page) =>{
  if(page !== currentPage.value) {
    currentPage.value =page;
    fetchBlogs(page);
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
    console.log(res)
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
const socialLinks = [
  { id: 1, name: "facebook", icon: "icon-facebook", url: "#" },
  { id: 2, name: "twitter", icon: "icon-twitter", url: "#" },
  { id: 3, name: "gplus", icon: "icon-gplus", url: "#" },
  { id: 4, name: "linkedin", icon: "icon-linkedin", url: "#" }
]
const fetchBlogs = async(page=1)=>{
  loading.value = true
  try{
    const res = await $api(`/blogs?page=${page}`,{
      method:'GET',
      headers:{
        'Content-Type': 'application/json',
      },
      
    })
    console.log(res)
    blogPosts.value = res.data.slice(0,4).map( g => ({
      id : g.id,
      title :g.title,
     thumbnail: g.thumbnail?.startsWith('/storage')
    ? baseUrl + g.thumbnail
    : g.thumbnail,
      excerpt:g.excerpt || stripHtml(g.content).slice(0 , 100) + '...',
      url: `/chia-se-kinh-nghiem/${g.slug}`,
    }))
    currentPage.value = res.current_Page || 1
    totalPages.value = res.last_Page || 1
  }catch(e){
    console.log('sai o dau do', e)
  }finally{
    loading.value = false
  }
}
onMounted(()=>{
  fetchBlogs(1)
  FetchPopularPosts()
})
function stripHtml(html = '') {
  return html.replace(/<[^>]*>/g, '')
}
</script>

<style scoped>
/* Tùy chỉnh thêm nếu muốn */
</style>
