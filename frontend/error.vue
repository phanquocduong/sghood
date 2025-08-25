<template>
    <!-- Hiển thị component Loading khi trạng thái loading là true -->
    <Loading :is-loading="loading" />

    <!-- Hiển thị thông báo đang tải khi loading là true -->
    <div v-if="loading" class="text-center p-5">
        <p>Đang tải...</p>
    </div>

    <!-- Hiển thị nội dung lỗi khi loading là false -->
    <div v-else>
        <!-- Phần tiêu đề của trang lỗi -->
        <div id="titlebar">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Hiển thị mã lỗi (mặc định là 404 nếu không có statusCode) -->
                        <h2>{{ error.statusCode || 404 }} - Không tìm thấy</h2>

                        <!-- Thanh điều hướng breadcrumb -->
                        <nav id="breadcrumbs">
                            <ul>
                                <li><NuxtLink to="/">Trang chủ</NuxtLink></li>
                                <li>Lỗi {{ error.statusCode || '404' }}</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần nội dung chính của trang lỗi -->
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <section id="not-found" class="center">
                        <!-- Hiển thị mã lỗi và biểu tượng dấu hỏi -->
                        <h2>{{ error.statusCode || 404 }} <i class="fa fa-question-circle"></i></h2>
                        <!-- Hiển thị thông báo lỗi tùy thuộc vào mã lỗi -->
                        <p>
                            {{ error.statusCode === 404 ? 'Không tìm thấy trang bạn yêu cầu' : error.message || 'Đã có lỗi xảy ra' }}
                        </p>

                        <!-- Nút quay lại trang chủ -->
                        <div class="row">
                            <div class="col-lg-8 col-lg-offset-2 text-center" style="margin-top: 50px">
                                <NuxtLink to="/" class="button">Quay lại Trang chủ</NuxtLink>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'; // Nhập ref và onMounted từ Vue

// Không sử dụng layout mặc định cho trang lỗi
definePageMeta({ layout: false });

// Biến trạng thái loading để kiểm soát hiển thị giao diện tải
const loading = ref(true);
// Lấy thông tin lỗi từ composable useError của Nuxt
const error = useError();

// Xử lý khi component được gắn vào DOM
onMounted(() => {
    // Giả lập thời gian tải (800ms) trước khi hiển thị nội dung lỗi
    setTimeout(() => {
        loading.value = false; // Tắt trạng thái loading
    }, 800);
});
</script>

<style>
/* Tùy chỉnh kích thước chữ cho thẻ p trong trang lỗi */
p {
    font-size: 20px;
}
</style>
