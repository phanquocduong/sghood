<template>
    <div>
        <Titlebar title="Lịch xem nhà trọ" />

        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Đặt phòng</h3>
            </div>
            <div class="message-reply margin-top-0">
                <div class="row with-forms">
                    <div class="col-lg-6">
                        <label>Phòng:</label>
                        <select v-model="formData.room_id" class="modal-room-select" ref="roomSelect">
                            <option value="">Chọn phòng</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label>Ngày bắt đầu:</label>
                        <input type="text" id="date-picker" placeholder="Ngày bắt đầu" readonly="readonly" />
                    </div>
                    <div class="col-lg-6">
                        <label>Thời gian thuê:</label>
                        <select v-model="formData.duration" class="modal-duration-select" ref="durationSelect">
                            <option value="">Thời gian</option>
                            <option value="1 năm">1 năm</option>
                            <option value="2 năm">2 năm</option>
                            <option value="3 năm">3 năm</option>
                            <option value="4 năm">4 năm</option>
                            <option value="5 năm">5 năm</option>
                        </select>
                    </div>
                </div>

                <textarea v-model="formData.note" cols="40" rows="2" placeholder="Ghi chú"></textarea>

                <button @click.prevent="submitBooking" class="button" :disabled="buttonLoading">
                    <span v-if="buttonLoading" class="spinner"></span>
                    {{ buttonLoading ? 'Đang xử lý...' : 'Đặt phòng' }}
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ScheduleFilter v-model:filter="filter" @update:filter="fetchSchedules" />
                    <ScheduleList :items="schedules" :is-loading="isLoading" @reject-item="rejectSchedule" @open-popup="openPopup" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useToast } from 'vue-toastification';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const schedules = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const buttonLoading = ref(false);
const toast = useToast();
const formData = ref({ room_id: null, start_date: '', duration: '', note: '' });
const rooms = ref([]);
const roomSelect = ref(null);
const durationSelect = ref(null);

const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error);
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0]));
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

const fetchSchedules = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/schedules', { method: 'GET', params: filter.value });
        schedules.value = response.data;
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const rejectSchedule = async ({ id }) => {
    isLoading.value = true;
    try {
        await $api(`/schedules/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchSchedules();
        toast.success('Hủy lịch xem nhà trọ thành công');
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const fetchRooms = async motelId => {
    try {
        const response = await $api(`/motels/${motelId}/rooms`, { method: 'GET' });
        rooms.value = response;
    } catch (error) {
        handleBackendError(error);
    }
};

const submitBooking = async () => {
    buttonLoading.value = true;
    try {
        if (!formData.value.room_id || !formData.value.start_date || !formData.value.duration) {
            toast.error('Vui lòng chọn phòng, ngày bắt đầu và thời gian thuê');
            return;
        }

        await $api('/bookings', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: formData.value
        });

        toast.success('Đặt phòng thành công');
        window.jQuery.magnificPopup.close();
        await fetchSchedules();
        formData.value = { room_id: null, start_date: '', duration: '', note: '' };
        rooms.value = [];
    } catch (error) {
        handleBackendError(error);
    } finally {
        buttonLoading.value = false;
    }
};

const updateChosenSelect = (selectRef, value = null) => {
    nextTick(() => {
        if (window.jQuery && selectRef.value) {
            const $select = window.jQuery(selectRef.value);
            if ($select.data('chosen')) {
                $select.trigger('chosen:updated');
                if (value !== null) {
                    $select.val(value).trigger('chosen:updated');
                }
            }
        }
    });
};

const openPopup = async motelId => {
    await fetchRooms(motelId);
    formData.value.room_id = null;

    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.open({
            items: {
                src: '#small-dialog',
                type: 'inline'
            },
            fixedContentPos: false,
            fixedBgPos: true,
            overflowY: 'auto',
            closeBtnInside: true,
            preloader: false,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in',
            callbacks: {
                open: function () {
                    updateChosenSelect(roomSelect);
                }
            }
        });
    } else {
        console.error('Magnific Popup không được tải');
    }
};

watch(
    rooms,
    () => {
        updateChosenSelect(roomSelect);
    },
    { deep: true }
);

onMounted(() => {
    fetchSchedules();
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.daterangepicker && window.moment) {
            const tomorrow = window.moment().add(15, 'days');
            window
                .jQuery('#date-picker')
                .daterangepicker({
                    opens: 'left',
                    singleDatePicker: true,
                    minDate: tomorrow,
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Xác nhận',
                        cancelLabel: 'Hủy',
                        daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                        monthNames: [
                            'Tháng 1',
                            'Tháng 2',
                            'Tháng 3',
                            'Tháng 4',
                            'Tháng 5',
                            'Tháng 6',
                            'Tháng 7',
                            'Tháng 8',
                            'Tháng 9',
                            'Tháng 10',
                            'Tháng 11',
                            'Tháng 12'
                        ]
                    }
                })
                .on('apply.daterangepicker', (ev, picker) => {
                    formData.value.start_date = picker.startDate.format('DD/MM/YYYY');
                });

            window.jQuery('#date-picker').on('showCalendar.daterangepicker', () => {
                window.jQuery('.daterangepicker').addClass('calendar-animated');
            });
            window.jQuery('#date-picker').on('show.daterangepicker', () => {
                window.jQuery('.daterangepicker').addClass('calendar-visible').removeClass('calendar-hidden');
            });
            window.jQuery('#date-picker').on('hide.daterangepicker', () => {
                window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
            });
        } else {
            console.error('jQuery, Moment hoặc daterangepicker không được tải');
        }

        if (window.jQuery && window.jQuery.fn.chosen) {
            const $durationSelect = window.jQuery(durationSelect.value).chosen({
                width: '100%',
                no_results_text: 'Không tìm thấy kết quả',
                disable_search: true,
                disable_search_threshold: 0
            });

            $durationSelect.on('change', event => {
                formData.value.duration = event.target.value;
            });

            const $roomSelect = window.jQuery(roomSelect.value).chosen({
                width: '100%',
                no_results_text: 'Không tìm thấy phòng',
                placeholder_text_single: 'Chọn phòng',
                allow_single_deselect: true
            });

            $roomSelect.on('change', event => {
                formData.value.room_id = event.target.value;
            });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});
</script>

<style scoped>
input#date-picker {
    border: 1px solid #dbdbdb;
    box-shadow: 0 1px 3px 0px rgba(0, 0, 0, 0.08);
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
