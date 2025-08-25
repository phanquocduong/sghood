<!-- Template cho điều hướng chính (desktop) -->
<template>
    <!-- Thanh điều hướng chính -->
    <nav id="navigation" class="style-1">
        <ul id="responsive">
            <!-- Mục điều hướng: Trang chủ -->
            <li>
                <NuxtLink to="/" active-class="current" class="inline-flex items-center">Trang Chủ</NuxtLink>
            </li>
            <!-- Mục điều hướng: Nhà trọ -->
            <li>
                <NuxtLink to="/danh-sach-nha-tro" active-class="current" class="inline-flex items-center">Nhà Trọ</NuxtLink>
            </li>
            <!-- Mục điều hướng: Kinh nghiệm -->
            <li>
                <NuxtLink to="/chia-se-kinh-nghiem" active-class="current" class="inline-flex items-center">Kinh Nghiệm</NuxtLink>
            </li>
            <!-- Mục điều hướng: Về chúng tôi -->
            <li>
                <NuxtLink to="/ve-chung-toi" active-class="current" class="inline-flex items-center">Về Chúng Tôi</NuxtLink>
            </li>
            <!-- Mục điều hướng: Liên hệ -->
            <li>
                <NuxtLink to="/lien-he" active-class="current" class="inline-flex items-center">Liên Hệ</NuxtLink>
            </li>
        </ul>
    </nav>
    <div class="clearfix"></div>
    <!-- Xóa float để đảm bảo bố cục -->
</template>
