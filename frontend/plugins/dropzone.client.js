import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

export default defineNuxtPlugin(nuxtApp => {
    // Tắt autoDiscover để tránh Dropzone tự động tìm các form
    Dropzone.autoDiscover = false;

    return {
        provide: {
            dropzone: Dropzone
        }
    };
});
