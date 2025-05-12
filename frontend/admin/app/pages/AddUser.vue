        <script lang="ts">
        
        </script>
<template>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow">
  <h2 class="text-2xl font-semibold mb-6">Thêm / Chỉnh sửa người dùng</h2>

  <form class="space-y-4">
    <!-- Họ tên -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Họ và tên</label>
      <input type="text" class="w-full p-2 border rounded mt-1" placeholder="Nhập họ và tên" />
    </div>

    <!-- Số điện thoại -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
      <input type="tel" class="w-full p-2 border rounded mt-1" placeholder="Nhập số điện thoại" />
    </div>

    <!-- Email -->
    <div>
      <label class="block text-sm font-medium text-gray-700">Email</label>
      <input type="email" class="w-full p-2 border rounded mt-1" placeholder="Nhập địa chỉ email" />
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

    <!-- Nút lưu căn phải -->
    <div class="flex justify-end">
      <button
        class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition"
      >
        Lưu
      </button>
    </div>
  </form>
</div>

</template>


<style scoped>

</style>