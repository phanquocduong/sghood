<template>
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
                        <div ref="carousel" class="simple-slick-carousel dots-nav">
                            <div v-for="motel in motels" :key="motel.id" class="carousel-item">
                                <NuxtLink :to="`/nha-tro/${motel.slug}`" class="listing-item-container">
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

const { $api } = useNuxtApp();
const config = useRuntimeConfig();
const motels = ref([]);
const carousel = ref(null);

onMounted(async () => {
    try {
        const response = await $api('/motels/featured', { method: 'GET' });
        motels.value = response.data;

        // Đợi DOM được render hoàn toàn
        await nextTick();

        // Chỉ khởi tạo Slick nếu có dữ liệu và DOM sẵn sàng
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
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 3
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

const formatPrice = price => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};
</script>

<style lang="scss" scoped></style>
