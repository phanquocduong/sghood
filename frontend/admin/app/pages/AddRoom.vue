<script setup lang="ts">
definePageMeta({
  layout: "default", // hoặc tên layout của bạn
});
</script>

<template>
      <div class="container mx-auto p-6 space-y-8">
  <!-- Thông tin cơ bản -->
  <section id="basic-info" class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2" fill="currentColor"><use xlink:href="#icon-doc"></use></svg>
      Thông tin cơ bản
    </h3>
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Tiêu đề danh sách <span class="text-gray-400" title="Tên doanh nghiệp của bạn">(tip)</span></label>
        <input type="text" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Danh mục</label>
          <select class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200">
            <option>Chọn danh mục</option>
            <option>Ăn uống</option>
            <option>Cửa hàng</option>
            <option>Khách sạn</option>
            <option>Nhà hàng</option>
            <option>Thể thao</option>
            <option>Sự kiện</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Từ khóa <span class="text-gray-400" title="Tối đa 15 từ khóa liên quan">(tip)</span></label>
          <input type="text" placeholder="Từ khóa, cách nhau bằng dấu phẩy" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
        </div>
      </div>
    </div>
  </section>

  <!-- Vị trí -->
  <section id="location" class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2" fill="currentColor"><use xlink:href="#icon-location"></use></svg>
      Vị trí
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Thành phố</label>
        <select class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200">
          <option>Chọn thành phố</option>
          <option>Hà Nội</option>
          <option>TP. Hồ Chí Minh</option>
          <option>Đà Nẵng</option>
          <option>Hải Phòng</option>
          <option>Cần Thơ</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Địa chỉ</label>
        <input type="text" placeholder="Ví dụ: 123 Đường ABC" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Tỉnh/Quận</label>
        <input type="text" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Mã ZIP</label>
        <input type="text" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
    </div>
  </section>

  <!-- Thư viện ảnh -->
  <section id="gallery" class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2" fill="currentColor"><use xlink:href="#icon-picture"></use></svg>
      Thư viện ảnh
    </h3>
    <div class="border-2 border-dashed border-green-400 p-6 rounded relative">
      <div class="flex space-x-4 overflow-x-auto">
        <!-- Thumbnail sample -->
        <div class="w-24 h-24 bg-gray-100 rounded overflow-hidden relative">
          <img src="" alt="" class="w-full h-full object-cover" />
          <button class="absolute top-1 right-1 bg-white rounded-full p-1 shadow"><svg class="w-4 h-4"><use xlink:href="#icon-close"></use></svg></button>
        </div>
        <!-- Thêm thumbnail khác tương tự -->
      </div>
      <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer" />
      <p class="mt-4 text-center text-gray-500">Kéo thả hoặc chọn ảnh để tải lên</p>
    </div>
  </section>

  <!-- Chi tiết & Liên hệ -->
  <section id="details" class="bg-white p-6 rounded shadow space-y-4">
    <h3 class="text-xl font-semibold mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2" fill="currentColor"><use xlink:href="#icon-docs"></use></svg>
      Chi tiết
    </h3>
    <div>
      <label class="block text-sm font-medium mb-1">Mô tả</label>
      <textarea rows="4" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200"></textarea>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Điện thoại <span class="text-gray-400">(không bắt buộc)</span></label>
        <input type="text" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Website <span class="text-gray-400">(không bắt buộc)</span></label>
        <input type="text" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">E-mail <span class="text-gray-400">(không bắt buộc)</span></label>
        <input type="email" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Facebook</label>
        <input type="url" placeholder="https://www.facebook.com" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Twitter</label>
        <input type="url" placeholder="https://www.twitter.com" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Google+</label>
        <input type="url" placeholder="https://plus.google.com" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Instagram</label>
        <input type="url" placeholder="https://www.instagram.com" class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring focus:ring-indigo-200" />
      </div>
    </div>
  </section>

  <!-- Tiện ích -->
  <section id="amenities" class="bg-white p-6 rounded shadow">
    <h3 class="text-xl font-semibold mb-4">Tiện ích</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Thang máy</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Không gian thân thiện</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Đặt ngay</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">WiFi miễn phí</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Đỗ xe riêng</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Đỗ xe ven đường</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Cho phép hút thuốc</span></label>
      <label class="inline-flex items-center"><input type="checkbox" class="form-checkbox" /><span class="ml-2">Sự kiện</span></label>
    </div>
  </section>

  <!-- Giá cả -->
  <section id="pricing" class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor"><use xlink:href="#icon-book-open"></use></svg>
        Giá cả
      </h3>
      <label class="flex items-center space-x-2"><span>Tắt/Bật</span><input type="checkbox" class="form-switch" checked /></label>
    </div>
    <div class="space-y-4">
      <div class="space-y-2">
        <div class="flex items-center space-x-2">
          <input type="text" placeholder="Tiêu đề" class="flex-1 border border-gray-300 rounded p-2" />
          <input type="text" placeholder="Mô tả" class="flex-1 border border-gray-300 rounded p-2" />
          <input type="text" placeholder="Giá (VNĐ)" class="w-32 border border-gray-300 rounded p-2" />
          <button class="text-red-500"><svg class="w-5 h-5"><use xlink:href="#icon-remove"></use></svg></button>
        </div>
        <button class="text-indigo-600">+ Thêm mục giá</button>
      </div>
    </div>
  </section>

  <div class="text-right">
    <button class="px-6 py-2 bg-green-600 text-white font-medium rounded hover:bg-green-700">Xem trước</button>
  </div>
</div>
</template>


