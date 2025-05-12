<script setup>
definePageMeta({
  layout: "layoutLogin" // hoặc tên layout của bạn
});

const phone = ref('')
const otp = ref('')
const router = useRouter()

const handleLogin = () => {
  if (!phone.value || !otp.value) {
    alert('Vui lòng nhập số điện thoại và mã OTP.')
    return
  }
  /* router.push('') */
}
</script>
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded-2xl shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4 text-center">Đăng nhập Admin</h2>
      <input
        v-model="phone"
        type="text"
        placeholder="Số điện thoại"
        class="w-full mb-3 p-3 border rounded-lg"
      />
      <input
        v-model="otp"
        type="text"
        placeholder="Mã OTP"
        class="w-full mb-3 p-3 border rounded-lg"
      />
      <button
        @click="handleLogin"
        class="w-full bg-blue-600 text-white py-2 rounded-xl hover:bg-blue-700 transition"
      >
        Xác minh OTP
      </button>

      <div class="flex justify-between mt-4 text-sm text-blue-600">
        <NuxtLink to="">Quên mật khẩu?</NuxtLink>
      </div>
    </div>
  </div>
</template>


