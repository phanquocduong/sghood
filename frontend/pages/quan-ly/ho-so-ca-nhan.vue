<template>
    <!-- Tiêu đề trang -->
    <Titlebar title="Hồ sơ cá nhân" />

    <div class="row">
        <!-- Phần thông tin cá nhân -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Thông tin cá nhân</h4>
                <!-- Tiêu đề phần thông tin cá nhân -->
                <div class="dashboard-list-box-static">
                    <!-- Khu vực hiển thị và tải ảnh đại diện -->
                    <div class="edit-profile-photo">
                        <ClientOnly>
                            <!-- Chỉ render phía client để tránh lỗi SSR -->
                            <img :src="previewAvatar || avatarUrl" alt="Avatar" />
                            <!-- Hiển thị ảnh đại diện -->
                        </ClientOnly>

                        <div class="change-photo-btn">
                            <div class="photoUpload">
                                <span><i class="fa fa-upload"></i> Tải ảnh lên</span>
                                <!-- Nút tải ảnh lên -->
                                <input type="file" class="upload" @change="handleAvatarUpload" />
                                <!-- Input để chọn file ảnh -->
                            </div>
                        </div>
                    </div>

                    <!-- Form cập nhật thông tin cá nhân -->
                    <form @submit.prevent="handleSubmit" class="my-profile">
                        <label>Họ tên</label>
                        <input v-model="form.name" type="text" required />
                        <!-- Trường nhập họ tên -->

                        <label>Số điện thoại</label>
                        <input v-model="form.phone" type="text" disabled />
                        <!-- Trường số điện thoại (không chỉnh sửa được) -->

                        <label>Email</label>
                        <input v-model="form.email" type="email" disabled />
                        <!-- Trường email (không chỉnh sửa được) -->

                        <ClientOnly>
                            <!-- Chỉ render phía client để khởi tạo Chosen -->
                            <div>
                                <label>Giới tính</label>
                                <select v-model="form.gender" class="chosen-select">
                                    <!-- Select box chọn giới tính -->
                                    <option value="">-- Chọn giới tính --</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </ClientOnly>

                        <label>Ngày sinh</label>
                        <input v-model="formattedBirthdate" type="date" />
                        <!-- Trường chọn ngày sinh -->

                        <label>Địa chỉ</label>
                        <input v-model="form.address" type="text" />
                        <!-- Trường nhập địa chỉ -->

                        <!-- Nút lưu thay đổi -->
                        <button type="submit" class="button margin-top-15" :disabled="loadingUpdateProfile">
                            <span v-if="loadingUpdateProfile" class="spinner"></span>
                            <!-- Hiển thị spinner khi đang xử lý -->
                            {{ loadingUpdateProfile ? 'Đang lưu...' : 'Lưu thay đổi' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Phần thay đổi mật khẩu -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Thay đổi mật khẩu</h4>
                <!-- Tiêu đề phần thay đổi mật khẩu -->
                <div class="dashboard-list-box-static">
                    <!-- Form thay đổi mật khẩu -->
                    <form @submit.prevent="handleChangePassword" class="my-profile">
                        <label class="margin-top-0">Mật khẩu hiện tại</label>
                        <input v-model="passwordForm.currentPassword" type="password" required />
                        <!-- Trường nhập mật khẩu hiện tại -->

                        <label>
                            Mật khẩu mới
                            <span style="color: #f91942">(tối thiểu 8 ký tự, gồm chữ hoa/thường, số và ký tự đặc biệt)</span>
                        </label>
                        <input
                            v-model="passwordForm.newPassword"
                            type="password"
                            required
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&\.\-]{8,}$"
                        />
                        <!-- Trường nhập mật khẩu mới với regex kiểm tra -->

                        <label>Xác nhận mật khẩu mới</label>
                        <input v-model="passwordForm.confirmPassword" type="password" required />
                        <!-- Trường xác nhận mật khẩu mới -->

                        <!-- Nút đổi mật khẩu -->
                        <button type="submit" class="button margin-top-15" :disabled="loadingChangePassword">
                            <span v-if="loadingChangePassword" class="spinner"></span>
                            <!-- Hiển thị spinner khi đang xử lý -->
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
import { useAuthStore } from '~/stores/auth'; // Store quản lý thông tin người dùng
import { storeToRefs } from 'pinia'; // Hàm hỗ trợ lấy reactive refs từ store
import { useAppToast } from '~/composables/useToast'; // Hàm hiển thị thông báo
import { useApi } from '~/composables/useApi'; // Hàm xử lý API

// Định nghĩa layout cho trang
definePageMeta({
    layout: 'management' // Sử dụng layout 'management'
});

const { $api } = useNuxtApp(); // Lấy instance của API từ NuxtApp
const toast = useAppToast(); // Hàm hiển thị thông báo
const config = useRuntimeConfig(); // Lấy cấu hình runtime
const authStore = useAuthStore(); // Khởi tạo store quản lý auth
const { user } = storeToRefs(authStore); // Lấy thông tin người dùng từ store
const { fetchUser } = authStore; // Hàm lấy lại thông tin người dùng từ API
const { handleBackendError } = useApi(); // Hàm xử lý lỗi từ backend

const loadingUpdateProfile = ref(false); // Trạng thái loading khi cập nhật hồ sơ
const loadingChangePassword = ref(false); // Trạng thái loading khi đổi mật khẩu

// Dữ liệu form hồ sơ
const form = ref({
    name: user.value?.name || '', // Họ tên người dùng
    phone: user.value?.phone || '', // Số điện thoại
    email: user.value?.email || '', // Email
    gender: user.value?.gender || '', // Giới tính
    address: user.value?.address || '', // Địa chỉ
    birthdate: user.value?.birthdate || '', // Ngày sinh
    avatar: null // File ảnh đại diện
});

// Dữ liệu form đổi mật khẩu
const passwordForm = ref({
    currentPassword: '', // Mật khẩu hiện tại
    newPassword: '', // Mật khẩu mới
    confirmPassword: '' // Xác nhận mật khẩu mới
});

// URL ảnh đại diện
const previewAvatar = ref(null); // Preview ảnh đại diện khi tải lên

const avatarUrl = computed(() => {
    // Trả về URL của ảnh đại diện từ server hoặc ảnh mặc định
    if (user.value?.avatar) {
        return `${config.public.baseUrl}${user.value.avatar}`;
    }
    return '/images/default-avatar.webp';
});

// Định dạng ngày sinh cho input type="date"
const formattedBirthdate = computed({
    get: () => {
        // Chuyển đổi định dạng ngày sinh sang dạng yyyy-mm-dd
        if (form.value.birthdate) {
            return new Date(form.value.birthdate).toISOString().split('T')[0];
        }
        return '';
    },
    set: value => {
        // Cập nhật ngày sinh khi input thay đổi
        form.value.birthdate = value ? new Date(value).toISOString().split('T')[0] : '';
    }
});

// Khởi tạo thư viện Chosen và gắn sự kiện change
onMounted(() => {
    nextTick(() => {
        // Kiểm tra và khởi tạo Chosen cho select box
        if (window.jQuery && window.jQuery.fn.chosen) {
            const $select = window.jQuery('.chosen-select').chosen({
                width: '100%',
                no_results_text: 'Không tìm thấy kết quả' // Cấu hình Chosen
            });

            // Gắn sự kiện change để đồng bộ với form.gender
            $select.on('change', event => {
                const value = event.target.value;
                form.value.gender = value; // Cập nhật giá trị giới tính
            });

            // Đặt giá trị ban đầu cho Chosen
            if (form.value.gender) {
                $select.val(form.value.gender).trigger('chosen:updated');
            }
        } else {
            console.error('jQuery hoặc Chosen không được tải'); // Báo lỗi nếu thư viện không tải
        }
    });
});

// Xử lý tải ảnh đại diện
const handleAvatarUpload = event => {
    const file = event.target.files[0]; // Lấy file từ input
    if (file) {
        previewAvatar.value = URL.createObjectURL(file); // Tạo URL tạm thời để preview ảnh
        form.value.avatar = file; // Lưu file để gửi lên server
    }
};

// Xử lý submit form cập nhật hồ sơ
const handleSubmit = async () => {
    try {
        loadingUpdateProfile.value = true; // Bật trạng thái loading
        const formData = new FormData(); // Tạo FormData để gửi dữ liệu
        if (form.value.name) formData.append('name', form.value.name); // Thêm họ tên
        if (form.value.gender) formData.append('gender', form.value.gender); // Thêm giới tính
        if (form.value.birthdate) formData.append('birthdate', form.value.birthdate); // Thêm ngày sinh
        if (form.value.address) formData.append('address', form.value.address); // Thêm địa chỉ
        if (form.value.avatar) formData.append('avatar', form.value.avatar); // Thêm file ảnh
        formData.append('_method', 'PATCH'); // Mô phỏng PATCH request

        // Gửi yêu cầu cập nhật hồ sơ
        await $api('/user/profile', {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
            }
        });
        await fetchUser(); // Làm mới thông tin người dùng từ server
        toast.success('Cập nhật thông tin hồ sơ thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        loadingUpdateProfile.value = false; // Tắt trạng thái loading
    }
};

// Xử lý đổi mật khẩu
const handleChangePassword = async () => {
    // Kiểm tra mật khẩu mới và xác nhận mật khẩu có khớp không
    if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
        toast.error('Mật khẩu mới và xác nhận mật khẩu không khớp!'); // Thông báo lỗi
        return;
    }
    try {
        loadingChangePassword.value = true; // Bật trạng thái loading
        // Gửi yêu cầu đổi mật khẩu
        await $api('/user/change-password', {
            method: 'POST',
            body: {
                current_password: passwordForm.value.currentPassword,
                new_password: passwordForm.value.newPassword,
                new_password_confirmation: passwordForm.value.confirmPassword,
                _method: 'PATCH' // Mô phỏng PATCH request
            },
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
            }
        });
        toast.success('Đổi mật khẩu thành công!'); // Hiển thị thông báo thành công
        // Reset form đổi mật khẩu
        passwordForm.value = { currentPassword: '', newPassword: '', confirmPassword: '' };
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        loadingChangePassword.value = false; // Tắt trạng thái loading
    }
};
</script>

<style scoped>
/* CSS cho spinner loading */
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

/* CSS cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed; /* Biểu tượng con trỏ không cho phép */
}

/* CSS cho input bị vô hiệu hóa */
input:disabled {
    cursor: not-allowed;
    background-color: #f1f1f1; /* Màu nền cho input bị vô hiệu hóa */
}
</style>
