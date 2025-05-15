<script setup lang="ts">
definePageMeta({
    layout: 'default' // hoặc tên layout của bạn
});
</script>

<template>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
        <h2 class="text-2xl font-semibold mb-6">Chỉnh sửa tiện ích</h2>

        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tên tiện ích</label>
                <input type="text" class="w-full p-2 border rounded mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Loại</label>
                <select class="w-full p-2 border rounded mt-1">
                    <option>Dãy trọ</option>
                    <option>Phòng trọ</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Hình ảnh hiện tại</label>
                <input type="file" class="mt-1" />
            </div>

            <!-- Căn phải nút Lưu -->
            <div class="flex justify-end">
                <button class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</template>
