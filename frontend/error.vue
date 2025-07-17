<template>
    <Loading :is-loading="loading" />

    <div v-if="loading" class="text-center p-5">
        <p>Đang tải...</p>
    </div>

    <div v-else>
        <div id="titlebar">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>{{ error.statusCode || 404 }} - Không tìm thấy</h2>

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

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <section id="not-found" class="center">
                        <h2>{{ error.statusCode || 404 }} <i class="fa fa-question-circle"></i></h2>
                        <p>
                            {{ error.statusCode === 404 ? 'Không tìm thấy trang bạn yêu cầu' : error.message || 'Đã có lỗi xảy ra' }}
                        </p>

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
import { ref, onMounted } from 'vue';

// Không dùng layout
definePageMeta({ layout: false });

// Dùng loading
const loading = ref(true);
const error = useError();

onMounted(() => {
    // Giả lập loading (chờ 800ms rồi mới hiển thị nội dung lỗi)
    setTimeout(() => {
        loading.value = false;
    }, 800);
});
</script>
<style>
p {
    font-size: 20px;
}
</style>
