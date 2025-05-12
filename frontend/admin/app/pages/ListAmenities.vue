<script setup lang="ts">
definePageMeta({
  layout: "default",
});
</script>

<template>
  <div class="flex justify-between items-center mb-4">
    <div class="text-2xl font-semibold mb-4">Danh sách tiện ích</div>
    <div class="flex mb-4 text-sm text-gray-500 ml-300 gap-1">
      Trang chủ &gt; Bảng điều khiển &gt;
      <span class="font-semibold text-gray-700">Tiện ích</span>
    </div>
  </div>

  <div class="p-6 bg-white min-h-screen">
    <div class="flex items-center justify-between mb-3">
  <span class="font-semibold text-gray-700">Danh sách tiện ích</span>
  <NuxtLink to="AddAmenities"
    class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition"
  >
    + Thêm tiện ích
  </NuxtLink>
</div>
    <hr class="mb-5 w-full" />

    <!-- Tiện ích 1 -->
    <div class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5">
      <div class="flex items-center space-x-4 p-4 mb-4 py-4">
        <img src="https://via.placeholder.com/80" alt="Hình tiện ích" class="w-20 h-20 object-cover rounded" />
        <div>
          <h3 class="text-lg font-semibold">Nhà trọ Minh Anh</h3>
          <p class="text-sm text-gray-500">Loại: Dãy trọ</p>
        </div>
      </div>

      <!-- Nút thao tác -->
     <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
        <NuxtLink href="EditAmenities" class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full flex items-center space-x-1 hover:bg-red-700 hover:text-white transition">
        
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </NuxtLink>
        
        <button
          class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full flex items-center space-x-1 hover:bg-red-700 hover:text-white transition"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
          <span>Xóa</span>
        </button>
      </div>
    </div>

    <!-- Tiện ích 2 -->
    <div class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5">
      <div class="flex items-center space-x-4 p-4 mb-4 py-4">
        <img src="https://via.placeholder.com/80" alt="Hình tiện ích" class="w-20 h-20 object-cover rounded" />
        <div>
          <h3 class="text-lg font-semibold">Phòng trọ A101</h3>
          <p class="text-sm text-gray-500">Loại: Phòng trọ</p>
        </div>
      </div>

      <!-- Nút thao tác -->
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
        <NuxtLink href="EditMotels" class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full flex items-center space-x-1 hover:bg-red-700 hover:text-white transition">
        
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </NuxtLink>
        
        <button
          class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full flex items-center space-x-1 hover:bg-red-700 hover:text-white transition"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
          <span>Xóa</span>
        </button>
      </div>
    </div>

    <!-- Lặp lại theo danh sách tiện ích -->
  </div>
</template>
