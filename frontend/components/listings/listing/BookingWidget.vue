<template>
    <div id="booking-widget-anchor" class="boxed-widget booking-widget message-vendor margin-top-35">
        <h3><i class="fa fa-envelope-o"></i> Đặt lịch xem phòng</h3>
        <div class="row with-forms margin-top-0">
            <div class="col-lg-12">
                <input type="datetime-local" v-model="formData.datetime" />
                <textarea name="" id="" cols="10" rows="2" placeholder="Lời nhắn (tuỳ chọn)" v-model="formData.message"></textarea>
            </div>
        </div>
        <a href="#" class="button book-now fullwidth margin-top-5" @click.prevent="submitForm">Đặt lịch</a>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const formData = ref({
    datetime: '',
    message: ''
});

const submitForm = () => {
    // Logic xử lý form (gửi API hoặc emit dữ liệu)
    console.log('Form submitted:', formData.value);
    // Emit sự kiện nếu cần
    // emit('submit', formData.value);
};

// Nếu cần emit dữ liệu ra ngoài
// defineEmits(['submit']);
</script>
