import { timestamp } from '@vueuse/core';
import { defineStore } from 'pinia';

// Định nghĩa store 'behavior' để quản lý hành vi người dùng
export const useBehaviorStore = defineStore('behavior', {
    // Trạng thái ban đầu của store
    state: () => ({
        seenChatIntro: false, // Biến kiểm tra xem người dùng đã xem hướng dẫn chat chưa
        visitedPages: [], // Danh sách các trang người dùng đã truy cập
        actionHistory: [], // Lịch sử hành động của người dùng
        chat: '' // Nội dung chat hiện tại
    }),

    // Các hành động để thao tác với trạng thái
    actions: {
        // Đánh dấu rằng người dùng đã xem hướng dẫn chat
        markChatIntroSeen() {
            this.seenChatIntro = true;
        },

        // Thêm một trang vào danh sách các trang đã truy cập
        addVisitedPage(page) {
            // Chỉ thêm nếu trang chưa có trong danh sách
            if (!this.visitedPages.includes(page)) {
                this.visitedPages.push(page);
            }
        },

        // Ghi lại một hành động của người dùng
        logAction(page, action) {
            // Thêm hành động vào lịch sử với thông tin trang, hành động và thời gian
            this.actionHistory.push({
                page, // Trang nơi hành động được thực hiện
                action, // Hành động cụ thể
                timestamp: Date.now() // Thời gian thực hiện hành động
            });
        },

        // Cập nhật nội dung chat
        updateChat(text) {
            this.chat = text;
        },

        // Xóa nội dung chat
        clearChat() {
            this.chat = '';
        }
    },

    // Kích hoạt lưu trữ trạng thái lâu dài (persistence)
    persist: true // Lưu trạng thái vào localStorage hoặc cơ chế lưu trữ khác
});
