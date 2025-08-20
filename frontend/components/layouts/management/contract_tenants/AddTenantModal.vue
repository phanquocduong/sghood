<template>
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Thêm người ở cùng</h3>
            <p class="booking-subtitle">Vui lòng điền đầy đủ thông tin để thêm người ở cùng.</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="booking-form-grid">
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-user"></i> Họ và tên (*):</label>
                        <input v-model="formData.name" type="text" placeholder="Nhập họ và tên" required />
                    </div>
                    <div class="form-col">
                        <label><i class="fa fa-phone"></i> Số điện thoại (*):</label>
                        <input v-model="formData.phone" type="text" placeholder="Nhập số điện thoại" required />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-envelope"></i> Email:</label>
                        <input v-model="formData.email" type="email" placeholder="Nhập email (không bắt buộc)" />
                    </div>
                    <div class="form-col">
                        <label><i class="fa fa-venus-mars"></i> Giới tính:</label>
                        <select v-model="formData.gender" class="modal-gender-select" ref="genderSelect">
                            <option value="">Chọn giới tính</option>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-calendar"></i> Ngày sinh:</label>
                        <div class="date-input-container">
                            <input type="text" id="birthdate-picker" placeholder="Chọn ngày sinh" readonly="readonly" />
                        </div>
                    </div>
                    <div class="form-col">
                        <label><i class="fa fa-home"></i> Địa chỉ:</label>
                        <input v-model="formData.address" type="text" placeholder="Nhập địa chỉ (không bắt buộc)" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-users"></i> Mối quan hệ với người thuê chính (*):</label>
                        <select v-model="formData.relation_with_primary" class="modal-relation-select" ref="relationSelect" required>
                            <option value="">Chọn mối quan hệ</option>
                            <option value="Vợ/Chồng">Vợ/Chồng</option>
                            <option value="Con">Con</option>
                            <option value="Anh/Chị/Em">Anh/Chị/Em</option>
                            <option value="Bạn bè">Bạn bè</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="booking-note-section">
                <label><i class="fa fa-credit-card"></i> Giấy tờ tùy thân (*):</label>
                <div class="upload-instructions">
                    <h5>Hướng dẫn tải ảnh CCCD</h5>
                    <ul>
                        <li>Tải lên đúng 2 ảnh: mặt trước và mặt sau của căn cước công dân.</li>
                        <li>Đảm bảo ảnh rõ nét, không bị mờ hoặc nhòe.</li>
                        <li>Ảnh không được nghiêng, chụp thẳng để hiển thị đầy đủ thông tin.</li>
                        <li>Cắt background sát với thẻ căn cước, không để các vật thể khác trong ảnh.</li>
                        <li>Định dạng ảnh: JPEG hoặc PNG, kích thước tối đa 2MB mỗi ảnh.</li>
                    </ul>
                </div>
                <p v-if="formData.identity_document.has_valid" class="valid-message">Ảnh căn cước đã hợp lệ, không thể tải lên thêm.</p>
                <p v-else-if="bypassExtract" class="bypass-message">
                    Quét CCCD thất bại nhiều lần. Vui lòng tải ảnh lên để admin xác nhận.
                </p>
                <div class="edit-profile-photo" style="position: relative">
                    <form
                        id="tenant-dropzone-upload"
                        class="dropzone"
                        :class="{ 'dropzone-disabled': formData.identity_document.has_valid }"
                    ></form>
                    <div v-if="extractLoading" class="loading-overlay">
                        <div class="spinner"></div>
                        <p>Đang quét căn cước công dân...</p>
                    </div>
                </div>
            </div>
            <div class="booking-actions">
                <button @click="closeModal" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                <button @click.prevent="handleSubmitTenant" class="button" :disabled="buttonLoading || !isFormComplete">
                    <span v-if="buttonLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ buttonLoading ? 'Đang xử lý...' : 'Thêm người ở cùng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useTenant } from '~/composables/useTenant';

const { $dropzone } = useNuxtApp();
const toast = useAppToast();

const props = defineProps({
    contractId: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['addTenant', 'close']);

const formData = ref({
    name: '',
    phone: '',
    email: '',
    gender: '',
    birthdate: '',
    address: '',
    relation_with_primary: '',
    identity_document: {
        identity_number: '',
        full_name: '',
        year_of_birth: '',
        date_of_issue: '',
        place_of_issue: '',
        permanent_address: '',
        has_valid: false
    },
    identity_images: []
});

const buttonLoading = ref(false);
const extractLoading = ref(false);
const bypassExtract = ref(false);
const extractErrorCount = ref(0);
const identityImages = ref([]);
const genderSelect = ref(null);
const relationSelect = ref(null);
const dropzoneInstance = ref(null);

const isFormComplete = computed(() => {
    return (
        formData.value.name &&
        formData.value.phone &&
        formData.value.relation_with_primary &&
        (formData.value.identity_document.has_valid || identityImages.value.length === 2)
    );
});

const { handleIdentityUpload, submitTenant } = useTenant({
    formData,
    identityImages,
    extractLoading,
    toast,
    dropzoneInstance,
    contractId: props.contractId,
    bypassExtract,
    extractErrorCount
});

const initChosenSelect = (selectRef, options = {}) => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải');
        toast.error('Không thể tải thư viện Chosen. Vui lòng thử lại sau.');
        return;
    }
    const $select = window.jQuery(selectRef.value).chosen({
        width: '100%',
        no_results_text: 'Không tìm thấy kết quả',
        ...options
    });
    $select.on('change', event => {
        if (selectRef === genderSelect) {
            formData.value.gender = event.target.value;
        } else if (selectRef === relationSelect) {
            formData.value.relation_with_primary = event.target.value;
        }
    });
    return $select;
};

const initDatePicker = (elementId, field) => {
    if (!window.jQuery || !window.jQuery.fn.daterangepicker || !window.moment) {
        console.error('jQuery, Moment hoặc Daterangepicker không được tải');
        toast.error('Không thể tải thư viện Daterangepicker. Vui lòng thử lại sau.');
        return;
    }

    const $datePicker = window.jQuery(`#${elementId}`);
    $datePicker
        .daterangepicker({
            opens: 'left',
            singleDatePicker: true,
            maxDate: window.moment(),
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
            field.value.birthdate = picker.startDate.format('DD/MM/YYYY');
            $datePicker.val(picker.startDate.format('DD/MM/YYYY'));
        })
        .on('cancel.daterangepicker', () => {
            field.value.birthdate = '';
            $datePicker.val('');
        })
        .on('showCalendar.daterangepicker', () => {
            window.jQuery('.daterangepicker').addClass('calendar-animated');
        })
        .on('show.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-hidden').addClass('calendar-visible');
        })
        .on('hide.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
        });

    $datePicker.val(field.value.birthdate || '');
};

const initDropzone = () => {
    if (!$dropzone) {
        console.error('Dropzone không được tải');
        toast.error('Không thể tải thư viện Dropzone. Vui lòng thử lại sau.');
        return;
    }

    dropzoneInstance.value = new $dropzone('#tenant-dropzone-upload', {
        url: '/',
        autoProcessQueue: true,
        maxFilesize: 5,
        acceptedFiles: 'image/jpeg,image/png',
        clickable: !formData.value.identity_document.has_valid,
        dictDefaultMessage: '<i class="sl sl-icon-plus"></i>Tải lên 2 ảnh căn cước công dân mặt trước và mặt sau',
        init() {
            this.on('queuecomplete', () => {
                const files = [...this.getQueuedFiles(), ...this.getAcceptedFiles()];
                if (files.length) handleIdentityUpload(files);
            });
            this.on('error', (file, message) => {
                console.error('Error uploading file:', message);
            });
            if (formData.value.identity_document.has_valid) this.disable();
        }
    });
};

const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
    if (dropzoneInstance.value) {
        dropzoneInstance.value.removeAllFiles(true);
    }
    emit('close');
};

const openModal = async () => {
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        toast.error('Không thể tải Magnific Popup. Vui lòng thử lại sau.');
        return;
    }

    formData.value = {
        name: '',
        phone: '',
        email: '',
        gender: '',
        birthdate: '',
        address: '',
        relation_with_primary: '',
        identity_document: {
            identity_number: '',
            full_name: '',
            year_of_birth: '',
            date_of_issue: '',
            place_of_issue: '',
            permanent_address: '',
            has_valid: false
        },
        identity_images: []
    };
    identityImages.value = [];
    bypassExtract.value = false;
    extractErrorCount.value = 0;

    window.jQuery.magnificPopup.open({
        items: { src: '#small-dialog', type: 'inline' },
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        closeOnBgClick: false,
        callbacks: {
            open: () => {
                nextTick(() => {
                    initChosenSelect(genderSelect, { placeholder_text_single: 'Chọn giới tính', allow_single_deselect: true });
                    initChosenSelect(relationSelect, { placeholder_text_single: 'Chọn mối quan hệ', allow_single_deselect: true });
                    initDatePicker('birthdate-picker', formData);
                    initDropzone();
                });
            }
        }
    });
};

const handleSubmitTenant = async () => {
    buttonLoading.value = true;
    try {
        await submitTenant();
        emit('addTenant');
        closeModal();
    } catch (error) {
        console.error('Error submitting tenant:', error);
    } finally {
        buttonLoading.value = false;
    }
};

onMounted(() => {
    nextTick(() => {
        if ($dropzone) {
            $dropzone.autoDiscover = false;
        }
    });
});

defineExpose({ openModal });
</script>

<style scoped>
@import '~/public/css/viewing-schedules.css';

.dropzone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.2s ease;
}

.dropzone:hover {
    border-color: #59b02c;
}

.dropzone-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
    border-color: #ccc !important;
}

.valid-message {
    color: #59b02c;
    font-size: 1.4rem;
    line-height: 2.2rem;
    margin-top: 10px;
    text-align: center;
}

.bypass-message {
    color: #f39c12;
    font-weight: bold;
    margin-top: 10px;
}

.upload-instructions {
    margin-bottom: 15px;
    text-align: left;
}

.upload-instructions h5 {
    font-size: 1.6rem;
    color: #333;
    margin-bottom: 12px;
}

.upload-instructions ul {
    list-style-type: disc;
    padding-left: 16px;
    font-size: 1.4rem;
    color: #555;
}

.upload-instructions li {
    margin-bottom: 8px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-overlay .spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #f91942;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

.loading-overlay p {
    font-size: 1.4rem;
    color: #333;
    font-weight: 500;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
