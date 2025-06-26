<template>
    <div class="right-side">
        <div class="header-widget">
            <!-- Nếu chưa đăng nhập -->
            <a v-if="!user" href="#sign-in-dialog" class="sign-in popup-with-zoom-anim" style="margin-left: auto">
                <i class="sl sl-icon-login"></i> Đăng ký/Đăng nhập
            </a>
            <!-- Nếu đã đăng nhập -->
           <ClientOnly>
            <div v-show="user" class="auth-container">
              
              <div class="user-menu notification-wrapper">
                <div class="notification-icon" @click="toggleDropdown">
                  <i class="fa fa-bell-o"></i>
                  <span class="badge" v-if="unreadCount > 0">{{ unreadCount }}</span>
                </div>

                <ul v-if="showDropdown" class="dropdown">
                  <li v-for="(noti, index) in topNoti" :key="noti.id">
                    <a href="#">
                      <strong>{{ noti.title }}</strong><br />
                      <small style="color: #888;">{{ noti.time }}</small>
                    </a>
                  </li>
                  <li>
                    <NuxtLink to="quan-ly/thong-bao"><i class="fa fa-eye"></i> Xem tất cả</NuxtLink>
                  </li>
                </ul>
              </div>


              <!-- 👤 Menu người dùng -->
              <div class="user-menu">
                <div class="user-name">
                  <span>
                    <img
                      :src="user?.avatar ? config.public.baseUrl + user.avatar : '/images/dashboard-avatar.jpg'"
                      alt="Avatar"
                    />
                  </span>
                  Xin chào, {{ user?.name || 'Người dùng' }}!
                </div>

                <ul>
                  <li>
                    <NuxtLink to="/thong-bao"> <i class="fa fa-bell-o"></i> Thông báo </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink to="/quan-ly/ho-so-ca-nhan"> <i class="sl sl-icon-user"></i> Hồ sơ cá nhân </NuxtLink>
                  </li>
                  <li>
                    <NuxtLink to="/quan-ly/lich-xem-phong-va-dat-phong">
                      <i class="fa fa-calendar-check-o"></i> Đặt phòng
                    </NuxtLink>
                  </li>
                  <li>
                    <a href="#" @click.prevent="authStore.logout"> <i class="sl sl-icon-power"></i> Đăng xuất </a>
                  </li>
                </ul>
              </div>
            </div>
          </ClientOnly>

        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth';
import { storeToRefs } from 'pinia';
import { useNotificationStore } from '~/stores/notication';
import { onMounted } from 'vue';
const config = useRuntimeConfig();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
onMounted(()=>{
    notificationStore.fetchNotifications();
})
// Dropdown control
const showDropdown = ref(false);
const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};
const notificationStore = useNotificationStore();
const notifications = computed(() => notificationStore.notifications);
const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);

const topNoti = computed(()=>{
    return [...notifications.value].sort((a,b) => new Date(b.time).getTime() - new Date(a.time).getTime()).slice(0,5)

})
</script>

<style scoped>
.header-widget {
    display: flex;
}

.auth-container {
    margin-left: auto;
    display: flex;
    align-items: center;
    margin-top: -23px;
}

/* Icon thông báo */
.notification-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    font-size: 18px;
    color: #4b4a4a;
    border-radius: 50%;
    background-color: #f1f1f1;
    transition: all 0.2s;
    cursor: pointer;
    margin-left: -50px;
}

.notification-icon:hover {
    color: #f91942;
    background-color: #eaeaea;
}

.notification-icon .badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background-color: #f91942;
    color: white;
    font-size: 10px;
    padding: 1px 4px;
    border-radius: 50%;
    font-weight: bold;
    min-width: 16px;
    height: 16px;
    line-height: 1;
    align-items: center;
    display: flex;
    justify-content: center;
}

/* Dùng class dropdown của template */
.user-menu .dropdown {
    position: absolute;
    right: -100px; /* dịch sang phải icon chuông */
    top: 48px; /* ngay phía dưới icon chuông */
    background: white;
    width: 220px;
    border-radius: 4px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

.user-menu .dropdown li {
    border-bottom: 1px solid #eee;
}

.user-menu .dropdown li:last-child {
    border-bottom: none;
}

.user-menu .dropdown li a {
    display: block;
    padding: 10px 20px;
    font-size: 14px;
    color: #333;
    transition: all 0.2s;
}

.user-menu .dropdown li a:hover {
    background-color: #f7f7f7;
    color: #f91942;
}
</style>
