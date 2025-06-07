<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="headline centered margin-bottom-35 margin-top-70">
                    <strong class="headline-with-separator">Khu Vực Nổi Bật</strong>
                </h3>
            </div>

            <div v-for="(district, index) in districts" :key="district.id" :class="index < 2 ? 'col-md-6' : 'col-md-4'">
                <a
                    @click.prevent="handleRedirect(district.name)"
                    class="img-box alternative-imagebox"
                    :style="{
                        backgroundImage: `url(${config.public.baseUrl}${district.image})`
                    }"
                >
                    <div class="img-box-content visible">
                        <h4>{{ district.name }}</h4>
                        <span>{{ district.motel_count }} nhà trọ</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
const config = useRuntimeConfig();
const router = useRouter(); // Khai báo router từ vue-router
defineProps(['districts']);

const handleRedirect = district => {
    router.push({
        path: '/danh-sach-nha-tro',
        query: {
            area: district
        }
    });
};
</script>

<style scoped>
.img-box {
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border-radius: 8px;
    cursor: pointer; /* Thêm con trỏ để báo hiệu có thể click */
}

.img-box:hover {
    transform: scale(1.02);
    filter: brightness(0.8);
}

.img-box-content {
    position: absolute;
    bottom: 20px;
    left: 20px;
    color: white;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    transition: all 0.3s ease;
}

.img-box:hover .img-box-content {
    transform: translateY(-10px);
    background: rgba(0, 0, 0, 0.4);
    padding: 10px 15px;
    border-radius: 5px;
}
</style>
