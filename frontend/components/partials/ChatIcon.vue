<template>
    <!-- Icon chat, khi click sẽ phát sự kiện 'toggle' -->
    <div class="chat-icon" @click="$emit('toggle')">
        <!-- Sử dụng icon MessageSquare từ thư viện lucide-vue-next -->
        <MessageSquare class="icon" />
        <!-- Hiển thị số tin nhắn chưa đọc nếu có -->
        <span v-if="unreadMessages > 0" class="unread-badge-number">
            <!-- Hiển thị '9+' nếu số tin nhắn chưa đọc lớn hơn 9, ngược lại hiển thị số thực -->
            {{ unreadMessages > 9 ? '9+' : unreadMessages }}
        </span>
    </div>
</template>

<script setup>
import { MessageSquare } from 'lucide-vue-next'; // Import icon MessageSquare từ thư viện lucide-vue-next

// Định nghĩa prop để nhận số lượng tin nhắn chưa đọc
defineProps({
    unreadMessages: Number
});
</script>

<style scoped>
/* Style cho icon chat */
.chat-icon {
    position: fixed; /* Vị trí cố định ở góc dưới bên phải màn hình */
    bottom: 90px; /* Nằm trên nút back-to-top */
    right: 24px; /* Cách lề phải 24px */
    z-index: 10000; /* Đảm bảo icon luôn nằm trên các phần tử khác */
    background-color: #f91942; /* Màu nền đỏ */
    color: white; /* Màu icon trắng */
    border-radius: 50%; /* Bo tròn thành hình tròn */
    width: 46px; /* Chiều rộng icon */
    height: 46px; /* Chiều cao icon */
    display: flex; /* Sử dụng flex để căn giữa nội dung */
    align-items: center; /* Căn giữa theo chiều dọc */
    justify-content: center; /* Căn giữa theo chiều ngang */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3); /* Hiệu ứng bóng */
    cursor: pointer; /* Con trỏ chuột kiểu pointer khi hover */
    transition: transform 0.2s ease; /* Hiệu ứng phóng to khi hover */
    margin-right: 2px; /* Khoảng cách lề phải */
}

/* Hiệu ứng phóng to khi hover */
.chat-icon:hover {
    transform: scale(1.1); /* Phóng to 110% */
}

/* Style cho icon bên trong */
.icon {
    width: 28px; /* Chiều rộng icon */
    height: 28px; /* Chiều cao icon */
}

/* Style cho badge hiển thị số tin nhắn chưa đọc */
.unread-badge-number {
    position: absolute; /* Vị trí tuyệt đối so với chat-icon */
    top: -2px; /* Cách đỉnh 2px */
    right: -2px; /* Cách phải 2px */
    background-color: #ff3b30; /* Màu nền đỏ sáng */
    color: white; /* Màu chữ trắng */
    font-size: 10px; /* Cỡ chữ nhỏ */
    font-weight: bold; /* Chữ đậm */
    min-width: 16px; /* Chiều rộng tối thiểu */
    height: 16px; /* Chiều cao */
    line-height: 16px; /* Căn giữa chữ theo chiều dọc */
    text-align: center; /* Căn giữa chữ theo chiều ngang */
    padding: 0 4px; /* Khoảng đệm ngang */
    border-radius: 9999px; /* Bo tròn thành hình tròn */
    box-shadow: 0 0 0 1px white; /* Viền trắng xung quanh badge */
    animation: pulse 1.2s infinite; /* Hiệu ứng nhấp nháy */
    white-space: nowrap; /* Ngăn chữ xuống dòng */
}

/* Hiệu ứng nhấp nháy cho badge */
@keyframes pulse {
    0% {
        transform: scale(1); /* Kích thước bình thường */
        opacity: 1; /* Độ mờ tối đa */
    }
    50% {
        transform: scale(1.3); /* Phóng to 130% */
        opacity: 0.7; /* Độ mờ giảm */
    }
    100% {
        transform: scale(1); /* Trở về kích thước bình thường */
        opacity: 1; /* Độ mờ tối đa */
    }
}
</style>
