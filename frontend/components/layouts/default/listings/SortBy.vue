<template>
    <ClientOnly>
        <div class="sort-by">
            <div class="sort-by-select">
                <select class="chosen-select-no-single">
                    <option v-for="option in options" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
    </ClientOnly>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:sort']);

// Khởi tạo Chosen và gắn sự kiện change
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            window
                .jQuery('.chosen-select-no-single')
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả'
                })
                .on('change', event => {
                    const value = event.target.value;
                    emit('update:sort', value);
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});
</script>
