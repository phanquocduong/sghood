<!-- SortBy.vue -->
<template>
    <ClientOnly>
        <div class="sort-by">
            <div class="sort-by-select">
                <select :value="selected" class="chosen-select-no-single">
                    <option v-for="option in options" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
    </ClientOnly>
</template>

<script setup>
import { onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        default: () => []
    },
    selected: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:sort']);

// Khởi tạo Chosen và gắn sự kiện change
onMounted(async () => {
    if (window.jQuery && window.jQuery.fn.chosen) {
        // Hoãn khởi tạo Chosen để đảm bảo DOM trong ClientOnly đã sẵn sàng
        await nextTick();
        window.jQuery('.chosen-select-no-single').each(function () {
            const $select = window.jQuery(this);
            $select
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả',
                    disable_search: false
                })
                .on('change', event => {
                    const value = event.target.value;
                    console.log('SortBy change:', value); // Debug giá trị
                    emit('update:sort', value); // Emit sự kiện update:sort
                });
        });
    } else {
        console.error('jQuery hoặc Chosen không được tải');
    }
});

// Hủy Chosen khi component bị hủy
onUnmounted(() => {
    if (window.jQuery && window.jQuery.fn.chosen) {
        window.jQuery('.chosen-select-no-single').chosen('destroy');
        window.jQuery('.chosen-select-no-single').off('change');
    }
});
</script>
