<template>
    <div class="flex h-screen ">
      <!-- Sidebar -->
      <Nav class="w-64 border-r" />
  
      <div class="flex flex-col flex-1">
        <!-- Header -->
        <NavHeaders class="h-30 w-10/12 border-b" />
  
        <!-- Nội dung trang -->
        <main class="flex-1 p-6 overflow-y-auto bg-gray-100">
          <NuxtPage />
        </main>
      </div>
    </div>
  </template>
  
  
  
  <script setup>
  
  </script>