<template>
  <div class="page-wrapper">
    <!-- VỀ CHÚNG TÔI -->
    <section class="section bg-gray">
      <div class="section-inner">
        <h2 class="section-title title-large">VỀ CHÚNG TÔI</h2>
        <div class="flex-row">
          <div class="text-block">
            <h3 class="sub-title">CHÚNG TÔI LÀ AI</h3>
            <div class="text-box">
              <p>
               {{configs.chung_toi_la_ai}}
              </p>
            </div>
          </div>
          <img src="/images/Questions-pana.png" alt="About" class="image" />
        </div>
      </div>
    </section>

    <!-- SỨ MỆNH & TẦM NHÌN -->
    <section class="section bg-white">
      <div class="section-inner">
        <h2 class="section-title">SỨ MỆNH & TẦM NHÌN</h2>
        <div class="text-box">
          <p>
           {{ configs.su_menh_tam_nhin_1 }}
          </p>
          <p>
          {{ configs.su_menh_tam_nhin_2 }}
          </p>
        </div>
      </div>
    </section>

    <!-- CHÚNG TÔI LÀM GÌ -->
    <section class="section bg-gray">
      <div class="section-inner">
        <h2 class="section-title">CHÚNG TÔI LÀM GÌ</h2>
        <div class="flex-row">
          <div class="text-block">
            <div class="text-box" >
              <p v-html="configs.chung_toi_lam_gi"></p>
             
            </div>
          </div>
          <img src="/images/Man thinking-pana.png" alt="About" class="image" />
        </div>
      </div>
    </section>

    <!-- ĐỘI NGŨ PHÁT TRIỂN -->
    <section class="section bg-white">
      <div class="section-inner">
        <h2 class="section-title">ĐỘI NGŨ PHÁT TRIỂN</h2>
        <div class="text-box">
          <p>
         {{configs.doi_ngu_phat_trien}}
          </p>
        </div>
      </div>
    </section>
  </div>
</template>
<script setup>
const configs = useState('configs')

</script>
<style scoped>
/* Layout chung */
.section {
  padding: 40px 0;
}

.bg-gray {
  background-color: #f8f8f8;
}

.bg-white {
  background-color: #ffffff;
}

.section-inner {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 16px;
}

.section-title {
  font-size: 32px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 24px;
  color: #f91942;
}

.title-large {
  font-size: 40px;
}

.sub-title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 16px;
  color: #333;
}

/* Flex layout */
.flex-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  flex-wrap: wrap;
}

.text-block {
  flex: 1 1 300px;
  max-width: 100%;
}

.text-box {
  max-width: 700px;
  margin: 0 auto;
}

.text-box p {
  margin-bottom: 12px;
  line-height: 1.6;
  color: #333;
  text-align: left;
}

.image {
  width: 300px;
  height: auto;
  object-fit: contain;
  border-radius: 8px;
  flex-shrink: 0;
}

/* Responsive Tablet */
@media screen and (max-width: 1024px) {
  .section-title {
    font-size: 28px;
  }
  .sub-title {
    font-size: 20px;
  }
  .image {
    width: 250px;
  }
}

@media screen and (max-width: 640px) {
  .section {
    padding: 24px 0; /* giảm chiều cao section */
  }

  .flex-row {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .image {
    order: -1; /* đẩy hình lên trên */
    width: 50%;
    max-width: 280px;
    margin-bottom: 16px;
  }

  .text-box p {
    text-align: center;
  }

  .section-title {
    font-size: 24px;
  }

  .sub-title {
    font-size: 18px;
  }

  .text-box {
    padding: 0 10px;
  }
}

</style>
