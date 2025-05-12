<script setup lang="ts">

definePageMeta({
  layout: "default" // hoặc tên layout của bạn
});

</script>

<template>
  <div class="flex justify-between items-center mb-4">
    <div class="text-2xl font-semibold mb-4">Danh sách khu vực</div>
    <div class="flex mb-4 text-sm text-gray-500 ml-300 gap-1">
      Trang chủ &gt; Bảng điều khiển &gt;
      <span class="font-semibold text-gray-700">Danh sách khu vực</span>
    </div>
  </div>
  <div class="p-6 bg-white min-h-screen">
   <div class="flex items-center justify-between mb-3">
  <span class="font-semibold text-gray-700">Danh sách khu vực</span>
  <NuxtLink to="AddDistricts"
    class="border border-red-500 text-red-500 px-5 py-2 rounded-full hover:bg-red-700 hover:text-white transition"
  >
    + Thêm khu vực
  </NuxtLink >
</div>
<hr class="mb-5 w-full" />

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
          <h3 class="text-lg font-semibold">Tên khu vực (quận 1 )</h3>
          <p class="text-sm text-gray-500">(địa chỉ cụ thể)</p>
          <div class="flex items-center space-x-1 text-yellow-400 text-sm">
            ★ ★ ★ ☆ ☆
            <span class="text-gray-500 ml-2">(12 đánh giá)</span>
          </div>
        </div>
      </div>

      <!-- Bên phải: nút hiện khi hover -->
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
        <NuxtLink href="EditDistricts" class="bg-gray-100 text-gray-700 px-4 py-1 rounded-full flex items-center space-x-1 hover:bg-red-700 hover:text-white transition">
        
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
          <div class="font-medium text-lg">Sticky Band</div>
          <div class="text-sm text-gray-500">Bishop Avenue, New York</div>
          <div class="text-yellow-500 text-sm mt-1">
            ★★★★★ <span class="text-gray-500 ml-1">(23 đánh giá)</span>
          </div>
        </div>
      </div>
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
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
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </button>
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

    <!-- Item 3 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div
        class="flex items-center space-x-4 p-4 mb-4"
      >
        <img
          src=""
          alt="Hotel Govendor"
          class="w-20 h-20 object-cover rounded-md"
        />
        <div class="flex-1">
          <div class="font-medium text-lg">Khách sạn Govendor</div>
          <div class="text-sm text-gray-500">778 Country Street, New York</div>
          <div class="text-yellow-500 text-sm mt-1">
            ★★☆☆☆ <span class="text-gray-500 ml-1">(47 đánh giá)</span>
          </div>
        </div>
      </div>
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
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
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </button>
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

    <!-- Item 4 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div
        class="flex items-center space-x-4 p-4 mb-4"
      >
        <img
          src=""
          alt="Burger House"
          class="w-20 h-20 object-cover rounded-md"
        />
        <div class="flex-1">
          <div class="font-medium text-lg">Burger House</div>
          <div class="text-sm text-gray-500">2726 Shinn Street, New York</div>
          <div class="text-yellow-500 text-sm mt-1">
            ★★★★☆ <span class="text-gray-500 ml-1">(31 đánh giá)</span>
          </div>
        </div>
      </div>
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
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
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </button>
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

    <!-- Item 5 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div
        class="flex items-center space-x-4 p-4 mb-4"
      >
        <img src="" alt="Airport" class="w-20 h-20 object-cover rounded-md" />
        <div class="flex-1">
          <div class="font-medium text-lg">Sân bay</div>
          <div class="text-sm text-gray-500">4512 Duncan Avenue, New York</div>
          <div class="text-yellow-500 text-sm mt-1">
            ★★★★☆ <span class="text-gray-500 ml-1">(46 đánh giá)</span>
          </div>
        </div>
      </div>
      <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
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
              d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
            />
          </svg>
          <span>Chỉnh sửa</span>
        </button>
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

    <!-- Item 6 -->
    <div
      class="bg-white p-4 rounded-lg shadow group flex justify-between items-center hover:bg-gray-50 transition mb-5"
    >
      <div
        class="flex items-center space-x-4 p-4 mb-4"
      >
        <img
          src=""
          alt="Think Coffee"
          class="w-20 h-20 object-cover rounded-md"
        />
        <div class="flex-1">
          <div class="font-medium text-lg">Think Coffee</div>
          <div class="text-sm text-gray-500">215 Terry Lane, New York</div>
          <div class="text-yellow-500 text-sm mt-1">
            ★★★★★ <span class="text-gray-500 ml-1">(31 đánh giá)</span>
          </div>
        </div>
      </div>
    
    <div class="flex space-x-2 opacity-0 group-hover:opacity-100 transition">
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
            d="M15 12H9m4-4H9m4 8H9m12 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h8l4-4z"
          />
        </svg>
        <span>Chỉnh sửa</span>
      </button>
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
