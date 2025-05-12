<script setup lang="ts">
    definePageMeta({
  layout: "default", // hoặc tên layout của bạn
});

</script>


<template>
  <div class="flex justify-between items-center mb-4">
    <div class="text-2xl font-semibold mb-4">Danh sách người dùng</div>
    <div class="flex mb-4 text-sm text-gray-500 ml-300 gap-1">
      Trang chủ &gt; Bảng điều khiển &gt;
      <span class="font-semibold text-gray-700">Danh sách người dùng</span>
    </div>
  </div>
  <div class="p-6 bg-white min-h-screen">
   <div class="flex items-center justify-between mb-3">
  <span class="font-semibold text-gray-700">Danh sách người dùng</span>
  <NuxtLink to="AddUser"
    class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition"
  >
    + Thêm người dùng
  </NuxtLink>

</div>
    <hr class="mb-5 w-full " />
    <!-- Item 1 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div class="flex items-center space-x-4 p-4 mb-4 py-4">
        <img
          src="https://via.placeholder.com/80"
          alt="Image"
          class="w-20 h-20 object-cover rounded"
        />
        <div>
          <h3 class="text-lg font-semibold"> Nguyễn Gia Bảo</h3>
          <p class="text-sm text-gray-500 mb-1"> 0903******</p>
          <p class="text-sm text-gray-500 mb-1">l3monsu@gmail.com</p>
          <p class="text-sm font-semibold mb-1  ">Chủ trọ</p>
          <p class="text-sm text-red-700 bg-red-100 inline-block px-2 py-1 rounded  ">Bị cấm</p> 
        </div>
      </div>

      <!-- Bên phải: nút hiện khi hover -->
       <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
        <NuxtLink to="EditUser"
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
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </NuxtLink to="EditUser">
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
    <!-- Item 2 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div
        class=" flex items-center space-x-4 p-4 mb-4"
      >
        <img
          src=""
          alt="Sticky Band"
          class="w-20 h-20 object-cover rounded-md"
        />
        <div class="flex-1">
         <h3 class="text-lg font-semibold"> Nguyễn Gia Bảo</h3>
          <p class="text-sm text-gray-500  mb-1 "> 0903******</p>
          <p class="text-sm text-gray-500  mb-1 ">l3monsu@gmail.com</p>
          <p class="text-sm font-semibold mb-1  ">Người dùng</p>
          <p class="text-sm text-green-700 bg-green-100 inline-block px-2 py-1 rounded mt-1 ">Hoạt động</p>
        </div>
      </div>
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
        <NuxtLink to="EditUser"
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
  </div>
</template>

<style scoped>

</style>