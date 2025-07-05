<template>
  <div>
    <!-- Titlebar -->
    <div id="titlebar" class="gradient">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h2>Blog</h2><span>Latest News</span>
            <nav id="breadcrumbs">
              <ul>
                <li><a href="#">Home</a></li>
                <li>Blog</li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="container blog-page">
      <div class="row">

        <!-- Main Post Content -->
        <div class="col-lg-9 col-md-8 padding-right-30">

          <!-- Blog Post -->
          <div class="blog-post single-post">
            <img class="post-img" src="/images/blog-post-02.jpg" alt="">
            <div class="post-content">
              <h3>Top 50 Nghệ Thuật Đường Phố London</h3>
              <ul class="post-meta">
                <li>22 Tháng 8, 2019</li>
                <li><a href="#">Mẹo hay</a></li>
                <li><a href="#">5 bình luận</a></li>
              </ul>
              <p>Nam nisl lacus, dignissim ac tristique ut, scelerisque eu massa...</p>
              <div class="post-quote">
                <blockquote>
                  Mauris aliquet ultricies ante, non faucibus ante gravida sed...
                </blockquote>
              </div>
              <p>Thêm nhiều nội dung chi tiết tại đây...</p>

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
            <div class="col-md-6">
              <a href="#" class="blog-compact-item-container">
                <div class="blog-compact-item">
                  <img src="/images/blog-compact-post-01.jpg" alt="">
                  <span class="blog-item-tag">Tips</span>
                  <div class="blog-compact-item-content">
                    <h3>Khách sạn giá rẻ</h3>
                    <p>Mô tả ngắn...</p>
                  </div>
                </div>
              </a>
            </div>
          </div>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4">
          <div class="sidebar right">
            <div class="widget">
              <h3 class="margin-top-0">Tìm kiếm</h3>
              <div class="search-blog-input">
                <input type="text" class="search-field" placeholder="Gõ và enter...">
              </div>
            </div>

            <div class="widget margin-top-40">
              <h3>Liên hệ</h3>
              <div class="info-box">
                <p>Nếu bạn có thắc mắc, đừng ngần ngại!</p>
                <a href="/lien-he" class="button fullwidth"><i class="fa fa-envelope-o"></i> Gửi thông tin</a>
              </div>
            </div>

            <div class="widget margin-top-40">
              <h3>Bài viết phổ biến</h3>
              <ul class="widget-tabs">
                <li>
                  <div class="widget-content">
                    <div class="widget-thumb">
                      <a href="#"><img src="/images/blog-widget-01.jpg" alt=""></a>
                    </div>
                    <div class="widget-text">
                      <h5><a href="#">Khách sạn giá rẻ</a></h5>
                      <span>26/10/2016</span>
                    </div>
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
  </div>
</template>

<script setup>

</script>

<style scoped>

</style>
