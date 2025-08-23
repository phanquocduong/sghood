<template>
    <Titlebar title="Yêu cầu sửa chữa" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4>
                    <div style="display: flex; align-items: center; justify-content: space-between">
                        Danh sách yêu cầu sửa chữa
                        <NuxtLink to="/quan-ly/yeu-cau-sua-chua/them-yeu-cau" class="button border with-icon">
                            Yêu cầu sửa chữa <i class="sl sl-icon-plus"></i>
                        </NuxtLink>
                    </div>
                </h4>

                <Loading :is-loading="loading" />

                <ul>
                    <li v-for="(req, index) in repairRequests" :key="req.id" :class="getItemClass(req.status)">
                        <div class="list-box-listing bookings">
                            <div class="list-box-listing-img">
                                <div v-if="req.images?.length" style="height: 150px">
                                    <img :src="`${baseUrl}${req.images[0]}`" alt="Hình ảnh sự cố" @click="openImageSlider(index)" />
                                </div>
                            </div>
                            <div class="list-box-listing-content">
                                <div class="inner">
                                    <h3>
                                        {{ req.title }}
                                        <span :class="getStatusClass(req.status)">{{ req.status }}</span>
                                    </h3>
                                    <div class="inner-booking-list">
                                        <h5>Mô tả sự cố:</h5>
                                        <ul class="booking-list">
                                            <li class="highlighted">{{ req.description }}</li>
                                        </ul>
                                    </div>
                                    <div v-if="req.note" class="inner-booking-list">
                                        <h5>Ghi chú:</h5>
                                        <ul class="booking-list">
                                            <li>{{ req.note }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="buttons-to-right">
                            <button
                                v-if="req && req.id && ['Chờ xác nhận'].includes(req.status)"
                                class="button gray reject"
                                :class="{ 'loading-btn': isLoading === req.id }"
                                @click="removeRequest(req.id)"
                                :disabled="isLoading === req.id"
                            >
                                <span v-if="isLoading === req.id" class="spinner"></span>
                                <i v-else class="sl sl-icon-close"></i>
                                {{ isLoading === req.id ? 'Đang hủy...' : 'Hủy yêu cầu' }}
                            </button>
                        </div>
                    </li>
                    <div v-if="!repairRequests.length" class="col-md-12 text-center">
                        <p>Chưa có yêu cầu nào.</p>
                    </div>
                </ul>
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

const getItemClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'pending-booking';
        case 'Đang thực hiện':
            return 'approved-booking';
        case 'Hoàn thành':
            return 'approved-booking';
        case 'Huỷ bỏ':
        case 'Đã hủy':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ xác nhận') {
        statusClass += ' pending';
    }
    return statusClass;
};

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
        console.error('Error: ', e);
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
        const res = await $api(`/repair-requests/${id}/cancel`, {
            method: 'PATCH',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
                Accept: 'application/json'
            }
        });
        await FetchRepair();
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
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

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

.loading-btn {
    opacity: 0.6;
    cursor: not-allowed;
}

.icon-repair {
    color: #f44336;
    margin-right: 8px;
}

.repair-image-wrapper {
    width: 90px;
    height: 90px;
    overflow: hidden;
    border-radius: 6px;
    cursor: pointer;
}

.repair-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    width: 500px;
    height: 500px;
    object-fit: contain;
    border-radius: 6px;
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
}

.slide-left-enter-active,
.slide-right-enter-active {
    transition: all 0.1s ease;
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
    height: 400px;
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
    left: -100px;
}

.mfp-arrow-right {
    right: -100px;
}

.modal-content {
    position: relative;
    padding: 20px;
    max-width: 80%;
    overflow: visible;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
    .mfp-arrow-left {
        left: -60px;
    }
    .mfp-arrow-right {
        right: -60px;
    }

    .slider-image {
        width: 300px;
        height: 300px;
    }

    .modal-content {
        max-width: 90%;
    }
}
</style>
