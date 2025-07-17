<template>
    <div class="booking-requests-filter">
        <ClientOnly>
            <div class="sort-by">
                <select name="sort" v-model="filter.sort" class="chosen-select" @change="updateFilter('sort', $event.target.value)">
                    <option value="default">Sắp xếp mặc định</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="latest">Mới nhất</option>
                </select>
            </div>
            <div class="sort-by">
                <select name="status" v-model="filter.status" class="chosen-select" @change="updateFilter('status', $event.target.value)">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Chờ xác nhận">Chờ xác nhận</option>
                    <option value="Đã xác nhận">Đã xác nhận</option>
                    <option value="Hoàn thành">Hoàn thành</option>
                    <option value="Huỷ bỏ">Huỷ bỏ</option>
                </select>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

const props = defineProps({
    filter: { type: Object, required: true }
});

const emit = defineEmits(['update:filter']);

const updateFilter = (key, value) => {
    emit('update:filter', { ...props.filter, [key]: value });
};

const initChosenSelect = () => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải');
        return;
    }
    window
        .jQuery('.chosen-select')
        .chosen({
            width: '100%',
            no_results_text: 'Không tìm thấy kết quả'
        })
        .on('change', event => {
            updateFilter(event.target.name, event.target.value);
        });
};

onMounted(() => {
    nextTick(initChosenSelect);
});
</script>

<style scoped></style>
