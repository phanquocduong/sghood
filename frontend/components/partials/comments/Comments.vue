<template>
    <section class="comments">
        <h4 class="headline margin-bottom-35">
            Bình luận <span class="comments-amount">({{ comments.length }})</span>
        </h4>
        <p v-if="comments.length === 0" class="text-gray-400">Chưa có bình luận nào. Nếu muốn bình luận hãy đăng nhập nhé!</p>
        <ul>
            <template v-for="comment in comments" :key="comment.id">
                <CommentsNode v-if="comment" :comment="comment" :blog_id="comment.blog_id" @refresh="fetchComments" />
            </template>
            <div
                class="pagination-container margin-bottom-40"
                style="display: flex; justify-content: center; margin-top: 30px"
                v-if="totalPages > 1"
            >
                <nav class="pagination">
                    <ul>
                        <li v-if="currentPage > 1">
                            <a href="#" @click.prevent="goToPage(currentPage - 1)">
                                <i class="sl sl-icon-arrow-left"></i>
                            </a>
                        </li>
                        <li v-for="page in totalPages" :key="page">
                            <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="goToPage(page)">
                                {{ page }}
                            </a>
                        </li>
                        <li v-if="currentPage < totalPages">
                            <a href="#" @click.prevent="goToPage(currentPage + 1)">
                                <i class="sl sl-icon-arrow-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div id="add-review" class="add-review-box" v-if="authStore.user">
                <!-- Add Review -->
                <h3 class="listing-desc-headline margin-bottom-35">Bình luận</h3>

                <!-- Review Comment -->
                <form id="add-comment" class="add-comment">
                    <fieldset>
                        <div>
                            <label>Bình luận:</label>
                            <textarea cols="40" rows="3" v-model="ReplayContent"></textarea>
                        </div>
                    </fieldset>

                    <button
                        class="button"
                        @click.prevent="AddReplay(blog_id)"
                        type="submit"
                        id="submit"
                        value="Gửi tin nhắn"
                        :disabled="loading"
                        style="margin-bottom: 10px; margin-top: -10px"
                    >
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
import CommentsNode from './CommentsNode.vue';
import { useRoute } from 'vue-router';
import { useAppToast } from '~/composables/useToast';
import { useAuthStore } from '~/stores/auth';
const comments = ref([]);
const { $api } = useNuxtApp();
const name = ref('');
const showReplay = ref(false);
const email = ref('');
const toast = useAppToast();
const loading = ref(false);
const ReplayContent = ref('');
const blog_id = ref(null);
const route = useRoute();
const currentPage = ref(1);
const totalPages = ref(1);
const slug = computed(() => route.params.slug);
const authStore = useAuthStore();
const goToPage = async page => {
    if (page !== currentPage.value) {
        await fetchComments(page);
    }
};

const fetchComments = async (page = 1) => {
    try {
        const res = await $api(`/blogs/${slug.value}/comments?page=${page}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        comments.value = res.data || [];
        if (Array.isArray(res.data) && res.data.length > 0) {
            blog_id.value = res.data[0].blog_id;
        }
        currentPage.value = res.meta.current_page || 1;
        totalPages.value = res.meta.last_page || 1;
    } catch (error) {
        console.error('Lỗi khi fetch bình luận:', error);
    }
};
const AddReplay = async blog_id => {
    if (ReplayContent.value.trim() === '') {
        toast('Vui lòng nhập nội dung bình luận');
        return;
    }
    loading.value = true;
    try {
        const res = await $api(`/blogs/${blog_id}/send-comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                content: ReplayContent.value,
                user_id: authStore.user?.id
            }
        });
        showReplay.value = false;
        ReplayContent.value = '';
        await fetchComments();
        toast.success('Bình luận đã được gửi thành công!');
    } catch (error) {
        console.error('Error handling reply:', error);
        toast.error('Có lỗi xảy ra khi gửi bình luận.');
    } finally {
        loading.value = false;
    }
};
const getBlogId = async slug => {
    try {
        const res = await $api(`/show/${slug}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        blog_id.value = res.data?.id;
    } catch (error) {
        console.error('Lỗi khi lấy blog ID:', error);
    }
};

// 👀 Theo dõi slug thay đổi
onMounted(async () => {
    await getBlogId(slug.value);
    await fetchComments();
    if (authStore.user) {
        name.value = authStore.user.name || '';
        email.value = authStore.user.email || '';
    }
});
watch(
    slug,
    async s => {
        if (!s) return;
        await getBlogId(s);
        await fetchComments();
    },
    { immediate: true }
);
</script>

<style scoped>
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.pagination ul {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}
</style>
