<!-- Template hiển thị danh sách bài viết blog -->
<template>
    <div class="col-md-12">
        <div class="row">
            <!-- Hiển thị từng bài viết -->
            <div v-for="post in blogPosts" :key="post.id" class="col-md-4 col-sm-12 mb-4">
                <div class="blog-post">
                    <!-- Ảnh đại diện bài viết -->
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
                            <li><NuxtLink href="chia-se-kinh-nghiem">Chia sẻ kinh nghiệm</NuxtLink></li>
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
import { onMounted, ref } from 'vue';

// Khởi tạo biến trạng thái
const loading = ref(false);
const { $api } = useNuxtApp();
const blogPosts = ref([]);
const baseUrl = useRuntimeConfig().public.baseUrl;

// Hàm định dạng ngày tháng
function formatDate(dateStr = '') {
    const date = new Date(dateStr);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Hàm loại bỏ thẻ HTML khỏi nội dung
function stripHtml(html = '') {
    return html.replace(/<[^>]*>/g, '');
}

// Hàm lấy danh sách bài viết từ API
const fetchBlogs = async () => {
    loading.value = true;
    try {
        const res = await $api(`/blogs`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        // Sắp xếp bài viết theo ngày tạo và lấy 3 bài mới nhất
        blogPosts.value = res.data
            .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
            .slice(0, 3)
            .map(g => ({
                id: g.id,
                title: g.title,
                thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
                excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
                url: `/chia-se-kinh-nghiem/${g.slug}`,
                created_at: formatDate(g.created_at)
            }));
    } catch (e) {
        console.error('Error: ', e);
    } finally {
        loading.value = false;
    }
};

// Gọi hàm lấy bài viết khi component được mount
onMounted(() => {
    fetchBlogs();
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho danh sách thẻ bài viết */
.blog-post-tags {
    list-style: none;
    padding: 0 20px;
    margin: 10px 0 0;
    font-size: 13px;
    color: #888;
}

/* CSS giới hạn số dòng cho tiêu đề bài viết */
.post-title {
    display: -webkit-box;
    -webkit-line-clamp: 1; /* hoặc 2 hoặc 4 tuỳ bạn */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* CSS cho nội dung bài viết */
.post-content {
    padding: 15px;
}

/* CSS cho box bài viết */
.blog-post {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Hiệu ứng hover cho box bài viết */
.blog-post:hover {
    transform: translateY(-5px);
}

/* CSS cho ảnh đại diện bài viết */
.post-img {
    position: relative;
    overflow: hidden;
    border-radius: 8px 8px 0 0;
    height: 250px;
}

/* CSS cho icon hiển thị khi hover */
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

/* Hiển thị icon khi hover */
.custom-hover-icon:hover .hover-icon {
    opacity: 1;
}
</style>
