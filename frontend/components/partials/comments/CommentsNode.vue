<template>
    <li v-if="comment">
        <!-- Avatar của người bình luận -->
        <div class="avatar">
            <img
                class="comment-avatar"
                style="padding: 0 0 0 0"
                :src="comment.user?.avatar ? baseUrl + comment.user.avatar : defaultAvatar"
            />
        </div>

        <!-- Nội dung bình luận -->
        <div class="comment-content">
            <div class="arrow-comment"></div>
            <!-- Hình tam giác chỉ vào nội dung bình luận -->
            <div class="comment-by">
                <!-- Hiển thị tên người dùng, thêm badge "Bạn" nếu là người dùng hiện tại -->
                <template v-if="comment.user && authStore.user && comment.user_id === authStore.user.id">
                    {{ authStore.user.name }} <span class="badge">Bạn</span>
                </template>
                <template v-else>
                    {{ comment.user?.name || 'Người dùng' }}
                    <!-- Hiển thị tên mặc định nếu không có -->
                </template>

                <!-- Thời gian bình luận -->
                <span class="date">{{ formatTimeAgo(comment.created_at) }}</span>
                <!-- Nút trả lời bình luận -->
                <a href="#" class="reply" @click.prevent="handleClickReply"> <i class="fa fa-reply"></i> Trả lời </a>
            </div>
            <!-- Nội dung bình luận -->
            <p>{{ comment.content }}</p>
        </div>
        <!-- Form trả lời bình luận -->
        <div v-if="showReplay" class="reply-box">
            <!-- Ô nhập nội dung trả lời -->
            <textarea v-model="ReplayContent" placeholder="Nhập trả lời..."></textarea>
            <!-- Nút gửi trả lời -->
            <button
                type="button"
                class="submit button"
                @click="() => HandleReply(comment.blog_id)"
                :disabled="loading"
                style="margin-top: 10px"
            >
                <!-- Hiển thị spinner khi đang gửi -->
                <span v-if="loading" class="spinner"></span>
                {{ loading ? ' Đang gửi...' : 'Gửi' }}
            </button>
        </div>
        <!-- Hiển thị đệ quy các bình luận con -->
        <ul v-if="comment.children && comment.children.length">
            <CommentsNode
                v-for="child in comment.children"
                :key="child.id"
                :comment="child"
                :blog_id="comment.blog_id"
                @refresh="fetchComments"
            />
        </ul>
    </li>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'; // Import store quản lý thông tin người dùng
import CommentsNode from './CommentsNode.vue'; // Import đệ quy chính component này
import { formatTimeAgo } from '~/utils/time'; // Import hàm định dạng thời gian
import { ref } from 'vue';
import { useAppToast } from '~/composables/useToast'; // Import composable để hiển thị thông báo

// Cấu hình runtime để lấy baseUrl
const config = useRuntimeConfig();
const baseUrl = config.public.baseUrl;

// Định nghĩa props
const props = defineProps({
    comment: {
        type: Object,
        required: true,
        default: () => ({}) // Bình luận hiện tại
    }
});

// Định nghĩa sự kiện emit
const emit = defineEmits(['refresh']);

// Hàm bật/tắt ô trả lời
const toggleReply = () => {
    showReplay.value = !showReplay.value;
};

// Khởi tạo các biến trạng thái
const showReplay = ref(false); // Trạng thái hiển thị ô trả lời
const loading = ref(false); // Trạng thái loading khi gửi trả lời
const ReplayContent = ref(''); // Nội dung trả lời
const toast = useAppToast(); // Hàm hiển thị thông báo toast
const { $api } = useNuxtApp(); // API từ Nuxt
const authStore = useAuthStore(); // Store quản lý thông tin người dùng
const defaultAvatar = '/images/sghood_logo1.png'; // Avatar mặc định

// Hàm gửi trả lời bình luận
const HandleReply = async blog_id => {
    if (ReplayContent.value.trim() === '') {
        toast('Vui lòng nhập nội dung bình luận'); // Thông báo nếu nội dung rỗng
        return;
    }
    loading.value = true; // Bật trạng thái loading
    try {
        const res = await $api(`/blogs/${blog_id}/replay-comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Token CSRF
            },
            body: JSON.stringify({
                content: ReplayContent.value,
                user_id: authStore.user?.id,
                parent_id: props.comment.id // ID của bình luận cha
            })
        });
        showReplay.value = false; // Ẩn ô trả lời
        ReplayContent.value = ''; // Xóa nội dung sau khi gửi
        emit('refresh'); // Phát sự kiện để làm mới danh sách bình luận
    } catch (error) {
        console.error('Error handling reply:', error);
    } finally {
        loading.value = false; // Tắt trạng thái loading
    }
};

// Hàm xử lý khi click nút trả lời
const handleClickReply = () => {
    if (!authStore.user) {
        toast.warning('Bạn hãy đăng nhập để trả lời nhé!'); // Thông báo nếu chưa đăng nhập
        return;
    }
    toggleReply(); // Bật/tắt ô trả lời
};
</script>

<style scoped>
/* Style cho tên người bình luận */
.comment-by {
    font-weight: bold;
}

/* Style cho spinner loading */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite; /* Hiệu ứng xoay */
    margin-right: 8px;
    vertical-align: middle;
}

/* Hiệu ứng xoay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Style cho nút bị vô hiệu hóa */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Style cho bình luận */
.comment {
    display: flex;
    align-items: center; /* Căn giữa theo chiều dọc */
    gap: 15px; /* Khoảng cách giữa avatar và nội dung */
}

/* Style cho avatar bình luận */
.comment-avatar {
    padding: 0; /* Bỏ padding */
    width: 60px; /* Chiều rộng avatar */
    height: 60px; /* Chiều cao avatar */
    margin-top: 20px;
    object-fit: cover; /* Đảm bảo ảnh không bị méo */
}
</style>
