<!-- Template cho component phân trang -->
<template>
    <div class="pagination-container margin-top-20 margin-bottom-40">
        <!-- Hiển thị phân trang nếu có trang -->
        <nav v-if="totalPages" class="pagination">
            <ul>
                <!-- Nút trang trước -->
                <li>
                    <a href="#" :class="{ disabled: currentPage === 1 }" @click.prevent="$emit('change:page', currentPage - 1)">
                        <i class="sl sl-icon-arrow-left"></i>
                    </a>
                </li>
                <!-- Các trang số -->
                <li v-for="page in totalPages" :key="page">
                    <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="$emit('change:page', page)">
                        {{ page }}
                    </a>
                </li>
                <!-- Nút trang sau -->
                <li>
                    <a href="#" :class="{ disabled: currentPage === totalPages }" @click.prevent="$emit('change:page', currentPage + 1)">
                        <i class="sl sl-icon-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
defineProps(['currentPage', 'totalPages']); // Nhận trang hiện tại và tổng số trang
defineEmits(['change:page']); // Phát sự kiện thay đổi trang
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho nút bị vô hiệu hóa */
.disabled {
    pointer-events: none;
    opacity: 0.5;
    cursor: not-allowed; /* Biểu thị con trỏ không thể nhấn */
}
</style>
