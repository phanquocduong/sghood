<template>
    <div class="signature-section mt-4">
        <div class="signature-container">
            <div class="signature-header">
                <h4>Chữ Ký Điện Tử</h4>
                <!-- Tiêu đề chữ ký -->
                <p>Vui lòng ký tên vào khung bên dưới</p>
                <!-- Hướng dẫn -->
            </div>

            <div class="signature-pad-wrapper">
                <!-- Canvas để vẽ chữ ký -->
                <canvas
                    ref="canvas"
                    class="signature-pad"
                    :width="canvasWidth"
                    :height="canvasHeight"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @mouseleave="stopDrawing"
                    @touchstart="handleTouch"
                    @touchmove="handleTouch"
                    @touchend="stopDrawing"
                ></canvas>

                <!-- Placeholder hiển thị khi chưa có chữ ký -->
                <div v-if="isEmpty" class="signature-placeholder">
                    <i class="fas fa-signature"></i>
                    <span>Ký tên tại đây</span>
                </div>
            </div>

            <div class="signature-actions">
                <!-- Nút xóa chữ ký -->
                <button type="button" class="btn btn-clear" @click="clearSignature">
                    <i class="fas fa-eraser"></i>
                    Xóa chữ ký
                </button>

                <!-- Nút xác nhận chữ ký -->
                <button type="button" class="btn btn-confirm" @click="saveSignature" :disabled="isEmpty">
                    <i class="fas fa-check"></i>
                    Xác nhận chữ ký
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onUnmounted } from 'vue';

// Định nghĩa props
const props = defineProps({
    width: {
        type: Number,
        default: 400 // Chiều rộng canvas mặc định
    },
    height: {
        type: Number,
        default: 200 // Chiều cao canvas mặc định
    }
});

// Định nghĩa emits
const emit = defineEmits(['signature-saved', 'signature-cleared']);

// Khởi tạo các biến reactive
const canvas = ref(null); // Ref cho canvas
const canvasWidth = ref(props.width); // Chiều rộng canvas
const canvasHeight = ref(props.height); // Chiều cao canvas
const isEmpty = ref(true); // Trạng thái canvas rỗng

// Khởi tạo biến trạng thái vẽ
let isDrawing = false; // Trạng thái đang vẽ
let ctx = null; // Context của canvas
let lastX = 0; // Tọa độ X cuối cùng
let lastY = 0; // Tọa độ Y cuối cùng

// Khởi tạo canvas
const initCanvas = () => {
    if (!canvas.value) return;

    ctx = canvas.value.getContext('2d'); // Lấy context 2D
    ctx.strokeStyle = '#000'; // Màu nét vẽ
    ctx.lineWidth = 2; // Độ dày nét vẽ
    ctx.lineCap = 'round'; // Đầu nét vẽ tròn
    ctx.lineJoin = 'round'; // Góc nối nét vẽ tròn

    // Đặt nền trắng cho canvas
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);

    // Đảm bảo tỷ lệ pixel cho nét vẽ sắc nét
    const rect = canvas.value.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    canvas.value.width = rect.width * dpr;
    canvas.value.height = rect.height * dpr;

    ctx.scale(dpr, dpr); // Tỷ lệ theo độ phân giải
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    // Đặt lại nền sau khi scale
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, rect.width, rect.height);
};

// Lấy tọa độ sự kiện (chuột hoặc cảm ứng)
const getEventPos = e => {
    const rect = canvas.value.getBoundingClientRect();
    const scaleX = canvas.value.width / rect.width;
    const scaleY = canvas.value.height / rect.height;

    let clientX, clientY;

    if (e.touches && e.touches.length > 0) {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    } else {
        clientX = e.clientX;
        clientY = e.clientY;
    }

    return {
        x: ((clientX - rect.left) * scaleX) / (window.devicePixelRatio || 1),
        y: ((clientY - rect.top) * scaleY) / (window.devicePixelRatio || 1)
    };
};

// Bắt đầu vẽ
const startDrawing = e => {
    e.preventDefault();
    isDrawing = true;
    const pos = getEventPos(e);
    lastX = pos.x;
    lastY = pos.y;
    isEmpty.value = false;

    ctx.beginPath(); // Bắt đầu đường vẽ mới
    ctx.moveTo(pos.x, pos.y); // Di chuyển đến vị trí bắt đầu
};

// Vẽ trên canvas
const draw = e => {
    if (!isDrawing) return;
    e.preventDefault();

    const pos = getEventPos(e);

    ctx.lineTo(pos.x, pos.y); // Vẽ đường đến vị trí mới
    ctx.stroke();

    lastX = pos.x;
    lastY = pos.y;
};

// Dừng vẽ
const stopDrawing = e => {
    if (!isDrawing) return;
    e.preventDefault();
    isDrawing = false;
    ctx.beginPath(); // Reset đường vẽ
};

// Xử lý sự kiện cảm ứng
const handleTouch = e => {
    e.preventDefault();

    switch (e.type) {
        case 'touchstart':
            startDrawing(e);
            break;
        case 'touchmove':
            draw(e);
            break;
        case 'touchend':
            stopDrawing(e);
            break;
    }
};

// Xóa chữ ký
const clearSignature = () => {
    if (!canvas.value || !ctx) return;

    const rect = canvas.value.getBoundingClientRect();
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, rect.width, rect.height); // Xóa canvas
    isEmpty.value = true;
    emit('signature-cleared'); // Emit sự kiện xóa chữ ký
};

// Lưu chữ ký
const saveSignature = () => {
    if (isEmpty.value || !canvas.value) return;

    const signatureData = canvas.value.toDataURL('image/png'); // Chuyển canvas thành dữ liệu hình ảnh
    emit('signature-saved', signatureData); // Emit sự kiện lưu chữ ký
};

// Cập nhật kích thước canvas cho responsive
const updateCanvasSize = () => {
    if (!canvas.value) return;

    const container = canvas.value.parentElement;
    if (container) {
        const containerWidth = container.offsetWidth - 32; // Trừ padding
        if (containerWidth < props.width) {
            canvasWidth.value = containerWidth;
            canvasHeight.value = Math.floor((containerWidth * props.height) / props.width); // Tính lại chiều cao
        } else {
            canvasWidth.value = props.width;
            canvasHeight.value = props.height;
        }

        // Cập nhật lại canvas sau khi thay đổi kích thước
        nextTick(() => {
            initCanvas();
        });
    }
};

// Lifecycle hooks
onMounted(async () => {
    await nextTick();
    updateCanvasSize(); // Cập nhật kích thước canvas
    window.addEventListener('resize', updateCanvasSize); // Lắng nghe sự kiện resize
});

onUnmounted(() => {
    window.removeEventListener('resize', updateCanvasSize); // Gỡ bỏ sự kiện resize
});
</script>

<style scoped>
@import '~/public/css/signature-pad.css';
</style>
