<template>
    <ListingSlider
        :images="[
            { src: '/images/single-listing-01.jpg' },
            { src: '/images/single-listing-02.jpg' },
            { src: '/images/single-listing-03.jpg' },
            { src: '/images/single-listing-04.jpg' }
        ]"
    />

    <!-- Content -->
    <div class="container">
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <ListingTitlebar title="Phòng 310" address="Nhà trọ phố Hiến" tag="Đang trống" />

                <div class="listing-section">
                    <p>
                        Giá từ 2,4 triệu, diện tích từ 34m², trang bị 8/14 tiện ích. Khu trọ nằm ngay trên trục đường Lê Văn Khương gần Mega
                        Market Q12, các tuyến đường lớn: QL1A, Lê Đức Thọ. Với thiết kế hiện đại đây sẽ là một không gian sống lý tưởng cho
                        các bạn tìm kiếm trọ chất lượng tại Q12.
                    </p>
                    <p>Diện tích: 25m<sup>2</sup></p>

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
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <ListingPricing :title="'Giá thuê'" :fees="[{ name: 'Phòng 310', price: '3.500.000đ/tháng' }]" />
                <BookingWidget />
            </div>
        </div>
        <!-- Food Menu -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Các phòng còn trống khác</h3>

            <div class="row">
                <div v-for="item in listings" :key="item.id" class="col-lg-4 col-md-6">
                    <ListingItemChild :item="item" />
                </div>
            </div>
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
