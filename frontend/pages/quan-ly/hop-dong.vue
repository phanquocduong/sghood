<template>
      <Titlebar title="Hợp đồng" />

   <div class="container mt-4">
    <div v-if="loading">Đang tải hợp đồng...</div>
    <div v-else-if="contractContent">
      <div v-html="contractContent">

      </div>
    </div>
    <div v-else>
      <p>Không tìm thấy nội dung hợp đồng.</p>
    </div>
      <button
                            type="submit"
                            class="submit button"
                            id="submit"
                            value="Gửi tin nhắn"
                            :disabled="loading"
                            style="margin-bottom: 10px; margin-top: -10px"
                        >
                            <span v-if="loading"  class="spinner"  ></span>
                            {{ loading ? ' Đợi chút...' : 'Xác nhận' }}
                        </button>
  </div>     
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp, useRoute } from '#app';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '~/stores/auth';
import { storeToRefs } from 'pinia';
import { useHead } from '#app';
// Định nghĩa layout
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const toast = useToast();
const config = useRuntimeConfig();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const route = useRoute();
const contractContent = ref('');
const loading = ref(false)

const fetchConstract = async () =>{
    try{
        loading.value =true;
        const userId = user.value?.id;
        if(!userId) return toast.error('Không tìm thấy id người dùng.');
        const res = await $api(`users/${userId}/contract`);
        console.log("Kết quả API trả về:", res);
        contractContent.value = res[0].content;
    }catch(error){
     /*    toast.error('Không thể tải hợp đồng'); */
        console.error('loi khi fetch' ,error)
    
    }finally{
        loading.value = false
    }
}
onMounted(async () => {
 await fetchConstract();
});
const logHTML = ()=>{
    console.log("html hop dong :", contractContent.value)

}
useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
    }
  ]
});
const submitButton = (async()=>{

})

</script>

<style scoped>

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ccc;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}


@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.container {
  max-width: 800px;
  margin: auto;
  color: #000;
  font-family: Arial, sans-serif;

}
</style>
