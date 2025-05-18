<template>
    <!-- Content -->
    <div class="container">
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <ListingTitlebar title="Phòng 310" address="01 đường Lê Văn Khương, phường Thới An, quận 12" />

                <ListingPricing :title="'Giá thuê'" :fees="[{ name: 'Phòng 310', price: '3.500.000đ/tháng' }]" />

                <ListingAmenities :amenities="['Gác lửng', 'Ban công']" />

                <ListingSliderSmall
                    :images="[
                        { src: '/images/single-listing-02a.jpg' },
                        { src: '/images/single-listing-01a.jpg' },
                        { src: '/images/single-listing-03a.jpg' },
                        { src: '/images/single-listing-04a.jpg' }
                    ]"
                />
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <BookingWidget />
            </div>
        </div>
        <!-- Food Menu -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Các phòng còn trống khác</h3>

            <div class="show-more">
                <div class="row">
                    <div v-for="item in listings" :key="item.id" class="col-lg-4 col-md-6">
                        <ListingItemChild :item="item" />
                    </div>
                </div>
            </div>
            <a href="#" class="show-more-button" data-more-title="Hiển thị thêm" data-less-title="Hiển thị ít hơn"
                ><i class="fa fa-angle-down"></i
            ></a>
        </div>
        <!-- Food Menu / End -->
    </div>
</template>

<script setup>
import { ref } from 'vue';
const listings = ref([
    {
        id: 1,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    },
    {
        id: 2,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    },
    {
        id: 3,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    },
    {
        id: 4,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    },
    {
        id: 5,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    },
    {
        id: 6,
        image: '/images/listing-item-03.jpg',
        status: 'Đang trống',
        name: 'Phòng 310',
        price: '3.500.000đ',
        area: 25,
        amenities: ['Ban công', 'Gác lửng']
    }
]);
</script>
