<template>
    <Loading :is-loading="loading" />
    <!-- Content -->
    <div class="container" style="margin-top: 50px">
        <div class="blog-page">
            <div class="row">
                <!-- Blog Posts -->
                <div class="col-lg-9 col-md-8 padding-right-30">
                    <div class="row margin-bottom-25" style="display: flex; align-items: flex-end">
                        <div class="col-md-6 col-xs-12">
                            <h2>Danh sách bài viết</h2>
                            <span>Gốc chia sẻ</span>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <SortByBlogs
                                :categories="categories"
                                :selected-category="selectedCategory"
                                @update:selectedCategory="
                                    val => {
                                        selectedCategory = val;
                                        // lọc lại blog
                                        fetchBlogs(1, selectedCategory); // tải lại blog theo category
                                    }
                                "
                            />
                        </div>
                    </div>
                    <div v-if="loading" class="text-center p-5">
                        <p>Đang tải bài viết...</p>
                    </div>

                    <!-- Kết quả tìm kiếm -->
                    <div v-if="searchKeyword">
                        <h4 class="headline margin-top-25">Kết quả tìm kiếm cho "{{ searchKeyword }}"</h4>
                        <div v-if="blogPosts.length > 0" class="row">
                            <!-- hiển thị kết quả tìm kiếm -->
                            <div v-for="post in blogPosts" :key="post.id" class="col-md-6 col-sm-12 mb-4">
                                <div class="blog-post">
                                    <NuxtLink :to="post.url" class="post-img">
                                        <img :src="post.thumbnail" :alt="post.title" />
                                    </NuxtLink>
                                    <div class="post-content">
                                        <h3>
                                            <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
                                        </h3>
                                        <ul class="post-meta">
                                            <li>{{ post.created_at }}</li>
                                            <li><a href="#">Chia sẻ kinh nghiệm</a></li>
                                        </ul>
                                        <div class="post-excerpt">
                                            <p v-html="post.excerpt"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-gray-500">Không tìm thấy bài viết phù hợp với từ khóa "{{ searchKeyword }}"</p>
                        </div>
                    </div>

                    <!-- ✅ Thêm phần này để hiển thị blog mặc định nếu không có search -->
                    <div v-else class="row">
                        <div v-for="post in blogPosts" :key="post.id" class="col-md-6 col-sm-12 mb-4">
                            <div class="blog-post">
                                <NuxtLink :to="post.url" class="post-img">
                                    <img :src="post.thumbnail" :alt="post.title" />
                                </NuxtLink>
                                <div class="post-content">
                                    <h3>
                                        <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
                                    </h3>
                                    <ul class="post-meta">
                                        <li>{{ post.created_at }}</li>
                                        <li><a href="#">Chia sẻ kinh nghiệm</a></li>
                                    </ul>
                                    <div class="post-excerpt">
                                        <p v-html="post.excerpt"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Phân trang -->
                    <div class="pagination-container margin-bottom-40" v-if="totalPages > 1">
                        <nav class="pagination">
                            <ul>
                                <li v-if="currentPage > 1">
                                    <a href="#" @click.prevent="goToPage(currentPage - 1)">
                                        <i class="sl sl-icon-arrow-left"></i>
                                    </a>
                                </li>
                                <li v-for="page in totalPages" :key="page">
                                    <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="goToPage(page)">
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

                <!-- Sidebar -->
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar right" style="margin-top: 60px">
                        <!-- Tìm kiếm -->
                        <div class="widget">
                            <h3 class="margin-top-0">Tìm kiếm</h3>
                            <div class="search-blog-input">
                                <input
                                    type="text"
                                    class="search-field"
                                    placeholder="Gõ và enter..."
                                    v-model="searchKeyword"
                                    @keyup.enter="searchBlogs(searchKeyword)"
                                />
                            </div>
                        </div>

                        <!-- Popular Posts -->
                        <div class="widget margin-top-40">
                            <h3>Bài viết phổ biến</h3>
                            <ul class="widget-tabs">
                                <li v-for="post in popularPosts" :key="post.id">
                                    <div class="widget-content">
                                        <div class="widget-thumb">
                                            <NuxtLink :to="post.url">
                                                <img :src="post.thumbnail" :alt="post.title" />
                                                <span class="hover-icon"><i class="fa fa fa-search-plus"></i></span>
                                            </NuxtLink>
                                        </div>
                                        <div class="widget-text">
                                            <h5>
                                                <NuxtLink :to="post.url">{{ post.title }}</NuxtLink>
                                            </h5>
                                            <span>{{ post.created_at }}</span>
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
import { onMounted, ref } from 'vue';
import SortByBlogs from '~/components/partials/SortBy-Blogs.vue';

const categories = ref([]);
const loading = ref(false);
const searchKeyword = ref('');
const { $api } = useNuxtApp();
const blog = ref(null);

const blogPosts = ref([]);
const popularPosts = ref([]);
const currentPage = ref(1);
const route = useRoute();
const totalPages = ref(1);
const commentsKey = ref(0);
const reloadComments = () => {
    commentsKey.value += 1;
};
const baseUrl = useRuntimeConfig().public.baseUrl;
const goToPage = page => {
    if (page !== currentPage.value) {
        currentPage.value = page;
        fetchBlogs(page);
    }
};
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
        year: 'numeric'
    });
}

const FetchPopularPosts = async () => {
    try {
        const res = await $api(`/blogs/popular`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        console.log(res);
        popularPosts.value = res.map(g => ({
            id: g.id,
            title: g.title,
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`
        }));
    } catch (e) {
        console.log('sai o dau do', e);
    }
};
const socialLinks = [
    { id: 1, name: 'facebook', icon: 'icon-facebook', url: '#' },
    { id: 2, name: 'twitter', icon: 'icon-twitter', url: '#' },
    { id: 3, name: 'gplus', icon: 'icon-gplus', url: '#' },
    { id: 4, name: 'linkedin', icon: 'icon-linkedin', url: '#' }
];
const fetchBlogs = async (page = 1, selectedCategory = '') => {
    loading.value = true;
    try {
        const url = selectedCategory ? `/blogs?category=${encodeURIComponent(selectedCategory)}&page=${page}` : `/blogs?page=${page}`;
        const res = await $api(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const mapped = res.data.map(g => ({
            id: g.id,
            title: g.title,
            category: g.category?.toString() || '',
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`,
            created_at: formatDate(g.created_at)
        }));
        console.log('fetchBlogs', res);
        blogPosts.value = mapped;
        allBlogs.value = [...mapped];

        categories.value = res.categories || [];

        currentPage.value = res.current_page || 1;
        totalPages.value = res.last_page || 1;
    } catch (e) {
        console.log('sai o dau do', e);
    } finally {
        loading.value = false;
    }
};

const searchBlogs = async (keyWord, page = 1, perPage = 5) => {
    try {
        const res = await $api(`/blogs?search=${encodeURIComponent(keyWord)}&page=${page}&per_page=${perPage}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        blogPosts.value = res.data.map(g => ({
            id: g.id,
            title: g.title,
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`
        }));
        console.log('searchBlogs', res);
        currentPage.value = res.current_page || 1;
        totalPages.value = res.last_page || 1;
    } catch (e) {
        console.log('searchBlogs error', e);
    }
};
onMounted(async () => {
    const keyWord = route.query.search;
    const page = route.query.page || 1;

    if (keyWord) {
        // Nếu đang tìm kiếm bài viết
        await searchBlogs(keyWord, page);
    } else {
        // Nếu đang xem chi tiết 1 bài viết
        await fetchBlogs(1);
        await FetchPopularPosts();
        if (blog.value && blog.value.id) {
            await fetchRelatedPosts(blog.value.id);
            await nextTick();
            reloadComments();
        }
    }
});
function stripHtml(html = '') {
    return html.replace(/<[^>]*>/g, '');
}
const handleFilter = () => {
    if (!selectedCategory.value) {
        blogPosts.value = allBlogs.value;
    } else {
        blogPosts.value = allBlogs.value.filter(blog => blog.category === selectedCategory.value);
    }
};
const selectedCategory = ref('');
const allBlogs = ref([]);
</script>

<style scoped>
.titlebar {
    margin-bottom: 10px;
}
.blog-post {
    position: relative;
    height: 450px; /* Chiều cao tổng thể của bài viết */
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
}

.post-img img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.post-content {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Giới hạn excerpt */
.post-excerpt {
    max-height: 80px;
    overflow: hidden;
    position: relative;
    margin-bottom: 10px;
}

.post-excerpt::after {
}
.post-excerpt p {
    display: -webkit-box;
    -webkit-line-clamp: 2; /* hoặc 2 hoặc 4 tuỳ bạn */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 767px) {
    .blog-post {
        height: auto;
        overflow: visible;
    }

    .post-excerpt {
        max-height: none;
        margin-bottom: 0;
    }

    .post-excerpt::after {
        display: none;
    }

    .read-more-wrapper {
        position: relative;
        bottom: auto;
        text-align: left;
        margin-top: 10px;
    }

    .read-more {
        background-color: #ff6600;
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 4px;
    }
}
.hover-icon i {
    font-size: 30px;
    color: #fff;
}
.hover-icon small {
    display: block;
    font-size: 12px;
    margin-top: 5px;
}
.post-content h3 {
    display: -webkit-box;
    -webkit-line-clamp: 1; /* hoặc 2 hoặc 4 tuỳ bạn */
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
