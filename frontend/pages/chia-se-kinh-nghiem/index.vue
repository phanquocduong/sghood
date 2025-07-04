<template>
  <!-- Titlebar -->
  <div id="titlebar" class="gradient">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>Blog</h2><span>Latest News</span>
          <!-- Breadcrumbs -->
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
          <div v-for="post in blogPosts" :key="post.id" class="blog-post">
            <NuxtLink :to="post.url" class="post-img">
              <img :src="post.image" :alt="post.title" />
            </NuxtLink>

            <div class="post-content">
              <h3>
                <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
              </h3>

              <ul class="post-meta">
                <li>{{ post.date }}</li>
                <li><a href="#">{{ post.category }}</a></li>
                <li><a href="#">{{ post.comments }} Comments</a></li>
              </ul>

              <p>{{ post.excerpt }}</p>
              <NuxtLink :to="post.url" class="read-more">
                Read More <i class="fa fa-angle-right"></i>
              </NuxtLink>
            </div>
          </div>

          <!-- Pagination -->
          <div class="clearfix"></div>
          <div class="row">
            <div class="col-md-12">
              <div class="pagination-container margin-bottom-40">
                <nav class="pagination">
                  <ul>
                    <li><a href="#" class="current-page">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#"><i class="sl sl-icon-arrow-right"></i></a></li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4">
          <div class="sidebar right">
            <!-- Search -->
            <div class="widget">
              <h3 class="margin-top-0 margin-bottom-25">Search Blog</h3>
              <div class="search-blog-input">
                <div class="input">
                  <input class="search-field" type="text" placeholder="Type and hit enter" />
                </div>
              </div>
              <div class="clearfix"></div>
            </div>

            <!-- Contact -->
            <div class="widget margin-top-40">
              <h3>Got any questions?</h3>
              <div class="info-box margin-bottom-10">
                <p>Having any questions? Feel free to ask!</p>
                <NuxtLink to="/pages-contact" class="button fullwidth margin-top-20">
                  <i class="fa fa-envelope-o"></i> Drop Us a Line
                </NuxtLink>
              </div>
            </div>

            <!-- Popular Posts -->
            <div class="widget margin-top-40">
              <h3>Popular Posts</h3>
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
              <h3 class="margin-bottom-25">Social</h3>
              <ul class="social-icons rounded">
                <li v-for="link in socialLinks" :key="link.id">
                  <a :class="link.name" :href="link.url"><i :class="link.icon"></i></a>
                </li>
              </ul>
            </div>

            <div class="clearfix"></div>
            <div class="margin-bottom-40"></div>
          </div>
        </div>
        <!-- Sidebar / End -->
      </div>
    </div>
  </div>
</template>

<script setup>
const blogPosts = [
  {
    id: 1,
    title: "Hotels for All Budgets",
    date: "August 22, 2019",
    category: "Tips",
    comments: 5,
    image: "images/blog-post-01.jpg",
    excerpt: "Nam nisl lacus, dignissim ac tristique ut, scelerisque eu massa...",
    url: "/pages-blog-post"
  },
  {
    id: 2,
    title: "The 50 Greatest Street Arts In London",
    date: "August 18, 2019",
    category: "Tips",
    comments: 7,
    image: "images/blog-post-02.jpg",
    excerpt: "Vestibulum ligula nunc, rutrum in malesuada vitae, tempus sed augue...",
    url: "/pages-blog-post"
  },
  {
    id: 3,
    title: "The Best Coffee Shops In Sydney Neighborhoods",
    date: "August 10, 2019",
    category: "Tips",
    comments: 12,
    image: "images/blog-post-03.jpg",
    excerpt: "Curabitur quis lectus quis augue dapibus facilisis...",
    url: "/pages-blog-post"
  }
]

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
</script>

<style scoped>
/* Tùy chỉnh thêm nếu muốn */
</style>
