<template>
    <div class="booking-requests-filter">
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select name="sort" :value="filter.sort" @change="updateFilter('sort', $event.target.value)" class="chosen-select">
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
                    <select
                        name="status"
                        :value="filter.status"
                        @change="updateFilter('status', $event.target.value)"
                        class="chosen-select"
                    >
                        <option value="">Tất cả trạng thái</option>
                        <option value="Chờ xác nhận">Chờ xác nhận</option>
                        <option value="Đã xác nhận">Đã xác nhận</option>
                        <option value="Hoàn thành">Hoàn thành</option>
                        <option value="Huỷ bỏ">Huỷ bỏ</option>
                    </select>
                </div>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

const props = defineProps({
    filter: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:filter']);

const updateFilter = (key, value) => {
    emit('update:filter', { ...props.filter, [key]: value });
};

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
                    updateFilter(key, value);
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});
</script>

<style scoped></style>
