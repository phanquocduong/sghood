<template>
    <div class="signature-container">
        <div class="signature-header">
            <h4>Chữ Ký Điện Tử</h4>
            <p>Vui lòng ký tên vào khung bên dưới</p>
        </div>

        <div class="signature-pad-wrapper">
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

            <div v-if="isEmpty" class="signature-placeholder">
                <i class="fas fa-signature"></i>
                <span>Ký tên tại đây</span>
            </div>
        </div>

        <div class="signature-actions">
            <button type="button" class="btn btn-clear" @click="clearSignature">
                <i class="fas fa-eraser"></i>
                Xóa chữ ký
            </button>

            <button type="button" class="btn btn-confirm" @click="saveSignature" :disabled="isEmpty">
                <i class="fas fa-check"></i>
                Xác nhận chữ ký
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    width: {
        type: Number,
        default: 400
    },
    height: {
        type: Number,
        default: 200
    }
});

const emit = defineEmits(['signature-saved', 'signature-cleared']);

// Refs
const canvas = ref(null);
const canvasWidth = ref(props.width);
const canvasHeight = ref(props.height);
const isEmpty = ref(true);

// Drawing state
let isDrawing = false;
let ctx = null;
let lastX = 0;
let lastY = 0;

// Methods
const initCanvas = () => {
    ctx = canvas.value.getContext('2d');
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    // Set white background
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);
};

const getMousePos = e => {
    const rect = canvas.value.getBoundingClientRect();
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
};

const getTouchPos = e => {
    const rect = canvas.value.getBoundingClientRect();
    return {
        x: e.touches[0].clientX - rect.left,
        y: e.touches[0].clientY - rect.top
    };
};

const startDrawing = e => {
    isDrawing = true;
    const pos = getMousePos(e);
    lastX = pos.x;
    lastY = pos.y;
    isEmpty.value = false;
};

const draw = e => {
    if (!isDrawing) return;

    const pos = getMousePos(e);

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();

    lastX = pos.x;
    lastY = pos.y;
};

const stopDrawing = () => {
    isDrawing = false;
};

const handleTouch = e => {
    e.preventDefault();

    if (e.type === 'touchstart') {
        isDrawing = true;
        const pos = getTouchPos(e);
        lastX = pos.x;
        lastY = pos.y;
        isEmpty.value = false;
    } else if (e.type === 'touchmove' && isDrawing) {
        const pos = getTouchPos(e);

        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();

        lastX = pos.x;
        lastY = pos.y;
    }
};

const clearSignature = () => {
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);
    isEmpty.value = true;
    emit('signature-cleared');
};

const saveSignature = () => {
    if (isEmpty.value) return;

    const signatureData = canvas.value.toDataURL('image/png');
    emit('signature-saved', signatureData);
};

// Responsive canvas
const updateCanvasSize = () => {
    const container = canvas.value?.parentElement;
    if (container) {
        const containerWidth = container.offsetWidth;
        if (containerWidth < props.width) {
            canvasWidth.value = containerWidth - 16;
            canvasHeight.value = Math.floor((canvasWidth.value * props.height) / props.width);
        }
    }
};

// Lifecycle
onMounted(async () => {
    await nextTick();
    updateCanvasSize();
    initCanvas();

    window.addEventListener('resize', updateCanvasSize);
});
</script>

<style scoped>
.signature-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    max-width: 500px;
    margin: 24px auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
}

.signature-header {
    text-align: center;
    margin-bottom: 24px;
}

.signature-header h4 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px;
}

.signature-header p {
    font-size: 1.4rem;
    color: #6b7280;
    margin: 0;
}

.signature-pad-wrapper {
    position: relative;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    background: #f9fafb;
    margin-bottom: 24px;
    transition: border-color 0.3s ease;
}

.signature-pad-wrapper:hover {
    border-color: #3b82f6;
}

.signature-pad {
    display: block;
    width: 100%;
    height: auto;
    border-radius: 8px;
    background: #ffffff;
    cursor: crosshair;
}

.signature-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.signature-placeholder i {
    font-size: 2.25rem;
    margin-bottom: 8px;
}

.signature-placeholder span {
    font-size: 1rem;
    font-weight: 500;
}

.signature-actions {
    display: flex;
    gap: 16px;
}

.btn {
    flex: 1;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-clear {
    background: #ffffff;
    border: 2px solid #d1d5db;
    color: #4b5563;
}

.btn-clear:hover:not(:disabled) {
    background: #f3f4f6;
    border-color: #9ca3af;
}

.btn-clear:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(209, 213, 219, 0.3);
}

.btn-confirm {
    background: #3b82f6;
    color: #ffffff;
    border: none;
}

.btn-confirm:hover:not(:disabled) {
    background: #2563eb;
    color: white !important;
}

.btn-confirm:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

@media (max-width: 640px) {
    .signature-container {
        padding: 16px;
        margin: 16px;
    }

    .signature-header h4 {
        font-size: 1.5rem;
    }

    .signature-pad-wrapper {
        border-width: 1px;
    }

    .signature-actions {
        flex-direction: column;
        gap: 12px;
    }

    .btn {
        padding: 10px 12px;
    }
}

@media (max-width: 480px) {
    .signature-placeholder i {
        font-size: 1.75rem;
    }

    .signature-placeholder span {
        font-size: 0.875rem;
    }
}
</style>
