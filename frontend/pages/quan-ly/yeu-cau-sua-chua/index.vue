<template>
    <div>
        <Titlebar title="Yêu cầu sửa chữa" />

        <Loading :is-loading="loading" />

        <div v-if="loading" class="text-center p-5">
            <p>Đang tải yêu cầu...</p>
        </div>

        <div v-else class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <div class="box-title-bar-req">
                        <h4>Danh sách yêu cầu sửa chữa</h4>
                        <NuxtLink to="/quan-ly/yeu-cau-sua-chua/them-yeu-cau" class="add-button">
                            <i class="im im-icon-Add mr-2"></i> Yêu cầu sửa chữa
                        </NuxtLink>
                    </div>

                    <div v-if="repairRequests.length === 0" class="box-title-bar-tb">
                        <p>Chưa có yêu cầu nào.</p>
                    </div>

                    <div v-for="(req, index) in repairRequests" :key="req.id" class="repair-item">
                        <!-- Ảnh bên trái -->
                        <div class="repair-image-wrapper" v-if="req.images?.length">
                            <img
                                :src="`${baseUrl}${req.images[0]}`"
                                alt="Hình ảnh sự cố"
                                class="repair-image"
                                @click="openImageSlider(index)"
                            />
                        </div>

                        <!-- Nội dung bên phải -->
                        <div class="repair-content">
                            <!-- Sửa phần này trong repair-item -->
                            <div class="repair-header">
                                <!-- Tiêu đề bên trái -->
                                <div class="repair-title">
                                    <h5>
                                        <i class="fas fa-tools icon-repair"></i>
                                        {{ req.title }}
                                    </h5>
                                </div>

                                <!-- Trạng thái + nút hủy bên phải theo cột dọc -->
                                <div class="repair-actions">
                                    <span
                                        class="status-tag"
                                        :class="{
                                            pending: req.status === 'Chờ xác nhận',
                                            inprogress: req.status === 'Đang thực hiện',
                                            done: req.status === 'Hoàn thành',
                                            canceled: req.status === 'Huỷ bỏ'
                                        }"
                                    >
                                        {{ req.status }}
                                    </span>

                                    <button
                                        class="delete-btn"
                                        :class="{ 'btn-cancelled-style': isLoading === req.id }"
                                        v-if="req && req.id && ['Chờ xác nhận'].includes(req.status)"
                                        @click="removeRequest(req.id)"
                                        :disabled="isLoading === req.id"
                                    >
                                        <span v-if="isLoading === req.id" class="spinner"></span>
                                        {{ isLoading === req.id ? ' Đang hủy...' : 'Hủy' }}
                                    </button>
                                </div>
                            </div>

                            <p class="description">{{ req.description }}</p>

                            <!-- Lý do hủy -->
                            <div v-if="req.status === 'Đã hủy' && req.cancellation_reason" class="cancel-box">
                                <strong>Lý do hủy:</strong> {{ req.cancellation_reason }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal xem ảnh -->
        <div v-if="showSlider" class="modal-overlay" @click.self="closeSlider">
            <div class="modal-content">
                <Transition :name="transitionName" mode="out-in">
                    <div class="slider-wrapper" :key="selectedImages[currentIndex]">
                        <img :src="selectedImages[currentIndex]" class="slider-image" />
                    </div>
                </Transition>

                <!-- Nút điều hướng dạng overlay nằm ngoài khung -->
                <button class="mfp-arrow mfp-arrow-left mfp-prevent-close" title="Previous" @click.stop="prevImage"></button>
                <button class="mfp-arrow mfp-arrow-right mfp-prevent-close" title="Next" @click.stop="nextImage"></button>

                <button class="modal-close" @click="closeSlider">×</button>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({ layout: 'management' });
import { ref, onMounted } from 'vue';

const showSlider = ref(false);
const selectedImages = ref([]);
const currentIndex = ref(0);
const loading = ref(true);
const transitionName = ref('slide-left');
const isLoading = ref(null);
const { $api } = useNuxtApp();
const repairRequests = ref([]);
const baseUrl = useRuntimeConfig().public.baseUrl;
const openImageSlider = reqIndex => {
    selectedImages.value = (repairRequests.value[reqIndex].images || []).map(img => `${baseUrl}${img}`);
    currentIndex.value = 0;
    showSlider.value = true;
};

const nextImage = () => {
    transitionName.value = 'slide-left';
    currentIndex.value = (currentIndex.value + 1) % selectedImages.value.length;
};

const prevImage = () => {
    transitionName.value = 'slide-left';
    currentIndex.value = (currentIndex.value - 1 + selectedImages.value.length) % selectedImages.value.length;
};

const closeSlider = () => {
    showSlider.value = false;
};

const FetchRepair = async () => {
    loading.value = true;
    try {
        const res = await $api(`/repair-requests`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        repairRequests.value = res.data || [];
    } catch (e) {
        console.log('sai o dau roi ban oi', e);
    } finally {
        loading.value = false;
    }
};
const removeRequest = async id => {
    if (!id) {
        console.warn('Không có ID để huỷ');
        return;
    }
    isLoading.value = id;
    try {
        const res = await $api(`/repair-requests/${id}/cancel `, {
            method: 'PATCH',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
                Accept: 'application/json'
            }
        });
        await FetchRepair();
        console.log(res);
    } catch (e) {
        console.log('sai o dau roi ban oi', e);
    } finally {
        isLoading.value = null;
    }
};
onMounted(() => {
    FetchRepair();
});
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
.repair-item {
    display: flex;
    align-items: flex-start;
    border-bottom: 1px solid #eee;
    padding: 20px;
    position: relative;
    background: #fff;
    gap: 20px;
}

.repair-image-wrapper {
    flex-shrink: 0;
}

.repair-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ddd;
    cursor: pointer;
}

.repair-content {
    display: block;
    flex: 1;
}

.repair-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.icon-repair {
    color: #f44336;
    margin-right: 8px;
}

.description {
    margin: 8px 0;
    color: #7d7d7d;
    margin-top: -6px;
    font-size: 15px;
    padding: 10px 1px 1px 1px;
}

.status-tag {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    min-width: 100px;
}

.pending {
    background-color: #edb717;
    color: white;
}

.inprogress {
    background-color: #39bcf9;
    color: white;
}

.done {
    background-color: #8ed83a;
    color: white;
}

.canceled {
    background-color: #f91942;
    color: white;
}
.btn-cancelled-style {
    border: 2px solid #f91942 !important;
    background-color: white !important;
    color: #f91942 !important;
    font-weight: bold;
}
.cancel-box {
    margin-top: 5px;
    padding: 10px;
    background: #ffebee;
    border-left: 4px solid #f91942;
    color: #f91942;
}

.repair-title {
    flex: 1;
    display: flex;
    align-items: center;
}

.repair-title {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
}
.repair-title h5 {
    font-size: 18px;
    font-weight: bold;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    word-break: break-word;
}

.delete-btn {
    background: transparent;
    border: 1px solid #f91942;
    font-size: 14px;
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    margin-left: auto;
    background-color: #f91942;
}
.box-title-bar-tb {
    font-size: larger;
    padding: 10px;
    text-align: center;
}

/* Modal xem ảnh */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-content {
    position: relative;
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    max-width: 80%;
}

.slider-image {
    width: 500px; /* 👈 Tuỳ chỉnh kích thước mong muốn */
    height: 500px;
    object-fit: contain; /* hoặc cover nếu bạn muốn ảnh full khung */
    border-radius: 6px;
}

.slider-controls {
    display: flex;
    justify-content: space-between;
    background-color: #e8e7e7;
}

.slider-controls button {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 6px;
    width: 40px;
    height: 40px;
    font-size: 20px;

    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    background: rgb(220, 220, 220);
    color: white;
    border: none;
    font-size: 24px;
    width: 40px;
    height: 40px;
    cursor: pointer;
    border-radius: 50%;
    transition: color 0.3s ease, transform 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: rgb(213, 13, 13);
    transform: scale(1.2);
}
h5 {
    font-size: 18px;
    transform: translate(-15px);
    font-weight: bold;
    bottom: 5px;
    top: 5px;
}
.slide-left-enter-active,
.slide-right-enter-active {
    transition: all 0.4s ease;
    width: 100%;
}

.slide-left-leave-active,
.slide-right-leave-active {
    transition: all 0.1s ease;

    width: 100%;
}

.slide-left-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-left-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.slide-right-enter-from {
    transform: translateX(-100%);
    opacity: 0;
}
.slide-right-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
.slider-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px; /* Hoặc max-height phù hợp */
    overflow: hidden;
    position: relative;
}
/* Nút trái & phải overlay */
.mfp-arrow {
    position: absolute;
    top: 50%;
    width: 60px;
    height: 60px;
    margin-top: -30px;
    cursor: pointer;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    font-size: 32px;
    line-height: 60px;
    text-align: center;
    z-index: 1000;
    border-radius: 50%;
    transition: background 0.3s;
}

.mfp-arrow:hover {
    background: rgba(0, 0, 0, 0.8);
}

.mfp-arrow-left {
    left: -100px; /* nằm ngoài khung modal-content */
}

.mfp-arrow-right {
    right: -100px;
}

/* Điều chỉnh nếu muốn nằm sát hình ảnh thay vì ngoài khung */
.modal-content {
    position: relative;
    padding: 20px;
    max-width: 80%;
    overflow: visible; /* Cho mũi tên vượt ra ngoài */
}

/* ----------- Desktop & Default Styles ----------- */
.add-button {
    background-color: #f91942;
    color: white;
    border: none;
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.add-button:hover {
    background-color: white;
    color: #f91942;
    border: 1px solid #f91942;
}

.repair-actions {
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
    flex-shrink: 0;
}

.box-title-bar-req {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    padding: 5px 20px;
    position: relative;
    background-color: white;
    gap: 12px;
    position: sticky;
    top: 0;
    z-index: 99;
}

.box-title-bar::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: #eaeaea;
}

/* ----------- Responsive Styles (Mobile < 480px) ----------- */
@media only screen and (max-width: 480px) {
    .box-title-bar-req {
        display: flex;
        flex-direction: column !important;
        align-items: center !important;
        width: 100% !important;
        justify-content: center;
    }

    .box-title-bar-req h4 {
        width: 100% !important;
        text-align: center;
        margin: auto;
    }

    .add-button {
        display: flex;
        width: 100%;
        justify-content: center;
    }

    .repair-actions {
        flex-direction: column;
        order: 1; /* hiển thị trước */
        align-items: stretch;
        gap: 8px;
        width: 100%;
    }

    .status-tag,
    .delete-btn {
        width: 100%;
        text-align: center;
    }

    .repair-header {
        flex-direction: column;
        align-items: flex-start;

        gap: 8px;
    }

    .repair-title {
        order: 2; /* hiển thị sau */
        width: 100%;
        padding: 8px 12px;
        text-align: center; /* hoặc left nếu bạn thích */
    }
}
</style>
