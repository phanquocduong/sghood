<template>
    <div class="terms-page">
        <!-- Scroll Progress Indicator -->
        <div class="scroll-indicator">
            <div class="scroll-progress" :style="{ width: scrollProgress + '%' }"></div>
        </div>

        <!-- Loading State -->
        <div v-if="pending" class="loading-container">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Đang tải điều khoản hợp đồng...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-container">
            <div class="error-content">
                <h2>❌ Có lỗi xảy ra</h2>
                <p>{{ error.message || 'Không thể tải điều khoản hợp đồng' }}</p>
                <button @click="refresh()" class="retry-btn">🔄 Thử lại</button>
            </div>
        </div>

        <!-- Main Content -->
        <div v-else class="container">
            <div class="terms-container fade-in">
                <div class="terms-header">
                    <h2>ĐIỀU KHOẢN HỢP ĐỒNG CHO THUÊ NHÀ TRỌ</h2>
                    <p class="subtitle">Quy định và trách nhiệm của các bên</p>
                </div>

                <div class="terms-content">
                    <!-- Trách nhiệm Bên A -->
                    <div class="section" ref="sectionA">
                        <h3 class="section-title">🏛️ TRÁCH NHIỆM BÊN CHO THUÊ (BÊN A)</h3>
                        <div class="section-content">
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

                    <!-- Trách nhiệm Bên B -->
                    <div class="section" ref="sectionB">
                        <h3 class="section-title">👤 TRÁCH NHIỆM BÊN THUÊ (BÊN B)</h3>
                        <div class="section-content">
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

                    <!-- Quyền chấm dứt hợp đồng -->
                    <div class="section" ref="sectionTermination">
                        <h3 class="section-title">⚖️ QUYỀN ĐƠN PHƯƠNG CHẤM DỨT HỢP ĐỒNG</h3>
                        <div class="section-content">
                            <div class="important-note">
                                <h4>Lưu ý quan trọng</h4>
                                <p style="margin-bottom: 0">Bên cho thuê có quyền đơn phương chấm dứt hợp đồng trong các trường hợp sau:</p>
                            </div>

                            <div v-for="(right, key) in terms?.termination_rights || {}" :key="key" class="termination-item">
                                {{ right }}
                            </div>
                        </div>
                    </div>

                    <!-- Điều khoản chung -->
                    <div class="section" ref="sectionGeneral">
                        <h3 class="section-title">📋 ĐIỀU KHOẢN CHUNG</h3>
                        <div class="section-content">
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

// Meta data for SEO
useHead({
    title: 'Điều Khoản Hợp Đồng Thuê Trọ - Quản Lý Thuê Trọ Thông Minh',
    meta: [
        {
            name: 'description',
            content:
                'Điều khoản hợp đồng cho thuê nhà trọ chi tiết, quy định trách nhiệm của các bên và quyền đơn phương chấm dứt hợp đồng.'
        },
        { name: 'keywords', content: 'điều khoản, hợp đồng thuê trọ, nhà trọ, quản lý thuê trọ' },
        { property: 'og:title', content: 'Điều Khoản Hợp Đồng Thuê Trọ' },
        { property: 'og:description', content: 'Xem chi tiết điều khoản hợp đồng cho thuê nhà trọ' },
        { property: 'og:type', content: 'website' }
    ]
});

// Use config from useState
const config = useState('configs');

// Parse rental_contract_terms from text to JSON
const terms = ref(null);
const pending = ref(true);
const error = ref(null);

const parseTerms = () => {
    try {
        if (config.value?.rental_contract_terms) {
            terms.value = JSON.parse(config.value.rental_contract_terms);
            pending.value = false;
        } else {
            throw new Error('Không có dữ liệu điều khoản hợp đồng');
        }
    } catch (err) {
        error.value = err;
        pending.value = false;
    }
};

// Reactive data
const scrollProgress = ref(0);
const isVisible = ref(false);
const currentYear = new Date().getFullYear();

// Template refs
const sectionA = ref();
const sectionB = ref();
const sectionTermination = ref();
const sectionGeneral = ref();
const sectionContact = ref();

// Behavior store
const behavior = useBehaviorStore();
const route = useRoute();

// Methods
const updateScrollProgress = () => {
    const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
    scrollProgress.value = Math.min(scrolled, 100);
    showBackToTop.value = window.scrollY > 300;
};

const refresh = () => {
    // Nếu cần refresh dữ liệu, có thể gọi lại API hoặc reload configs
    location.reload();
};

const observeElements = () => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                isVisible.value = true;
            }
        });
    }, observerOptions);

    // Observe all sections
    const sections = [sectionA, sectionB, sectionTermination, sectionGeneral, sectionContact];
    sections.forEach(section => {
        if (section.value) {
            observer.observe(section.value);
        }
    });
};

// Lifecycle hooks
onMounted(() => {
    // Parse terms on mount
    parseTerms();

    // Add scroll event listener
    window.addEventListener('scroll', updateScrollProgress);

    // Initialize intersection observer
    nextTick(() => {
        observeElements();
    });

    // Log behavior
    behavior.addVisitedPage(route.path);
    behavior.logAction(route.path, 'dieu-khoan');
});

onUnmounted(() => {
    window.removeEventListener('scroll', updateScrollProgress);
});

// Watch for config changes
watch(config, () => {
    parseTerms();
    nextTick(() => {
        observeElements();
    });
});
</script>

<style scoped>
.terms-page {
    line-height: 1.8;
    color: #2c2c2c;
    background: #fff;
    min-height: 100vh;
}

.container {
    max-width: 960px;
    margin: 0 auto;
    padding: 32px 16px;
}

/* Header */
.terms-header {
    text-align: center;
    margin-bottom: 36px;
}

.terms-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #f91942;
    margin-bottom: 8px;
}

.subtitle {
    font-size: 16px;
    color: #666;
}

/* Section Titles */
.section-title {
    font-size: 18px;
    font-weight: 600;
    border-left: 4px solid #f91942;
    padding-left: 12px;
    margin-bottom: 16px;
    color: #333;
}

/* Section Block */
.section {
    margin-bottom: 32px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s ease;
}

.section.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Item Blocks */
.responsibility-item,
.general-item {
    background: #f9f9f9;
    padding: 12px 16px;
    border-radius: 6px;
    border-left: 3px solid #ccc;
    margin-bottom: 12px;
    position: relative;
    transition: border-color 0.3s ease;
}

.responsibility-item:hover,
.general-item:hover {
    border-left-color: #f91942;
}

.responsibility-item::before,
.general-item::before {
    content: '•';
    color: #f91942;
    font-weight: bold;
    margin-right: 8px;
}

/* Thông báo chấm dứt */
.termination-item {
    background: #fff5f5;
    border-left: 4px solid #f91942;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 12px;
    position: relative;
}

.termination-item::before {
    content: '⚠';
    margin-right: 8px;
    color: #f91942;
    font-weight: bold;
}

/* Ghi chú */
.important-note {
    background: #fffde7;
    border-left: 4px solid #fbc02d;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.important-note h4 {
    margin-bottom: 8px;
    color: #333;
    font-size: 16px;
    font-weight: 600;
}

/* Scroll Progress Bar */
.scroll-indicator {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #f0f0f0;
    z-index: 100;
}

.scroll-progress {
    height: 100%;
    background: #f91942;
    transition: width 0.2s ease;
}

/* Responsive */
@media (max-width: 768px) {
    .section-title {
        font-size: 16px;
    }

    .terms-header h2 {
        font-size: 20px;
    }

    .container {
        padding: 20px 12px;
    }
}
</style>
