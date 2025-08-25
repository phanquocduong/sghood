<!-- Template cho trang điều khoản hợp đồng -->
<template>
    <div class="terms-page">
        <!-- Thanh tiến trình cuộn trang -->
        <div class="scroll-indicator">
            <div class="scroll-progress" :style="{ width: scrollProgress + '%' }"></div>
        </div>

        <!-- Trạng thái đang tải -->
        <div v-if="pending" class="loading-container">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Đang tải điều khoản hợp đồng...</p>
            </div>
        </div>

        <!-- Trạng thái lỗi -->
        <div v-else-if="error" class="error-container">
            <div class="error-content">
                <h2>❌ Có lỗi xảy ra</h2>
                <p>{{ error.message || 'Không thể tải điều khoản hợp đồng' }}</p>
                <!-- Nút thử lại khi gặp lỗi -->
                <button @click="refresh()" class="retry-btn">🔄 Thử lại</button>
            </div>
        </div>

        <!-- Nội dung chính -->
        <div v-else class="container">
            <div class="terms-container fade-in">
                <!-- Tiêu đề chính của trang -->
                <div class="terms-header">
                    <h2>ĐIỀU KHOẢN HỢP ĐỒNG CHO THUÊ NHÀ TRỌ</h2>
                    <p class="subtitle">Quy định và trách nhiệm của các bên</p>
                </div>

                <!-- Nội dung điều khoản -->
                <div class="terms-content">
                    <!-- Phần trách nhiệm bên cho thuê (Bên A) -->
                    <div class="section" ref="sectionA">
                        <h3 class="section-title">🏛️ TRÁCH NHIỆM BÊN CHO THUÊ (BÊN A)</h3>
                        <div class="section-content">
                            <!-- Hiển thị danh sách trách nhiệm của bên A -->
                            <div
                                v-for="(responsibility, key) in terms?.party_a_responsibilities || {}"
                                :key="key"
                                class="responsibility-item"
                                :class="{ 'animate-in': isVisible }"
                            >
                                {{ responsibility }}
                            </div>
                        </div>
                    </div>

                    <!-- Phần trách nhiệm bên thuê (Bên B) -->
                    <div class="section" ref="sectionB">
                        <h3 class="section-title">👤 TRÁCH NHIỆM BÊN THUÊ (BÊN B)</h3>
                        <div class="section-content">
                            <!-- Hiển thị danh sách trách nhiệm của bên B -->
                            <div
                                v-for="(responsibility, key) in terms?.party_b_responsibilities || {}"
                                :key="key"
                                class="responsibility-item"
                                :class="{ 'animate-in': isVisible }"
                            >
                                {{ responsibility }}
                            </div>
                        </div>
                    </div>

                    <!-- Phần quyền chấm dứt hợp đồng -->
                    <div class="section" ref="sectionTermination">
                        <h3 class="section-title">⚖️ QUYỀN ĐƠN PHƯƠNG CHẤM DỨT HỢP ĐỒNG</h3>
                        <div class="section-content">
                            <!-- Ghi chú quan trọng -->
                            <div class="important-note">
                                <h4>Lưu ý quan trọng</h4>
                                <p style="margin-bottom: 0">Bên cho thuê có quyền đơn phương chấm dứt hợp đồng trong các trường hợp sau:</p>
                            </div>

                            <!-- Hiển thị danh sách quyền chấm dứt hợp đồng -->
                            <div v-for="(right, key) in terms?.termination_rights || {}" :key="key" class="termination-item">
                                {{ right }}
                            </div>
                        </div>
                    </div>

                    <!-- Phần điều khoản chung -->
                    <div class="section" ref="sectionGeneral">
                        <h3 class="section-title">📋 ĐIỀU KHOẢN CHUNG</h3>
                        <div class="section-content">
                            <!-- Hiển thị danh sách điều khoản chung -->
                            <div
                                v-for="(term, key) in terms?.general_terms || {}"
                                :key="key"
                                class="general-item"
                                :class="{ 'animate-in': isVisible }"
                            >
                                {{ term }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useBehaviorStore } from '~/stores/behavior';
import { useRoute } from 'vue-router';
import { useHead } from '#app';

// Cấu hình SEO cho trang điều khoản hợp đồng
useHead({
    title: 'SGHood - Điều Khoản Hợp Đồng Thuê Trọ', // Tiêu đề trang
    meta: [
        { charset: 'utf-8' }, // Thiết lập mã hóa ký tự
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Responsive viewport
        {
            hid: 'description',
            name: 'description',
            content:
                'Điều khoản hợp đồng thuê trọ của SGHood, quy định chi tiết trách nhiệm bên cho thuê, bên thuê và quyền chấm dứt hợp đồng.' // Mô tả SEO
        },
        {
            name: 'keywords',
            content: 'SGHood, điều khoản hợp đồng, thuê trọ, nhà trọ TP. Hồ Chí Minh, hợp đồng thuê nhà, quy định thuê trọ' // Từ khóa SEO
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả
        // Open Graph metadata
        {
            property: 'og:title',
            content: 'SGHood - Điều Khoản Hợp Đồng Thuê Trọ' // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content:
                'Điều khoản hợp đồng thuê trọ của SGHood, quy định chi tiết trách nhiệm bên cho thuê, bên thuê và quyền chấm dứt hợp đồng.' // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        { property: 'og:url', content: 'https://sghood.com.vn/dieu-khoan-hop-dong' } // URL Open Graph
    ]
});

// Lấy cấu hình từ state toàn cục
const config = useState('configs');

// Biến lưu trữ điều khoản hợp đồng sau khi parse từ JSON
const terms = ref(null);
// Biến trạng thái đang tải
const pending = ref(true);
// Biến lưu trữ lỗi nếu có
const error = ref(null);

// Hàm parse dữ liệu điều khoản từ text sang JSON
const parseTerms = () => {
    try {
        if (config.value?.rental_contract_terms) {
            const termsArray = JSON.parse(config.value.rental_contract_terms); // Chuyển đổi chuỗi JSON thành object
            if (Array.isArray(termsArray) && termsArray.length > 0) {
                terms.value = termsArray[0]; // Lấy object đầu tiên từ mảng
                pending.value = false; // Tắt trạng thái đang tải
            } else {
                throw new Error('Dữ liệu điều khoản hợp đồng không đúng định dạng');
            }
        } else {
            throw new Error('Không có dữ liệu điều khoản hợp đồng');
        }
    } catch (err) {
        error.value = err; // Lưu lỗi
        pending.value = false; // Tắt trạng thái đang tải
    }
};

// Biến phản hồi (reactive)
const scrollProgress = ref(0); // Tiến trình cuộn trang
const isVisible = ref(false); // Trạng thái hiển thị các mục
const currentYear = new Date().getFullYear(); // Năm hiện tại

// Tham chiếu tới các phần tử DOM
const sectionA = ref(); // Phần trách nhiệm bên A
const sectionB = ref(); // Phần trách nhiệm bên B
const sectionTermination = ref(); // Phần quyền chấm dứt hợp đồng
const sectionGeneral = ref(); // Phần điều khoản chung
const sectionContact = ref(); // Phần liên hệ (không sử dụng trong template hiện tại)

// Store và route
const behavior = useBehaviorStore(); // Store theo dõi hành vi người dùng
const route = useRoute(); // Lấy thông tin route hiện tại

// Hàm cập nhật tiến trình cuộn trang
const updateScrollProgress = () => {
    const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100; // Tính phần trăm cuộn
    scrollProgress.value = Math.min(scrolled, 100); // Giới hạn tối đa 100%
};

// Hàm làm mới trang khi gặp lỗi
const refresh = () => {
    location.reload(); // Tải lại trang
};

// Hàm theo dõi các phần tử khi vào khung nhìn
const observeElements = () => {
    const observerOptions = {
        threshold: 0.1, // Kích hoạt khi 10% phần tử hiển thị
        rootMargin: '0px 0px -50px 0px' // Khoảng cách lề
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible'); // Thêm lớp visible khi vào khung nhìn
                isVisible.value = true; // Cập nhật trạng thái hiển thị
            }
        });
    }, observerOptions);

    // Theo dõi tất cả các phần
    const sections = [sectionA, sectionB, sectionTermination, sectionGeneral, sectionContact];
    sections.forEach(section => {
        if (section.value) {
            observer.observe(section.value); // Quan sát phần tử
        }
    });
};

// Lifecycle hooks
onMounted(() => {
    parseTerms(); // Parse điều khoản khi component được mount

    // Thêm sự kiện cuộn trang
    window.addEventListener('scroll', updateScrollProgress);

    // Khởi tạo Intersection Observer
    nextTick(() => {
        observeElements();
    });

    // Ghi lại hành vi người dùng
    behavior.addVisitedPage(route.path); // Lưu trang đã truy cập
    behavior.logAction(route.path, 'dieu-khoan'); // Ghi log hành động
});

// Xóa sự kiện cuộn khi component bị hủy
onUnmounted(() => {
    window.removeEventListener('scroll', updateScrollProgress);
});

// Theo dõi thay đổi config
watch(config, () => {
    parseTerms(); // Parse lại điều khoản khi config thay đổi
    nextTick(() => {
        observeElements(); // Cập nhật lại Intersection Observer
    });
});
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
@import '~/public/css/contract-terms.css';
</style>
