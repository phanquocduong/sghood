import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

// Định nghĩa plugin Dropzone cho Nuxt
export default defineNuxtPlugin(nuxtApp => {
    // Tắt tính năng tự động tìm kiếm và khởi tạo các form Dropzone trên trang
    Dropzone.autoDiscover = false;

    // Cung cấp Dropzone như một injectable để sử dụng trong ứng dụng Nuxt
    return {
        provide: {
            dropzone: Dropzone
        }
    };
});
