<template>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <Sidebar class="w-64 border-r" />

        <div class="flex flex-col flex-1">
            <!-- Nội dung trang -->
            <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
                <Loading />
                <NuxtPage />
            </main>
        </div>
    </div>
</template>

<script setup></script>
