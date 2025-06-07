<template>
    <div>
        <Titlebar title="Đặt lịch xem phòng" />

        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Đặt phòng</h3>
            </div>
            <div class="message-reply margin-top-0">
                <div class="row with-forms">
                    <div class="col-lg-6">
                        <label>Ngày bắt đầu:</label>
                        <input type="text" id="date-picker" placeholder="Ngày bắt đầu" readonly="readonly" />
                    </div>
                    <div class="col-lg-6">
                        <label>Thời gian thuê:</label>
                        <select class="modal-duration-select">
                            <option value="">Thời gian</option>
                            <option value="1 năm">1 năm</option>
                            <option value="2 năm">2 năm</option>
                            <option value="3 năm">3 năm</option>
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
                    <ViewingScheduleFilter v-model:filter="filter" @update:filter="fetchBookings" />
                    <ViewingScheduleList
                        :bookings="bookings"
                        :is-loading="isLoading"
                        @reject-booking="rejectBooking"
                        @open-popup="openPopup"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useToast } from 'vue-toastification';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const bookings = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const buttonLoading = ref(false);
const toast = useToast();
const formData = ref({ room_id: null, start_date: '', duration: '', note: '' });

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

const fetchBookings = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/viewing-schedules', { method: 'GET', params: filter.value });
        bookings.value = response.data;
    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu:', error);
    } finally {
        isLoading.value = false;
    }
};

const rejectBooking = async id => {
    isLoading.value = true;
    try {
        await $api(`/viewing-schedules/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchBookings();
        toast.success('Huỷ lịch xem phòng thành công');
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const submitBooking = async () => {
    buttonLoading.value = true;
    try {
        if (!formData.value.room_id || !formData.value.start_date || !formData.value.duration) {
            toast.error('Vui lòng chọn phòng, ngày bắt đầu và thời gian thuê');
            return;
        }

        await $api('/booking', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: formData.value
        });

        toast.success('Đặt phòng thành công');
        window.jQuery.magnificPopup.close();
        await fetchBookings();
        formData.value = { room_id: null, start_date: '', duration: '', note: '' };
    } catch (error) {
        handleBackendError(error);
    } finally {
        buttonLoading.value = false;
    }
};

const openPopup = roomId => {
    formData.value.room_id = roomId;
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
            mainClass: 'my-mfp-zoom-in'
        });
    } else {
        console.error('Magnific Popup không được tải');
    }
};

onMounted(() => {
    fetchBookings();
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.daterangepicker && window.moment) {
            window
                .jQuery('#date-picker')
                .daterangepicker({
                    opens: 'left',
                    singleDatePicker: true,
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
            const $select = window.jQuery('.modal-duration-select').chosen({
                width: '100%',
                no_results_text: 'Không tìm thấy kết quả',
                disable_search: true, // Ép tắt thanh tìm kiếm
                disable_search_threshold: 0 // Tắt dựa trên số lượng tùy chọn
            });

            $select.on('change', event => {
                formData.value.duration = event.target.value;
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
