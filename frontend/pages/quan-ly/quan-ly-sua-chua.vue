<template>
  <div>
    <Titlebar title="Yêu cầu sửa chữa" />

    <Loading :is-loading="loading" />

    <div v-if="loading" class="text-center p-5">
      <p>Đang tải yêu cầu...</p>
    </div>

    <div v-else class="row">
      <div class="col-lg-12 col-md-12">
        <div class="dashboard-list-box margin-top-0">
          <div class="box-title-bar">
            <h4>Danh sách yêu cầu sửa chữa</h4>
              <NuxtLink to="/quan-ly/Add-Repair" class="add-button">Yêu cầu sữa chữa</NuxtLink>
          </div>

          <div v-if="repairRequests.length === 0" class="box-title-bar-tb">
            <p>Chưa có yêu cầu nào.</p>
          </div>

          <div
            v-for="(req, index) in repairRequests"
            :key="req.id"
            class="repair-item"
          >
          <!-- Ảnh bên trái -->
          <div class="repair-image-wrapper" v-if="req.images?.length">
              <img
              :src="req.images[0]"
              alt="Hình ảnh sự cố"
              class="repair-image"
              @click="openImageSlider(index)"
              />
            </div>
            
            <!-- Nội dung bên phải -->
            <div class="repair-content">
                <div class="repair-header">
                    <h5>
                        <i class="fas fa-tools icon-repair"></i>
                        {{ req.title }}
                        
                    </h5>
                    
                    <button class="delete-btn" @click="removeRequest(index)">Hủy</button>
                <span
                  class="status-tag"
                  :class="{
                    pending: req.status === 'Chờ xác nhận',
                    inprogress: req.status === 'Đang thực hiện',
                    done: req.status === 'Hoàn thành',
                    canceled: req.status === 'Đã hủy'
                  }"
                >
                  {{ req.status }}
                </span>
                
              </div>

              <p class="description">{{ req.description }}</p>

              <!-- Lý do hủy -->
              <div
                v-if="req.status === 'Đã hủy' && req.cancellation_reason"
                class="cancel-box"
              >
                <strong>Lý do hủy:</strong> {{ req.cancellation_reason }}
              </div>
            </div>

           
          </div>
        </div>
      </div>
    </div>

   <!-- Modal xem ảnh -->
<div v-if="showSlider" class="modal-overlay" @click.self="closeSlider">
  <div class="modal-content">
    <Transition :name="transitionName" mode="out-in">
      <div class="slider-wrapper" :key="selectedImages[currentIndex]">
        <img :src="selectedImages[currentIndex]" class="slider-image" />
      </div>
    </Transition>

    <!-- Nút điều hướng dạng overlay nằm ngoài khung -->
    <button
      class="mfp-arrow mfp-arrow-left mfp-prevent-close"
      title="Previous"
      @click.stop="prevImage"
    ></button>
    <button
      class="mfp-arrow mfp-arrow-right mfp-prevent-close"
      title="Next"
      @click.stop="nextImage"
    ></button>

    <button class="modal-close" @click="closeSlider">×</button>
  </div>
</div>


  </div>
</template>

<script setup>
definePageMeta({ layout: 'management' });
import { ref } from 'vue'
const showSlider = ref(false)
const selectedImages = ref([])
const currentIndex = ref(0)
const loading = ref(false)
const transitionName = ref("slide-left")

const openImageSlider = (reqIndex)=>{
    selectedImages.value = repairRequests.value[reqIndex].images || []
    currentIndex.value = 0
    showSlider.value = true
}  

const nextImage= ()=>{
    transitionName.value = "slide-left"
    currentIndex.value= (currentIndex.value+1 ) % selectedImages.value.length
}

const prevImage = ()=>{
      transitionName.value= "slide-left"
    currentIndex.value = (currentIndex.value - 1 + selectedImages.value.length ) % selectedImages.value.length
}

const closeSlider = ()=>{
    showSlider.value = false
}

const repairRequests = ref([
  {
    id: 1,
    title: 'Máy lạnh không hoạt động',
    description: 'Máy lạnh phòng A302 bị tắt đột ngột và không khởi động lại.',
     images: ['/images/sghood_logo1.png', '/images/popular-location-01.jpg','/images/sghood_logo1.png','/images/sghood_logo2.png','/images/sghood_logo1.png'],
    status: 'Chờ xác nhận',
    cancellation_reason: null
  },
  {
    id: 2,
    title: 'Ống nước bị vỡ',
    description: 'Ống nước trong nhà vệ sinh tầng 2 bị vỡ, gây rò rỉ lớn.',
     images: ['/images/sghood_logo1.png', '/images/maylanh2.png'],
    status: 'Đang thực hiện',
    cancellation_reason: null
  },
  {
    id: 3,
    title: 'Sơn tường bị bong tróc',
    description: 'Tường phòng B101 bong tróc sơn, ảnh hưởng đến thẩm mỹ.',
     images: ['/images/sghood_logo1.png', '/images/maylanh2.png'],
    status: 'Hoàn thành',
    cancellation_reason: null
  },
  {
    id: 4,
    title: 'Hủy yêu cầu sửa cửa',
    description: 'Cửa phòng bị kẹt nhưng đã tự xử lý.',
     images: ['/images/sghood_logo1.png', '/images/maylanh2.png'],
    status: 'Đã hủy',
    cancellation_reason: 'Người dùng tự sửa, không cần hỗ trợ nữa.'
  }
])

const removeRequest = (index) => {
  repairRequests.value.splice(index, 1)
}


</script>

<style scoped>
.repair-item {
  display: flex;
  align-items: flex-start;
  border-bottom: 1px solid #eee;
  padding: 20px;
  position: relative;
  background: #fff;
  gap: 20px;
}

.repair-image-wrapper {
  flex-shrink: 0;
}

.repair-image {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ddd;
  cursor: pointer;
}

.repair-content {
  display: block;
    flex: 1;
    
}

.repair-header {
    display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.icon-repair {
  color: #f44336;
  margin-right: 8px;
}

.description {
  margin: 8px 0;
  color: #7d7d7d;
  margin-top: -6px;
  font-size: 15px;
}

.status-tag {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-align: center;
  min-width: 100px;
}

.pending {
  background-color: #ffe082;
  color: #795548;
}

.inprogress {
  background-color: #81d4fa;
  color: #0277bd;
}

.done {
  background-color: #aed581;
  color: #33691e;
}

.canceled {
  background-color: #ef9a9a;
  color: #b71c1c;
}

.cancel-box {
  margin-top: 5px;
  padding: 10px;
  background: #ffebee;
  border-left: 4px solid #f44336;
  color: #b71c1c;
}

.repair-title {
  flex: 1;
  display: flex;
  align-items: center;
}

.right-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.delete-btn {
  background: transparent;
  border: 1px solid #f44336;
  font-size: 14px;
  color: #f44336;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
      margin-left: auto;
}

.delete-btn:hover {
  background: #f44336;
  color: white;
}

.box-title-bar-tb {
  font-size: larger;
  padding: 10px;
  text-align: center;
}

/* Modal xem ảnh */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.modal-content {
  position: relative;
  background: #fff;
  padding: 10px;
  border-radius: 8px;
  max-width: 80%;
}

.slider-image {
  max-width: 100%;
  height: auto;
  border-radius: 6px;
}

.slider-controls {
  display: flex;
  justify-content: space-between;
  background-color: #e8e7e7;
 
}

.slider-controls button {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 6px;
  width: 40px;
  height: 40px;
  font-size: 20px;
  
  border: none;
  border-radius: 4px;
  cursor: pointer;
}


.modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  background: rgb(220, 220, 220);
  color: white;
  border: none;
  font-size: 24px;
  width: 40px;
  height: 40px;
  cursor: pointer;
  border-radius: 50%;
  transition: color 0.3s ease, transform 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close:hover {
  color: rgb(213, 13, 13);
  transform: scale(1.2);
}
h5{
    font-size: 18px;
    transform:translate(-8px) ;
    font-weight: bold;
   
}
.slide-left-enter-active,
.slide-right-enter-active {
  transition: all 0.4s ease;
  width: 100%;
}

.slide-left-leave-active,
.slide-right-leave-active {
  transition: all 0.4s ease;
   
  width: 100%;
}

.slide-left-enter-from {
  transform: translateX(100%);
  opacity: 0;
}
.slide-left-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.slide-right-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}
.slide-right-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
.slider-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 400px; /* Hoặc max-height phù hợp */
  overflow: hidden;
  position: relative;
}
/* Nút trái & phải overlay */
.mfp-arrow {
  position: absolute;
  top: 50%;
  width: 60px;
  height: 60px;
  margin-top: -30px;
  cursor: pointer;
  background: rgba(0, 0, 0, 0.5);
  border: none;
  color: white;
  font-size: 32px;
  line-height: 60px;
  text-align: center;
  z-index: 1000;
  border-radius: 50%;
  transition: background 0.3s;
}

.mfp-arrow:hover {
  background: rgba(0, 0, 0, 0.8);
}

.mfp-arrow-left {
  left: -100px; /* nằm ngoài khung modal-content */
}

.mfp-arrow-right {
  right: -100px;
}

/* Điều chỉnh nếu muốn nằm sát hình ảnh thay vì ngoài khung */
.modal-content {
  position: relative;
  padding: 20px;
  max-width: 80%;
  overflow: visible; /* Cho mũi tên vượt ra ngoài */
}


.add-button {
  position: relative;
  float: right;
  background-color: transparent;
  color: white;
  border: 2px solid #d32f2f;
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 16px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s ease;
  background-color: #ffffff; /* Nền đỏ */
  margin-top: -60px;
margin-right: 18px;
  background-color: #d32f2f;
  height: 35px;
  top: 5px;
  text-align: center;
  display: flex;
align-items: center;
justify-content: center;
}

.add-button:hover {
  background-color: white;
  color: #d32f2f;
  border-color: #d32f2f;
}

</style>
