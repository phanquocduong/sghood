<!-- Template hiển thị danh sách khu vực nổi bật -->
<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="headline centered margin-bottom-40 margin-top-80">
                    <strong class="headline-with-separator">Khu Vực Nổi Bật</strong>
                </h3>
            </div>

            <!-- Hiển thị từng khu vực -->
            <div v-for="(district, index) in districts" :key="district.id" :class="index < 2 ? 'col-md-6' : 'col-md-4'">
                <!-- Liên kết đến trang danh sách nhà trọ theo quận -->
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

// Lấy cấu hình và router
const config = useRuntimeConfig();
const router = useRouter();

// Định nghĩa props
defineProps(['districts']);

// Hàm xử lý chuyển hướng đến trang danh sách nhà trọ theo quận
const handleRedirect = district => {
    router.push({
        path: '/danh-sach-nha-tro',
        query: {
            district: district
        }
    });
};
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho box hình ảnh khu vực */
.img-box {
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border-radius: 4px;
    cursor: pointer;
}

/* Hiệu ứng hover cho box hình ảnh */
.img-box:hover {
    transform: scale(1.01);
}

/* CSS cho nội dung trong box hình ảnh */
.img-box-content {
    position: absolute;
    bottom: 20px;
    left: 20px;
    color: white;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    transition: all 0.3s ease;
}

/* Hiệu ứng hover cho nội dung box */
.img-box:hover .img-box-content {
    transform: translateY(-10px);
    background: rgba(0, 0, 0, 0.5);
    padding: 10px 15px;
    border-radius: 4px;
}
</style>
