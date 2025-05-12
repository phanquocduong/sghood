<script setup lang="ts">
definePageMeta({
  layout: "default",
})
const showMore = ref(false)
const showMore1 = ref(false)
const showMore2 = ref(false)
const showMore3 = ref(false)
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 gap-6 items-start">
    
    <!-- Phòng 1 -->
    <div class="bg-white rounded-xl shadow p-5 space-y-3 relative group">
      <!-- Nút -->
      <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition duration-300 flex gap-2">
          <NuxtLink to="/AddMotels" class="bg-green-100 text-green-800 px-3 py-1 text-sm rounded hover:bg-green-600 hover:text-white transition">
          Thêm
        </NuxtLink>
        <NuxtLink to="/EditMotels" class="bg-blue-100 text-blue-800 px-3 py-1 text-sm rounded hover:bg-blue-600 hover:text-white transition">
          Chỉnh sửa
        </NuxtLink>
        <button class="bg-red-100 text-red-700 px-3 py-1 text-sm rounded hover:bg-red-600 hover:text-white transition">
          Xóa
        </button>
      </div>

      <h2 class="text-xl font-bold">Phòng VIP 101</h2>
      <div class="text-blue-600 font-semibold">500.000đ / đêm</div>
      <div class="text-gray-600">Diện tích: 25.5 m²</div>
      <div class="text-sm text-green-700 bg-green-100 inline-block px-2 py-1 rounded">Còn trống</div>
      <div class="relative">
        <h3 class="mt-3 font-semibold">Tiện nghi:</h3>
        <ul class="list-disc list-inside text-gray-700 text-sm">
          <li>Máy lạnh</li>
          <li>Wifi</li>
          <li v-if="showMore">Tivi</li>
          <li v-if="showMore">Bồn tắm</li>
          <li v-if="showMore">Máy giặt</li>
          <li v-if="showMore">Bếp điện</li>
        </ul>
        <div class="mt-2 flex justify-center">
          <button @click="showMore = !showMore" class="mt-2 text-blue-600 text-sm hover:scale-110 transition-transform duration-300">
            {{ showMore ? 'Thu gọn ▲' : 'Xem thêm ▼' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Phòng 2 -->
    <div class="bg-white rounded-xl shadow p-5 space-y-3 relative group">
       <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition duration-300 flex gap-2">
          <NuxtLink to="/AddMotels" class="bg-green-100 text-green-800 px-3 py-1 text-sm rounded hover:bg-green-600 hover:text-white transition">
          Thêm
        </NuxtLink>
        <NuxtLink to="/EditMotels" class="bg-blue-100 text-blue-800 px-3 py-1 text-sm rounded hover:bg-blue-600 hover:text-white transition">
          Chỉnh sửa
        </NuxtLink>
        <button class="bg-red-100 text-red-700 px-3 py-1 text-sm rounded hover:bg-red-600 hover:text-white transition">
          Xóa
        </button>
      </div>
      <h2 class="text-xl font-bold">Phòng Tiêu chuẩn A2</h2>
      <div class="text-blue-600 font-semibold">300.000đ / đêm</div>
      <div class="text-gray-600">Diện tích: 20.0 m²</div>
      <div class="text-sm text-yellow-700 bg-yellow-100 inline-block px-2 py-1 rounded">Đã thuê</div>
      <div class="relative">
        <h3 class="mt-3 font-semibold">Tiện nghi:</h3>
        <ul class="list-disc list-inside text-gray-700 text-sm">
          <li>Máy lạnh</li>
          <li>Wifi</li>
          <li v-if="showMore1">Tivi</li>
          <li v-if="showMore1">Bồn tắm</li>
          <li v-if="showMore1">Máy giặt</li>
          <li v-if="showMore1">Bếp điện</li>
        </ul>
        <div class="mt-2 flex justify-center">
          <button @click="showMore1 = !showMore1" class="mt-2 text-blue-600 text-sm hover:scale-110 transition-transform duration-300">
            {{ showMore1 ? 'Thu gọn ▲' : 'Xem thêm ▼' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Phòng 3 -->
    <div class="bg-white rounded-xl shadow p-5 space-y-3 relative group">
      <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition duration-300 flex gap-2">
          <NuxtLink to="/AddMotels" class="bg-green-100 text-green-800 px-3 py-1 text-sm rounded hover:bg-green-600 hover:text-white transition">
          Thêm
        </NuxtLink>
        <NuxtLink to="/EditMotels" class="bg-blue-100 text-blue-800 px-3 py-1 text-sm rounded hover:bg-blue-600 hover:text-white transition">
          Chỉnh sửa
        </NuxtLink>
        <button class="bg-red-100 text-red-700 px-3 py-1 text-sm rounded hover:bg-red-600 hover:text-white transition">
          Xóa
        </button>
      </div>

      <h2 class="text-xl font-bold">Phòng Mini 203</h2>
      <div class="text-blue-600 font-semibold">200.000đ / đêm</div>
      <div class="text-gray-600">Diện tích: 15.0 m²</div>
      <div class="text-sm text-red-700 bg-red-100 inline-block px-2 py-1 rounded">Đang sửa</div>
      <div class="relative">
        <h3 class="mt-3 font-semibold">Tiện nghi:</h3>
        <ul class="list-disc list-inside text-gray-700 text-sm">
          <li>Máy lạnh</li>
          <li>Wifi</li>
          <li v-if="showMore2">Tivi</li>
          <li v-if="showMore2">Bồn tắm</li>
          <li v-if="showMore2">Máy giặt</li>
          <li v-if="showMore2">Bếp điện</li>
        </ul>
        <div class="mt-2 flex justify-center">
          <button @click="showMore2 = !showMore2" class="mt-2 text-blue-600 text-sm hover:scale-110 transition-transform duration-300">
            {{ showMore2 ? 'Thu gọn ▲' : 'Xem thêm ▼' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Phòng 4 -->
    <div class="bg-white rounded-xl shadow p-5 space-y-3 relative group">
      <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition duration-300 flex gap-2">
          <NuxtLink to="/AddMotels" class="bg-green-100 text-green-800 px-3 py-1 text-sm rounded hover:bg-green-600 hover:text-white transition">
          Thêm
        </NuxtLink>
        <NuxtLink to="/EditMotels" class="bg-blue-100 text-blue-800 px-3 py-1 text-sm rounded hover:bg-blue-600 hover:text-white transition">
          Chỉnh sửa
        </NuxtLink>
        <button class="bg-red-100 text-red-700 px-3 py-1 text-sm rounded hover:bg-red-600 hover:text-white transition">
          Xóa
        </button>
      </div>

      <h2 class="text-xl font-bold">Phòng VIP 102</h2>
      <div class="text-blue-600 font-semibold">500.000đ / đêm</div>
      <div class="text-gray-600">Diện tích: 25.5 m²</div>
      <div class="text-sm text-green-700 bg-green-100 inline-block px-2 py-1 rounded">Còn trống</div>
      <div class="relative">
        <h3 class="mt-3 font-semibold">Tiện nghi:</h3>
        <ul class="list-disc list-inside text-gray-700 text-sm">
          <li>Máy lạnh</li>
          <li>Wifi</li>
          <li v-if="showMore3">Tivi</li>
          <li v-if="showMore3">Bồn tắm</li>
          <li v-if="showMore3">Máy giặt</li>
          <li v-if="showMore3">Bếp điện</li>
        </ul>
        <div class="mt-2 flex justify-center">
          <button @click="showMore3 = !showMore3" class="mt-2 text-blue-600 text-sm hover:scale-110 transition-transform duration-300">
            {{ showMore3 ? 'Thu gọn ▲' : 'Xem thêm ▼' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* Tuỳ chỉnh CSS nếu cần */
</style>
