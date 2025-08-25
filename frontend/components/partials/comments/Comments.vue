<template>
    <section class="comments">
        <!-- Tiêu đề hiển thị số lượng bình luận -->
        <h4 class="headline margin-bottom-35">
            Bình luận <span class="comments-amount">({{ comments.length }})</span>
        </h4>
        <!-- Thông báo khi chưa có bình luận -->
        <p v-if="comments.length === 0" class="text-gray-400">Chưa có bình luận nào. Nếu muốn bình luận hãy đăng nhập nhé!</p>
        <ul>
            <!-- Lặp qua danh sách bình luận để hiển thị -->
            <template v-for="comment in comments" :key="comment.id">
                <!-- Hiển thị từng bình luận bằng component CommentsNode -->
                <CommentsNode v-if="comment" :comment="comment" :blog_id="comment.blog_id" @refresh="fetchComments" />
            </template>
            <!-- Phân trang cho bình luận -->
            <div
                class="pagination-container margin-bottom-40"
                style="display: flex; justify-content: center; margin-top: 30px"
                v-if="totalPages > 1"
            >
                <nav class="pagination">
                    <ul>
                        <!-- Nút chuyển về trang trước -->
                        <li v-if="currentPage > 1">
                            <a href="#" @click.prevent="goToPage(currentPage - 1)">
                                <i class="sl sl-icon-arrow-left"></i>
                            </a>
                        </li>
                        <!-- Danh sách các trang -->
                        <li v-for="page in totalPages" :key="page">
                            <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="goToPage(page)">
                                {{ page }}
                            </a>
                        </li>
                        <!-- Nút chuyển sang trang sau -->
                        <li v-if="currentPage < totalPages">
                            <a href="#" @click.prevent="goToPage(currentPage + 1)">
                                <i class="sl sl-icon-arrow-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <!-- Form thêm bình luận, chỉ hiển thị nếu người dùng đã đăng nhập -->
            <div id="add-review" class="add-review-box" v-if="authStore.user">
                <h3 class="listing-desc-headline margin-bottom-35">Bình luận</h3>

                <!-- Form nhập nội dung bình luận -->
                <form id="add-comment" class="add-comment">
                    <fieldset>
                        <div>
                            <label>Bình luận:</label>
                            <!-- Ô nhập nội dung bình luận -->
                            <textarea cols="40" rows="3" v-model="ReplayContent"></textarea>
                        </div>
                    </fieldset>

                    <!-- Nút gửi bình luận -->
                    <button
                        class="button"
                        @click.prevent="AddReplay(blog_id)"
                        type="submit"
                        id="submit"
                        value="Gửi tin nhắn"
                        :disabled="loading"
                        style="margin-bottom: 10px; margin-top: -10px"
                    >
                        <!-- Hiển thị spinner khi đang gửi -->
                        <span v-if="loading" class="spinner"> </span>
                        {{ loading ? ' Đang gửi...' : 'Gửi đi' }}
                    </button>
                </form>
            </div>
        </ul>
    </section>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import CommentsNode from './CommentsNode.vue'; // Import component CommentsNode để hiển thị bình luận
import { useRoute } from 'vue-router'; // Import useRoute để lấy thông tin route
import { useAppToast } from '~/composables/useToast'; // Import composable để hiển thị thông báo
import { useAuthStore } from '~/stores/auth'; // Import store để quản lý thông tin người dùng

// Khởi tạo các biến trạng thái
const comments = ref([]); // Danh sách bình luận
const { $api } = useNuxtApp(); // API từ Nuxt
const name = ref(''); // Tên người dùng
const showReplay = ref(false); // Trạng thái hiển thị ô trả lời (không sử dụng trong template)
const email = ref(''); // Email người dùng
const toast = useAppToast(); // Hàm hiển thị thông báo toast
const loading = ref(false); // Trạng thái loading khi gửi bình luận
const ReplayContent = ref(''); // Nội dung bình luận mới
const blog_id = ref(null); // ID của bài viết
const route = useRoute(); // Lấy thông tin route hiện tại
const currentPage = ref(1); // Trang hiện tại của phân trang
const totalPages = ref(1); // Tổng số trang
const slug = computed(() => route.params.slug); // Lấy slug từ params của route
const authStore = useAuthStore(); // Store quản lý thông tin người dùng

// Hàm chuyển đến trang cụ thể
const goToPage = async page => {
    if (page !== currentPage.value) {
        await fetchComments(page); // Gọi lại hàm fetchComments khi chuyển trang
    }
};

// Hàm lấy danh sách bình luận từ API
const fetchComments = async (page = 1) => {
    try {
        const res = await $api(`/blogs/${slug.value}/comments?page=${page}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        comments.value = res.data || []; // Cập nhật danh sách bình luận
        if (Array.isArray(res.data) && res.data.length > 0) {
            blog_id.value = res.data[0].blog_id; // Lấy blog_id từ bình luận đầu tiên
        }
        currentPage.value = res.meta.current_page || 1; // Cập nhật trang hiện tại
        totalPages.value = res.meta.last_page || 1; // Cập nhật tổng số trang
    } catch (error) {
        console.error('Lỗi khi fetch bình luận:', error);
    }
};

// Hàm gửi bình luận mới
const AddReplay = async blog_id => {
    if (ReplayContent.value.trim() === '') {
        toast('Vui lòng nhập nội dung bình luận'); // Thông báo nếu nội dung rỗng
        return;
    }
    loading.value = true; // Bật trạng thái loading
    try {
        const res = await $api(`/blogs/${blog_id}/send-comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Token CSRF
            },
            body: {
                content: ReplayContent.value,
                user_id: authStore.user?.id
            }
        });
        showReplay.value = false; // Ẩn ô trả lời (không sử dụng trong template)
        ReplayContent.value = ''; // Xóa nội dung sau khi gửi
        await fetchComments(); // Lấy lại danh sách bình luận
        toast.success('Bình luận đã được gửi thành công!'); // Thông báo thành công
    } catch (error) {
        console.error('Error handling reply:', error);
        toast.error('Có lỗi xảy ra khi gửi bình luận.'); // Thông báo lỗi
    } finally {
        loading.value = false; // Tắt trạng thái loading
    }
};

// Hàm lấy blog_id từ slug
const getBlogId = async slug => {
    try {
        const res = await $api(`/show/${slug}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        blog_id.value = res.data?.id; // Cập nhật blog_id
    } catch (error) {
        console.error('Lỗi khi lấy blog ID:', error);
    }
};

// Khởi tạo khi component được mount
onMounted(async () => {
    await getBlogId(slug.value); // Lấy blog_id từ slug
    await fetchComments(); // Lấy danh sách bình luận
    if (authStore.user) {
        name.value = authStore.user.name || ''; // Cập nhật tên người dùng
        email.value = authStore.user.email || ''; // Cập nhật email người dùng
    }
});

// Theo dõi thay đổi slug để cập nhật bình luận
watch(
    slug,
    async s => {
        if (!s) return;
        await getBlogId(s); // Lấy blog_id mới
        await fetchComments(); // Lấy lại danh sách bình luận
    },
    { immediate: true } // Thực thi ngay lập tức khi slug thay đổi
);
</script>

<style scoped>
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
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Style cho phân trang */
.pagination ul {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}
</style>
