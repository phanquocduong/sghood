<template>
  <!-- Titlebar -->
  <div id="titlebar" class="gradient">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>Blog</h2><span>Latest News</span>
          <nav id="breadcrumbs">
            <ul>
              <li><NuxtLink to="/">Home</NuxtLink></li>
              <li>Blog</li>
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
                        <img :src="post.image" :alt="post.title" />
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
const baseUrl = useRuntimeConfig().public.baseUrl;
const popularPosts = [
  {
    id: 101,
    title: "Hotels for All Budgets",
    date: "October 26, 2016",
    image: "images/blog-widget-03.jpg",
    url: "/pages-blog-post"
  },
  {
    id: 102,
    title: "The 50 Greatest Street Arts In London",
    date: "November 9, 2016",
    image: "images/blog-widget-02.jpg",
    url: "/pages-blog-post"
  },
  {
    id: 103,
    title: "The Best Coffee Shops In Sydney Neighborhoods",
    date: "November 12, 2016",
    image: "images/blog-widget-01.jpg",
    url: "/pages-blog-post"
  }
]

const socialLinks = [
  { id: 1, name: "facebook", icon: "icon-facebook", url: "#" },
  { id: 2, name: "twitter", icon: "icon-twitter", url: "#" },
  { id: 3, name: "gplus", icon: "icon-gplus", url: "#" },
  { id: 4, name: "linkedin", icon: "icon-linkedin", url: "#" }
]
const fetchBlogs = async()=>{
  loading.value = true
  try{
    const res = await $api(`/blogs`,{
      method:'GET',
      headers:{
        'Content-Type': 'application/json',
      },
      
    })
    console.log(res)
    blogPosts.value = res.data.map( g => ({
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
  }finally{
    loading.value = false
  }
}
onMounted(()=>{
  fetchBlogs()
})
function stripHtml(html = '') {
  return html.replace(/<[^>]*>/g, '')
}
</script>

<style scoped>
/* Tùy chỉnh thêm nếu muốn */
</style>
