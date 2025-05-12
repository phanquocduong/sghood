<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded-2xl shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4 text-center">Đăng ký tài khoản</h2>
      <input
        v-model="name"
        type="text"
        placeholder="Họ và tên"
        class="w-full mb-3 p-3 border rounded-lg"
      />
      <input
        v-model="phone"
        type="text"
        placeholder="Số điện thoại"
        class="w-full mb-3 p-3 border rounded-lg"
      />
       <input
        v-model="email"
        type="text"
        placeholder="Email"
        class="w-full mb-3 p-3 border rounded-lg"
      />
        <input
        v-model="password"
        type="text"
        placeholder="Mật khẩu"
        class="w-full mb-3 p-3 border rounded-lg"
      />
      <button
        @click="handleRegister"
        class="w-full bg-green-600 text-white py-2 rounded-xl hover:bg-green-700 transition"
      >
        Đăng ký
      </button>
      <div class="mt-4 text-sm text-center text-blue-600">
        <RouterLink to="/admin/login">Đã có tài khoản? Đăng nhập</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "layoutLogin" 
});
const name = ref('')
const phone = ref('')
const router = useRouter()

const handleRegister = () => {
  if (!name.value || !phone.value) {
    alert('Vui lòng nhập đầy đủ họ tên và số điện thoại.')
    return
  }

  // Giả lập đăng ký thành công
  alert('Đăng ký thành công! Vui lòng xác minh OTP.')
  router.push('/admin/login')
}
</script>
