<template>
    <!-- Hiển thị component Loading khi đang tải dữ liệu -->
    <Loading :is-loading="loading" />

    <!-- Container chính của trang, margin-top để tạo khoảng cách -->
    <div class="container" style="margin-top: 50px">
        <div class="blog-page">
            <div class="row">
                <!-- Cột chính chứa danh sách bài viết -->
                <div class="col-lg-9 col-md-8 padding-right-30">
                    <!-- Tiêu đề và bộ lọc danh mục -->
                    <div class="row margin-bottom-25" style="display: flex; align-items: flex-end">
                        <div class="col-md-6 col-xs-12">
                            <h2>Danh sách bài viết</h2>
                            <span>Góc chia sẻ</span>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <!-- Component SortByBlogs để chọn danh mục bài viết -->
                            <SortByBlogs
                                :categories="categories"
                                :selected-category="selectedCategory"
                                @update:selectedCategory="
                                    val => {
                                        selectedCategory = val;
                                        // Gọi hàm fetchBlogs để tải lại danh sách bài viết theo danh mục
                                        fetchBlogs(1, selectedCategory);
                                    }
                                "
                            />
                        </div>
                    </div>

                    <!-- Hiển thị thông báo đang tải khi loading = true -->
                    <div v-if="loading" class="text-center p-5">
                        <p>Đang tải bài viết...</p>
                    </div>

                    <!-- Phần hiển thị kết quả tìm kiếm khi có từ khóa -->
                    <div v-if="searchKeyword">
                        <h4 class="headline margin-top-25">Kết quả tìm kiếm cho "{{ searchKeyword }}"</h4>
                        <div v-if="blogPosts.length > 0" class="row">
                            <!-- Hiển thị danh sách bài viết tìm kiếm được -->
                            <div v-for="post in blogPosts" :key="post.id" class="col-md-6 col-sm-12 mb-4">
                                <div class="blog-post">
                                    <!-- Link đến trang chi tiết bài viết -->
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
                                        <!-- Hiển thị đoạn trích bài viết -->
                                        <div class="post-excerpt">
                                            <p v-html="post.excerpt"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Thông báo khi không tìm thấy bài viết -->
                        <div v-else>
                            <p class="text-gray-500">Không tìm thấy bài viết phù hợp với từ khóa "{{ searchKeyword }}"</p>
                        </div>
                    </div>

                    <!-- Hiển thị danh sách bài viết mặc định nếu không có tìm kiếm -->
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

                    <!-- Phân trang cho danh sách bài viết -->
                    <div class="pagination-container margin-bottom-40" v-if="totalPages > 1">
                        <nav class="pagination">
                            <ul>
                                <!-- Nút quay lại trang trước -->
                                <li v-if="currentPage > 1">
                                    <a href="#" @click.prevent="goToPage(currentPage - 1)">
                                        <i class="sl sl-icon-arrow-left"></i>
                                    </a>
                                </li>
                                <!-- Hiển thị các số trang -->
                                <li v-for="page in totalPages" :key="page">
                                    <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="goToPage(page)">
                                        {{ page }}
                                    </a>
                                </li>
                                <!-- Nút chuyển sang trang tiếp theo -->
                                <li v-if="currentPage < totalPages">
                                    <a href="#" @click.prevent="goToPage(currentPage + 1)">
                                        <i class="sl sl-icon-arrow-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

                <!-- Sidebar bên phải -->
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar right" style="margin-top: 60px">
                        <!-- Widget tìm kiếm -->
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

                        <!-- Widget bài viết phổ biến -->
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

                        <!-- Widget liên kết mạng xã hội -->
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
import { useHead } from '#app';
import SortByBlogs from '~/components/partials/SortBy-Blogs.vue';

// Cấu hình SEO cho trang danh sách bài viết
useHead({
    title: 'SGHood - Chia Sẻ Kinh Nghiệm Thuê Trọ',
    meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
            hid: 'description',
            name: 'description',
            content:
                'Khám phá kinh nghiệm thuê trọ tại TP. Hồ Chí Minh với SGHood. Mẹo tìm phòng trọ, quản lý chi phí và các bài viết hữu ích.'
        },
        {
            name: 'keywords',
            content: 'SGHood, kinh nghiệm thuê trọ, nhà trọ TP. Hồ Chí Minh, mẹo thuê trọ, bài viết chia sẻ'
        },
        { name: 'author', content: 'SGHood Team' },
        // Cấu hình Open Graph cho chia sẻ mạng xã hội
        {
            property: 'og:title',
            content: 'SGHood - Chia Sẻ Kinh Nghiệm Thuê Trọ'
        },
        {
            property: 'og:description',
            content:
                'Khám phá kinh nghiệm thuê trọ tại TP. Hồ Chí Minh với SGHood. Mẹo tìm phòng trọ, quản lý chi phí và các bài viết hữu ích.'
        },
        { property: 'og:type', content: 'website' },
        { property: 'og:url', content: 'https://sghood.com.vn/chia-se-kinh-nghiem' }
    ]
});

// Khởi tạo các biến reactive để quản lý trạng thái
const categories = ref([]); // Danh sách danh mục bài viết
const loading = ref(false); // Trạng thái đang tải
const searchKeyword = ref(''); // Từ khóa tìm kiếm
const { $api } = useNuxtApp(); // Sử dụng API từ Nuxt context
const blog = ref(null); // Dữ liệu bài viết chi tiết
const blogPosts = ref([]); // Danh sách bài viết
const popularPosts = ref([]); // Danh sách bài viết phổ biến
const currentPage = ref(1); // Trang hiện tại
const route = useRoute(); // Lấy thông tin route hiện tại
const totalPages = ref(1); // Tổng số trang
const commentsKey = ref(0); // Key để reload comment
const reloadComments = () => {
    commentsKey.value += 1; // Tăng key để reload comment
};
const baseUrl = useRuntimeConfig().public.baseUrl; // URL cơ sở từ cấu hình runtime

// Hàm chuyển trang
const goToPage = page => {
    if (page !== currentPage.value) {
        currentPage.value = page;
        fetchBlogs(page); // Tải lại bài viết khi chuyển trang
    }
};

// Hàm định dạng ngày tháng
function formatDate(dateStr = '') {
    if (!dateStr) return 'Không rõ ngày';

    // Xử lý định dạng ngày dạng dd-MM-yyyy
    if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
        const [day, month, year] = dateStr.split('-');
        dateStr = `${year}-${month}-${day}`; // Chuyển sang yyyy-MM-dd
    }

    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return 'Ngày không hợp lệ';

    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Hàm lấy danh sách bài viết phổ biến
const FetchPopularPosts = async () => {
    try {
        const res = await $api(`/blogs/popular`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        popularPosts.value = res.map(g => ({
            id: g.id,
            title: g.title,
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`
        }));
    } catch (e) {
        console.error('Error: ', e);
    }
};

// Danh sách liên kết mạng xã hội
const socialLinks = [
    { id: 1, name: 'facebook', icon: 'icon-facebook', url: '#' },
    { id: 2, name: 'twitter', icon: 'icon-twitter', url: '#' },
    { id: 3, name: 'gplus', icon: 'icon-gplus', url: '#' },
    { id: 4, name: 'linkedin', icon: 'icon-linkedin', url: '#' }
];

// Hàm lấy danh sách bài viết
const fetchBlogs = async (page = 1, selectedCategory = '') => {
    loading.value = true; // Bật trạng thái đang tải
    try {
        // Tạo URL API dựa trên danh mục và trang
        const url = selectedCategory ? `/blogs?category=${encodeURIComponent(selectedCategory)}&page=${page}` : `/blogs?page=${page}`;
        const res = await $api(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        // Ánh xạ dữ liệu bài viết từ API
        const mapped = res.data.map(g => ({
            id: g.id,
            title: g.title,
            category: g.category?.toString() || '',
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`,
            created_at: formatDate(g.created_at)
        }));
        blogPosts.value = mapped;
        allBlogs.value = [...mapped];

        categories.value = res.categories || []; // Lưu danh sách danh mục
        currentPage.value = res.current_page || 1; // Cập nhật trang hiện tại
        totalPages.value = res.last_page || 1; // Cập nhật tổng số trang
    } catch (e) {
        console.error('Error: ', e);
    } finally {
        loading.value = false; // Tắt trạng thái đang tải
    }
};

// Hàm tìm kiếm bài viết
const searchBlogs = async (keyWord, page = 1, perPage = 5) => {
    try {
        // Gọi API tìm kiếm với từ khóa
        const res = await $api(`/blogs?search=${encodeURIComponent(keyWord)}&page=${page}&per_page=${perPage}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        // Ánh xạ dữ liệu bài viết tìm kiếm được
        blogPosts.value = res.data.map(g => ({
            id: g.id,
            title: g.title,
            thumbnail: g.thumbnail?.startsWith('/storage') ? baseUrl + g.thumbnail : g.thumbnail,
            excerpt: g.excerpt || stripHtml(g.content).slice(0, 100) + '...',
            url: `/chia-se-kinh-nghiem/${g.slug}`
        }));
        currentPage.value = res.current_page || 1;
        totalPages.value = res.last_page || 1;
    } catch (e) {
        console.error('searchBlogs error', e);
    }
};

// Hàm xử lý khi trang được tải
onMounted(async () => {
    const keyWord = route.query.search; // Lấy từ khóa tìm kiếm từ query
    const page = route.query.page || 1; // Lấy số trang từ query

    if (keyWord) {
        // Nếu có từ khóa, thực hiện tìm kiếm
        await searchBlogs(keyWord, page);
    } else {
        // Nếu không có từ khóa, lấy danh sách bài viết mặc định
        await fetchBlogs(1);
        await FetchPopularPosts(); // Lấy bài viết phổ biến
        if (blog.value && blog.value.id) {
            await fetchRelatedPosts(blog.value.id); // Lấy bài viết liên quan nếu đang xem chi tiết
            await nextTick();
            reloadComments(); // Reload comment
        }
    }
});

// Hàm xóa thẻ HTML khỏi nội dung
function stripHtml(html = '') {
    return html.replace(/<[^>]*>/g, '');
}

// Biến reactive để lưu danh mục được chọn và danh sách tất cả bài viết
const selectedCategory = ref('');
const allBlogs = ref([]);
</script>

<style scoped>
/* CSS cho tiêu đề trang */
.titlebar {
    margin-bottom: 10px;
}

/* CSS cho mỗi bài viết */
.blog-post {
    position: relative;
    height: 450px; /* Chiều cao cố định cho bài viết */
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
}

/* CSS cho hình ảnh bài viết */
.post-img img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

/* CSS cho nội dung bài viết */
.post-content {
    flex: 1;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Giới hạn chiều cao và hiển thị đoạn trích */
.post-excerpt {
    max-height: 80px;
    overflow: hidden;
    position: relative;
    margin-bottom: 10px;
}

/* Giới hạn số dòng hiển thị cho đoạn trích */
.post-excerpt p {
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Hiển thị tối đa 2 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* CSS responsive cho màn hình nhỏ */
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

/* CSS cho icon hover trên hình ảnh */
.hover-icon i {
    font-size: 30px;
    color: #fff;
}

.hover-icon small {
    display: block;
    font-size: 12px;
    margin-top: 5px;
}

/* Giới hạn số dòng hiển thị cho tiêu đề bài viết */
.post-content h3 {
    display: -webkit-box;
    -webkit-line-clamp: 1; /* Hiển thị tối đa 1 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
