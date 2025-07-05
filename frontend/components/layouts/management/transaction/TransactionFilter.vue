<template>
    <div class="booking-requests-filter">
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.sort" name="sort" class="chosen-select">
                        <option value="default">Sắp xếp mặc định</option>
                        <option value="oldest">Cũ nhất</option>
                        <option value="latest">Mới nhất</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.type" name="type" class="chosen-select">
                        <option value="">Tất cả loại</option>
                        <option value="in">Chi</option>
                        <option value="out">Thu</option>
                    </select>
                </div>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { watch, nextTick } from 'vue';

const props = defineProps({
    filter: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:filter']);

// Cập nhật filter và trigger Chosen khi filter thay đổi
watch(
    () => props.filter,
    () => {
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated');
            }
        });
    },
    { deep: true }
);

onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            window
                .jQuery('.chosen-select')
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả'
                })
                .on('change', event => {
                    const key = event.target.name;
                    const value = event.target.value;
                    emit('update:filter', { ...props.filter, [key]: value });
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});
</script>

<style scoped></style>
