<script setup lang="ts">
definePageMeta({
  layout: "default", // hoặc tên layout của bạn
});
</script>

<template>
 <div class="max-w-md mx-auto p-6 bg-white rounded-lg shadow">
  <h2 class="text-2xl font-semibold mb-6">Cập nhật thông tin người dùng</h2>

  <form class="space-y-4">
    <!-- Tên (chỉ xem) -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Họ tên</label>
      <input
        type="text"
        class="w-full p-2 border rounded mt-1 bg-gray-100 cursor-not-allowed"
        value="Nguyễn Gia Bảo"
        disabled
      />
    </div>

    <!-- Số điện thoại (chỉ xem) -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
      <input
        type="text"
        class="w-full p-2 border rounded mt-1 bg-gray-100 cursor-not-allowed"
        value="0903******"
        disabled
      />
    </div>

    <!-- Vai trò -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Vai trò</label>
      <select class="w-full p-2 border rounded mt-1">
      
        <option value="host">Chủ trọ</option>
        <option value="user">Người dùng</option>
      </select>
    </div>

    <!-- Trạng thái -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
      <select class="w-full p-2 border rounded mt-1">
        <option value="active">Hoạt động</option>
        <option value="banned">Bị cấm</option>
      </select>
    </div>

    <!-- Nút lưu -->
    <div class="flex justify-end">
      <button
        class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition"
      >
        Lưu thay đổi
      </button>
    </div>
  </form>
</div>


</template>


