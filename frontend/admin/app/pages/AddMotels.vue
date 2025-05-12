        <script setup lang="ts">
        
        </script>
<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-semibold mb-6">Thêm dãy trọ mới</h2>

    <form class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Tên phòng</label>
        <input type="text" class="w-full p-2 border rounded mt-1 focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
        <input type="text" class="w-full p-2 border rounded mt-1 focus:ring-2 focus:ring-blue-500" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Hình ảnh</label>
        <input type="file" class="mt-1" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Mô tả</label>
        <textarea rows="4" class="w-full p-2 border rounded mt-1 focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
        Thêm mới
      </button>
    </form>
  </div>
</template>




