<template>
    <!-- Titlebar -->
    <div id="titlebar">
        <div class="row">
            <div class="col-md-12">
                <h2>Hồ sơ cá nhân</h2>
                <nav id="breadcrumbs">
                    <ul>
                        <li><NuxtLink to="/">Trang chủ</NuxtLink></li>
                        <li>Hồ sơ cá nhân</li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Thông tin hồ sơ</h4>
                <div class="dashboard-list-box-static">
                    <div class="edit-profile-photo">
                        <ClientOnly>
                            <img :src="previewAvatar || avatarUrl" alt="Avatar" />
                        </ClientOnly>

                        <div class="change-photo-btn">
                            <div class="photoUpload">
                                <span><i class="fa fa-upload"></i> Tải ảnh lên</span>
                                <input type="file" class="upload" @change="handleAvatarUpload" />
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="handleSubmit" class="my-profile">
                        <label>Họ tên</label>
                        <input v-model="form.name" type="text" required />

                        <label>Số điện thoại</label>
                        <input v-model="form.phone" type="text" disabled />

                        <label>Email</label>
                        <input v-model="form.email" type="email" disabled />

                        <ClientOnly
                            ><div>
                                <label>Giới tính</label>
                                <select v-model="form.gender" class="chosen-select">
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </ClientOnly>

                        <label>Ngày sinh</label>
                        <input v-model="formattedBirthdate" type="date" />

                        <label>Địa chỉ</label>
                        <input v-model="form.address" type="text" />

                        <button type="submit" class="button margin-top-15" :disabled="loadingUpdateProfile">
                            <span v-if="loadingUpdateProfile" class="spinner"></span>
                            {{ loadingUpdateProfile ? 'Đang lưu...' : 'Lưu thay đổi' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Thay đổi mật khẩu</h4>
                <div class="dashboard-list-box-static">
                    <form @submit.prevent="handleChangePassword" class="my-profile">
                        <label class="margin-top-0">Mật khẩu hiện tại</label>
                        <input v-model="passwordForm.currentPassword" type="password" required />

                        <label>Mật khẩu mới</label>
                        <input v-model="passwordForm.newPassword" type="password" required />

                        <label>Xác nhận mật khẩu mới</label>
                        <input v-model="passwordForm.confirmPassword" type="password" required />

                        <button type="submit" class="button margin-top-15" :disabled="loadingChangePassword">
                            <span v-if="loadingChangePassword" class="spinner"></span>
                            {{ loadingChangePassword ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '~/stores/auth';
import { storeToRefs } from 'pinia';
import { useToast } from 'vue-toastification';

// Định nghĩa layout
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const toast = useToast();
const config = useRuntimeConfig();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const { fetchUser } = authStore;

const loadingUpdateProfile = ref(false);
const loadingChangePassword = ref(false);

// Dữ liệu form hồ sơ
const form = ref({
    name: user.value?.name || '',
    phone: user.value?.phone || '',
    email: user.value?.email || '',
    gender: user.value?.gender || '',
    address: user.value?.address || '',
    birthdate: user.value?.birthdate || '',
    avatar: null
});

// Dữ liệu form đổi mật khẩu
const passwordForm = ref({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
});

// URL ảnh đại diện
const previewAvatar = ref(null);

const avatarUrl = computed(() => {
    if (user.value?.avatar) {
        return `${config.public.baseUrl}${user.value.avatar}`;
    }
    return '/images/default-avatar.webp';
});

// Định dạng birthDate cho input type="date"
const formattedBirthdate = computed({
    get: () => {
        if (form.value.birthdate) {
            return new Date(form.value.birthdate).toISOString().split('T')[0];
        }
        return '';
    },
    set: value => {
        form.value.birthdate = value ? new Date(value).toISOString().split('T')[0] : '';
    }
});

// Khởi tạo Chosen và gắn sự kiện change
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            const $select = window.jQuery('.chosen-select').chosen({
                width: '100%',
                no_results_text: 'Không tìm thấy kết quả'
            });

            // Gắn sự kiện change để đồng bộ với form.gender
            $select.on('change', event => {
                const value = event.target.value;
                form.value.gender = value; // Cập nhật giá trị trong form
            });

            // Đặt giá trị ban đầu cho Chosen
            if (form.value.gender) {
                $select.val(form.value.gender).trigger('chosen:updated');
            }
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});

// Xử lý tải ảnh lên (lưu file vào form)
const handleAvatarUpload = event => {
    const file = event.target.files[0];
    if (file) {
        previewAvatar.value = URL.createObjectURL(file);
        form.value.avatar = file; // Lưu file để gửi cùng form
    }
};

const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error);
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0]));
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

// Xử lý submit form hồ sơ (gộp avatar và thông tin cá nhân)
const handleSubmit = async () => {
    try {
        loadingUpdateProfile.value = true;
        const formData = new FormData();
        if (form.value.name) formData.append('name', form.value.name);
        if (form.value.gender) formData.append('gender', form.value.gender);
        if (form.value.birthdate) formData.append('birthdate', form.value.birthdate);
        if (form.value.address) formData.append('address', form.value.address);
        if (form.value.avatar) formData.append('avatar', form.value.avatar);
        formData.append('_method', 'PATCH');

        await $api('/user/profile', {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchUser();
        toast.success('Cập nhật thông tin hồ sơ thành công');
    } catch (error) {
        handleBackendError(error);
    } finally {
        loadingUpdateProfile.value = false;
    }
};

// Xử lý đổi mật khẩu
const handleChangePassword = async () => {
    if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
        toast.error('Mật khẩu mới và xác nhận mật khẩu không khớp!');
        return;
    }
    try {
        loadingChangePassword.value = true;
        await $api('/user/change-password', {
            method: 'POST',
            body: {
                current_password: passwordForm.value.currentPassword,
                new_password: passwordForm.value.newPassword,
                new_password_confirmation: passwordForm.value.confirmPassword,
                _method: 'PATCH'
            },
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        toast.success('Đổi mật khẩu thành công!');
        passwordForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' };
    } catch (error) {
        handleBackendError(error);
    } finally {
        loadingChangePassword.value = false;
    }
};
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
</style>
