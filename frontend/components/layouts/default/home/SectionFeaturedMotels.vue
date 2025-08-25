<!-- Template hiển thị danh sách nhà trọ nổi bật -->
<template>
    <!-- Chỉ render ở client-side để tránh lỗi SSR -->
    <ClientOnly>
        <section class="fullwidth margin-top-65 padding-top-75 padding-bottom-70" data-background-color="#f8f8f8">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="headline centered margin-bottom-40">
                            <strong class="headline-with-separator">Nhà Trọ Nổi Bật</strong>
                        </h3>
                    </div>

                    <div class="col-md-12">
                        <!-- Carousel hiển thị nhà trọ -->
                        <div ref="carousel" class="simple-slick-carousel dots-nav">
                            <div v-for="motel in motels" :key="motel.id" class="carousel-item">
                                <!-- Liên kết đến trang chi tiết nhà trọ -->
                                <NuxtLink :to="`/danh-sach-nha-tro/${motel.slug}`" class="listing-item-container">
                                    <div class="listing-item">
                                        <img :src="`${config.public.baseUrl}${motel.main_image}`" :alt="motel.name" />
                                        <div class="listing-badge now-open">Nổi bật</div>
                                        <div class="listing-item-details">
                                            <ul>
                                                <li>Còn {{ motel.room_count }} phòng trống</li>
                                            </ul>
                                        </div>
                                        <div class="listing-item-content">
                                            <span class="tag">{{ motel.district_name }}</span>
                                            <h3>{{ motel.name }}</h3>
                                            <span>{{ motel.address }}</span>
                                        </div>
                                    </div>
                                    <div class="star-rating">
                                        <div class="rating-counter">Giá từ {{ formatPrice(motel.min_price) }}/tháng</div>
                                    </div>
                                </NuxtLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </ClientOnly>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useFormatPrice } from '~/composables/useFormatPrice';

// Lấy API, cấu hình và composable format giá
const { $api } = useNuxtApp();
const config = useRuntimeConfig();
const { formatPrice } = useFormatPrice();
const motels = ref([]); // Lưu danh sách nhà trọ
const carousel = ref(null); // Tham chiếu đến carousel

// Hàm chạy khi component được mount
onMounted(async () => {
    try {
        // Gọi API để lấy danh sách nhà trọ nổi bật
        const response = await $api('/motels/featured', { method: 'GET' });
        motels.value = response.data;

        // Đợi DOM được render hoàn toàn
        await nextTick();

        // Khởi tạo Slick carousel nếu có dữ liệu và jQuery/Slick có sẵn
        if (motels.value.length > 0 && carousel.value && typeof window !== 'undefined' && window.jQuery && window.jQuery.fn.slick) {
            const $ = window.jQuery;
            $(carousel.value).slick({
                dots: true,
                arrows: true,
                infinite: true,
                speed: 500,
                slidesToShow: 3,
                slidesToScroll: 3,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        }
    } catch (error) {
        console.error('Đã xảy ra lỗi khi lấy dữ liệu nhà trọ nổi bật', error);
        motels.value = [];
    }
});
</script>

<style scoped></style>
