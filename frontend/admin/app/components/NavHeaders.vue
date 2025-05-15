<template>
    <header class="w-full bg-white shadow-sm px-4 py-4 flex items-center justify-between">
        <!-- Bên trái: menu -->
        <nav class="flex items-center space-x-2 text-sm">
            <div class="relative group inline-block text-left">
                <!-- Nút dropdown -->
                <button
                    class="flex items-center space-x-1 text-gray-800 hover:bg-red-50 hover:text-red-500 px-4 py-2 rounded-full transition gap-1"
                >
                    <span>Trang chủ</span>
                    <svg class="w-3 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Nội dung dropdown -->
                <div
                    class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 group-hover:opacity-100 invisible group-hover:visible transform transition-all duration-200 z-50"
                >
                    <div class="py-1 space-y-1 m-auto">
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Trang chủ 1 (Hiện đại)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Trang chủ 2 (Mặc định)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-building mr-2 w-4 text-gray-400"></i> Trang chủ 3 (Airbnb)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-lightbulb mr-2 w-4 text-gray-400"></i> Trang chủ 4 (Sáng tạo)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-images mr-2 w-4 text-gray-400"></i> Trang chủ 5 (Trình chiếu)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-map-marked-alt mr-2 w-4 text-gray-400"></i> Trang chủ 6 (Bản đồ)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-video mr-2 w-4 text-gray-400"></i> Trang chủ 7 (Video)
                        </a>
                        <a
                            href="#"
                            class="flex px-4 py-2 text-sm text-gray-700 hover/sub:text-red-500 gap-1 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-th-large mr-2 w-4 text-gray-400"></i> Trang chủ 8 (Cổ điển)
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative inline-block text-left group">
                <!-- Button -->
                <button
                    class="flex items-center space-x-1 text-gray-800 hover:bg-red-50 hover:text-red-500 px-4 py-2 rounded-full transition gap-1"
                >
                    <span>Danh sách</span>
                    <svg class="w-3 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Main dropdown -->
                <div
                    class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                >
                    <div class="py-1 space-y-1">
                        <!-- Menu item with submenu -->
                        <div class="relative group/sub">
                            <a
                                href="#"
                                class="flex px-4 py-2 text-sm text-gray-700 group-hover/sub:text-red-500 gap-1 hover:translate-x-2 transition-transform duration-300 ease-in-out"
                            >
                                <i class="fas fa-home w-4 text-gray-400"></i> Trang chủ 1
                                <p class="w-4 h-4 m-auto mt-0">></p>
                            </a>
                            <div
                                class="absolute top-0 left-full ml-1 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-300 ease-out z-50 translate-y-2"
                            >
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:text-red-600">Sub menu 1</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:text-red-600">Sub menu 2</a>
                                </div>
                            </div>
                        </div>

                        <!-- Menu item with submenu -->
                        <div class="relative group/sub">
                            <a
                                href="#"
                                class="flex px-4 py-2 text-sm text-gray-700 group-hover/sub:text-red-500 gap-1 hover:translate-x-2 transition-transform duration-300 ease-in-out"
                            >
                                <i class="fas fa-home w-4 text-gray-400"></i> Trang chủ 2
                                <p class="w-4 h-4 m-auto mt-0">></p>
                            </a>
                            <div
                                class="absolute top-0 left-full ml-1 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 z-50"
                            >
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:text-red-600">Sub menu 1</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:text-red-600">Sub menu 2</a>
                                </div>
                            </div>
                        </div>

                        <!-- Items không có submenu -->
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 gap-1 hover:translate-x-2 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-building w-4 text-gray-400"></i> Trang chủ 3
                        </a>

                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 gap-1 hover:translate-x-2 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-lightbulb w-4 text-gray-400"></i> Trang chủ 4
                        </a>
                    </div>
                </div>
            </div>

            <div class="relative group inline-block text-left">
                <!-- Nút dropdown -->
                <button
                    class="flex items-center space-x-1 text-gray-800 hover:bg-red-50 hover:text-red-500 px-4 py-2 rounded-full transition gap-1"
                >
                    <span>Bảng người dùng</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Nội dung dropdown -->
                <div
                    class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 group-hover:opacity-100 invisible group-hover:visible transform transition-all duration-200 z-50"
                >
                    <div class="py-1">
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Bảng điều khiển
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Tin nhắn
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-building mr-2 w-4 text-gray-400"></i> Đặt chỗ
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-lightbulb mr-2 w-4 text-gray-400"></i> Ví tiền
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-images mr-2 w-4 text-gray-400"></i> Danh sách của tôi
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-map-marked-alt mr-2 w-4 text-gray-400"></i> Đánh giá
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-video mr-2 w-4 text-gray-400"></i> Bookmarks
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-th-large mr-2 w-4 text-gray-400"></i> Thêm danh sách
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-th-large mr-2 w-4 text-gray-400"></i> Tài khoản của tôi
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-th-large mr-2 w-4 text-gray-400"></i> Hóa đơn
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative group inline-block text-left">
                <!-- Nút dropdown -->
                <button
                    class="flex items-center space-x-1 text-gray-800 hover:bg-red-50 hover:text-red-500 px-4 py-2 rounded-full transition gap-1"
                >
                    <span>Trang khác</span>
                    <svg class="w-3 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <!-- Nội dung dropdown -->
                <div
                    class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 group-hover:opacity-100 invisible group-hover:visible transform transition-all duration-200 z-50"
                >
                    <div class="py-1 w-3/4">
                        <div>
                            <a
                                href="#"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                            >
                                <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Trang chủ 1 (Hiện đại)
                            </a>
                            <a
                                href="#"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                            >
                                <i class="fas fa-home mr-2 w-4 text-gray-400"></i> Trang chủ 2 (Mặc định)
                            </a>
                            <a
                                href="#"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                            >
                                <i class="fas fa-building mr-2 w-4 text-gray-400"></i> Trang chủ 3 (Airbnb)
                            </a>
                        </div>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-lightbulb mr-2 w-4 text-gray-400"></i> Trang chủ 4 (Sáng tạo)
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-images mr-2 w-4 text-gray-400"></i> Trang chủ 5 (Trình chiếu)
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-map-marked-alt mr-2 w-4 text-gray-400"></i> Trang chủ 6 (Bản đồ)
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-video mr-2 w-4 text-gray-400"></i> Trang chủ 7 (Video)
                        </a>
                        <a
                            href="#"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:text-red-500 hover:scale-110 transition-transform duration-300 ease-in-out"
                        >
                            <i class="fas fa-th-large mr-2 w-4 text-gray-400"></i> Trang chủ 8 (Cổ điển)
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Bên phải: avatar + nút thêm -->
        <div class="flex items-center space-x-2 ml-490px relative group text-left">
            <!-- Tài khoản -->
            <div class="flex items-center space-x-2 cursor-pointer" style="margin-left: 273px">
                <div class="relative">
                    <img src="https://via.placeholder.com/40" class="w-10 h-10 rounded-full shadow-md" alt="Avatar" />
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                </div>
                <span class="hover:text-red-500 flex items-center space-x-1 gap-2"
                    >Tài khoản của tôi
                    <svg class="w-3 h-3 text-current" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg
                ></span>
            </div>

            <!-- Nút thêm -->
        </div>
    </header>
</template>

<script setup lang="ts"></script>
