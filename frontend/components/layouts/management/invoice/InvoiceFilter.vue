<template>
    <div class="booking-requests-filter">
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.month" name="month" class="chosen-select">
                        <option value="">Tất cả tháng</option>
                        <option v-for="month in months" :key="month" :value="month">Tháng {{ month }}</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.year" name="year" class="chosen-select">
                        <option value="">Tất cả năm</option>
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

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
                        <option value="Đặt cọc">Đặt cọc</option>
                        <option value="Hàng tháng">Hàng tháng</option>
                    </select>
                </div>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { useNuxtApp } from '#app';
import { useAppToast } from '~/composables/useToast';

const { $api } = useNuxtApp();
const toast = useAppToast();
const months = ref([]);
const years = ref([]);

const props = defineProps({
    filter: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:filter']);

const fetchMonthsAndYears = async () => {
    try {
        const response = await $api('/invoices/months-years', { method: 'GET' });
        months.value = response.data.months;
        years.value = response.data.years;
        // Cập nhật Chosen sau khi dữ liệu được fetch
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated');
            }
        });
    } catch (error) {
        toast.error('Lỗi khi lấy danh sách tháng và năm.');
    }
};

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
    fetchMonthsAndYears();
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
