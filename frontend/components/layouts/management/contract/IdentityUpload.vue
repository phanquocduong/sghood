<template>
    <div class="col-lg-3 col-md-12">
        <div class="dashboard-list-box margin-top-0">
            <h4 class="gray">Giấy tờ tùy thân</h4>
            <!-- Tiêu đề khu vực giấy tờ tùy thân -->
            <div class="dashboard-list-box-static">
                <div class="upload-instructions">
                    <h5>Hướng dẫn tải ảnh CCCD</h5>
                    <!-- Hướng dẫn tải ảnh -->
                    <ul>
                        <li>Tải lên đúng 2 ảnh: mặt trước và mặt sau của căn cước công dân.</li>
                        <li>Đảm bảo ảnh rõ nét, không bị mờ hoặc nhòe.</li>
                        <li>Ảnh không được nghiêng, chụp thẳng để hiển thị đầy đủ thông tin.</li>
                        <li>Cắt background sát với thẻ căn cước, không để các vật thể khác trong ảnh.</li>
                        <li>Định dạng ảnh: JPEG hoặc PNG, kích thước tối đa 2MB mỗi ảnh.</li>
                    </ul>
                </div>
                <!-- Thông báo khi ảnh CCCD đã hợp lệ -->
                <p v-if="identityDocument?.has_valid" class="valid-message">Ảnh căn cước đã hợp lệ, không thể tải lên thêm.</p>
                <!-- Thông báo khi quét CCCD thất bại -->
                <p v-else-if="bypassExtract" class="bypass-message">
                    Quét CCCD thất bại nhiều lần. Vui lòng nhập thông tin CCCD trực tiếp vào hợp đồng và tải ảnh lên để admin xác nhận.
                </p>
                <!-- Khu vực tải ảnh CCCD -->
                <div class="edit-profile-photo">
                    <form id="dropzone-upload" class="dropzone" :class="{ 'dropzone-disabled': identityDocument?.has_valid }"></form>
                </div>
            </div>
        </div>
        <!-- Nút lưu hợp đồng -->
        <button @click="$emit('save-contract')" class="button margin-top-20" :disabled="saveLoading || !isFormComplete">
            <span v-if="saveLoading" class="button-spinner"></span>
            {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
        </button>
    </div>
</template>

<script setup>
// Định nghĩa props
defineProps({
    identityDocument: {
        type: Object,
        required: true // Thông tin giấy tờ tùy thân
    },
    isFormComplete: {
        type: Boolean,
        required: true // Trạng thái hoàn thiện form
    },
    saveLoading: {
        type: Boolean,
        required: true // Trạng thái loading khi lưu
    },
    bypassExtract: {
        type: Boolean,
        required: true // Trạng thái bypass quét CCCD
    }
});

// Định nghĩa emits
defineEmits(['save-contract', 'identity-upload']);
</script>

<style scoped>
@import '~/public/css/identity-upload.css';
</style>
