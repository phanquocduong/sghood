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
                        <img :src="avatarUrl || '/images/user-avatar.jpg'" alt="Avatar" />
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

                        <div>
                            <label>Giới tính</label>
                            <select class="chosen-select">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>

                        <label>Ngày sinh</label>
                        <input v-model="form.birthDate" type="date" />

                        <label>Địa chỉ</label>
                        <input type="text" />

                        <button type="submit" class="button margin-top-15">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password & Dropzone -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Giấy tờ tuỳ thân</h4>
                <div class="dashboard-list-box-static">
                    <div class="edit-profile-photo">
                        <form id="dropzone-upload" class="dropzone"></form>
                    </div>
                </div>
            </div>
            <div class="dashboard-list-box margin-top-30">
                <h4 class="gray">Thay đổi mật khẩu</h4>
                <div class="dashboard-list-box-static">
                    <div class="my-profile">
                        <label class="margin-top-0">Mật khẩu hiện tại</label>
                        <input type="password" />

                        <label>Mật khẩu mới</label>
                        <input type="password" />

                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" />

                        <button class="button margin-top-15">Đổi mật khẩu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Định nghĩa layout
definePageMeta({
    layout: 'management'
});

// Dữ liệu form
const form = ref({
    name: 'Tom Perrin',
    phone: '+84828283169',
    email: 'tom@example.com',
    birthDate: ''
});

const avatarUrl = ref(null);

// Xử lý tải ảnh lên
const handleAvatarUpload = event => {
    const file = event.target.files[0];
    if (file) {
        avatarUrl.value = URL.createObjectURL(file);
        // Thêm logic gửi file lên server nếu cần
    }
};

// Xử lý submit form
const handleSubmit = () => {
    console.log('Form submitted:', form.value);
    // Thêm logic gửi dữ liệu lên server nếu cần
};

// Khởi tạo Dropzone
onMounted(() => {
    const { $dropzone } = useNuxtApp();
    new $dropzone('#dropzone-upload', {
        url: '/file-upload', // URL API để xử lý file
        autoProcessQueue: true, // Tự động upload khi file được chọn
        maxFilesize: 5, // Giới hạn kích thước file (MB)
        acceptedFiles: 'image/*', // Chỉ chấp nhận file hình ảnh
        dictDefaultMessage: '<i class="sl sl-icon-plus"></i>Kéo và thả file hoặc nhấp để tải lên',
        success: (file, response) => {
            console.log('File uploaded successfully:', response);
        },
        error: (file, message) => {
            console.error('Error uploading file:', message);
        }
    });
});
</script>

<style scoped>
.dropzone {
    border: 2px dashed #ccc;
}

.dropzone:hover {
    border: 2px dashed #59b02c;
}
</style>
