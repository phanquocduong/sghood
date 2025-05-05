<template>
    <NuxtLayout>
        <NuxtPage></NuxtPage>
    </NuxtLayout>
</template>

<script setup>
useHead({
    script: [
        { src: '/scripts/jquery-3.6.0.min.js', defer: true },
        { src: '/scripts/jquery-migrate-3.3.2.min.js', defer: true },
        { src: '/scripts/mmenu.min.js', defer: true },
        { src: '/scripts/chosen.min.js', defer: true },
        { src: '/scripts/slick.min.js', defer: true },
        { src: '/scripts/rangeslider.min.js', defer: true },
        { src: '/scripts/magnific-popup.min.js', defer: true },
        { src: '/scripts/waypoints.min.js', defer: true },
        { src: '/scripts/counterup.min.js', defer: true },
        { src: '/scripts/jquery-ui.min.js', defer: true },
        { src: '/scripts/tooltips.min.js', defer: true },
        { src: '/scripts/custom.js', defer: true },
        { src: '/scripts/switcher.js', defer: true }
    ]
});
</script>
