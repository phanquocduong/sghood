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
                    href="listings-list-with-sidebar.html"
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
const config = useRuntimeConfig();
defineProps(['districts']);
</script>

<style scoped>
.img-box {
    background-size: cover;
    background-position: center;
}
</style>
