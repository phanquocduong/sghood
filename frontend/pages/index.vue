<template>
    <div>
        <!-- Banner luôn hiển thị -->
        <SearchBanner
            :search="search"
            :districts="districts.map(d => d.name)"
            :price-options="priceOptions"
            @update:search="search = $event"
            @search="handleSearch"
        />

        <!-- Hiệu ứng loading -->
        <Loading :is-loading="isLoading" />

        <!-- Nội dung chỉ hiển thị khi load xong -->
        <div>
            <SectionFeaturedDistricts :districts="districts" />
            <SectionFeaturedMotels />

            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <h2 class="headline centered margin-top-80">
                            <strong class="headline-with-separator">Tìm Phòng Trọ Dễ Dàng Chỉ Với Vài Bước</strong>
                            <span class="margin-top-25">
                                Chọn phòng phù hợp, đặt lịch xem trực tiếp và ký hợp đồng an tâm chỉ trong vài bước đơn giản.
                            </span>
                        </h2>
                    </div>
                </div>

                <div class="row icons-container">
                    <div class="col-md-4">
                        <div class="icon-box-2 with-line">
                            <i class="im im-icon-Map2"></i>
                            <h3>Tìm Phòng Phù Hợp</h3>
                            <p>Sử dụng bộ lọc để tìm trọ theo khu vực, giá, diện tích, tiện ích...</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box-2 with-line">
                            <i class="im im-icon-Mail-withAtSign"></i>
                            <h3>Đặt Lịch Xem Phòng</h3>
                            <p>Gửi yêu cầu hẹn giờ xem phòng với Trọ Việt ngay trên hệ thống.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box-2">
                            <i class="im im-icon-Checked-User"></i>
                            <h3>Ký Hợp Đồng & Đặt Cọc</h3>
                            <p>Sau khi xem phòng ưng ý, tiến hành đặt phòng, ký hợp đồng và đặt cọc nhanh chóng.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Blog Posts -->
            <section class="fullwidth border-top margin-top-70 padding-top-75 padding-bottom-75" data-background-color="#fff">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="headline centered margin-bottom-50">
                                <strong class="headline-with-separator">Bài Viết Mới</strong>
                            </h3>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Blog Post Item -->
                        <div class="col-md-4">
                            <a href="pages-blog-post.html" class="blog-compact-item-container">
                                <div class="blog-compact-item">
                                    <img src="/images/blog-compact-post-01.jpg" alt="" />
                                    <span class="blog-item-tag">Tips</span>
                                    <div class="blog-compact-item-content">
                                        <ul class="blog-post-tags">
                                            <li>22 August 2019</li>
                                        </ul>
                                        <h3>Hotels for All Budgets</h3>
                                        <p>
                                            Sed sed tristique nibh iam porta volutpat finibus. Donec in aliquet urneget mattis lorem.
                                            Pellentesque pellentesque.
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Blog post Item / End -->

                        <!-- Blog Post Item -->
                        <div class="col-md-4">
                            <a href="pages-blog-post.html" class="blog-compact-item-container">
                                <div class="blog-compact-item">
                                    <img src="/images/blog-compact-post-02.jpg" alt="" />
                                    <span class="blog-item-tag">Tips</span>
                                    <div class="blog-compact-item-content">
                                        <ul class="blog-post-tags">
                                            <li>18 August 2019</li>
                                        </ul>
                                        <h3>The 50 Greatest Street Arts In London</h3>
                                        <p>
                                            Sed sed tristique nibh iam porta volutpat finibus. Donec in aliquet urneget mattis lorem.
                                            Pellentesque pellentesque.
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Blog post Item / End -->

                        <!-- Blog Post Item -->
                        <div class="col-md-4">
                            <a href="pages-blog-post.html" class="blog-compact-item-container">
                                <div class="blog-compact-item">
                                    <img src="/images/blog-compact-post-03.jpg" alt="" />
                                    <span class="blog-item-tag">Tips</span>
                                    <div class="blog-compact-item-content">
                                        <ul class="blog-post-tags">
                                            <li>10 August 2019</li>
                                        </ul>
                                        <h3>The Best Cofee Shops In Sydney Neighborhoods</h3>
                                        <p>
                                            Sed sed tristique nibh iam porta volutpat finibus. Donec in aliquet urneget mattis lorem.
                                            Pellentesque pellentesque.
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!-- Blog post Item / End -->

                        <div class="col-md-12 centered-content">
                            <a href="pages-blog.html" class="button border margin-top-10">Xem thêm</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Recent Blog Posts / End -->
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const { $api } = useNuxtApp();
const router = useRouter();

const search = ref({ keyword: '', district: '', priceRange: '' });
const districts = ref([]);
const isLoading = ref(true);

const priceOptions = ref([
    { value: '', label: 'Tất cả mức giá' },
    { value: 'under_1m', label: 'Dưới 1 triệu' },
    { value: '1m_2m', label: '1 - 2 triệu' },
    { value: '2m_3m', label: '2 - 3 triệu' },
    { value: '3m_5m', label: '3 - 5 triệu' },
    { value: 'over_5m', label: 'Trên 5 triệu' }
]);

const handleSearch = () => {
    router.push({
        path: '/danh-sach-nha-tro',
        query: {
            keyword: search.value.keyword || undefined,
            district: search.value.district || undefined,
            priceRange: search.value.priceRange || undefined
        }
    });
};

onMounted(async () => {
    requestAnimationFrame(async () => {
        try {
            const response = await $api('/districts', { method: 'GET' });
            districts.value = response.data;
        } catch (error) {
            console.error('Lỗi khi tải danh sách quận:', error);
        } finally {
            isLoading.value = false;
        }
    });
});
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-overlay p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
