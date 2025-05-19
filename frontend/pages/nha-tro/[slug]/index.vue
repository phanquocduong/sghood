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
                <ListingTitlebar title="01 đường Lê Văn Khương, phường Thới An" address="Quận 12" price="3.500.000đ" availableRooms="14" />

                <!-- Listing Nav -->
                <div class="listing-nav-container">
                    <ul class="listing-nav">
                        <li><a href="#listing-overview" class="active">Tổng quan</a></li>
                        <li><a href="#listing-location">Vị trí</a></li>
                    </ul>
                </div>

                <!-- Overview -->
                <div id="listing-overview" class="listing-section">
                    <!-- Description -->
                    <p>
                        Giá từ 2,4 triệu, diện tích từ 34m², trang bị 8/14 tiện ích. Khu trọ nằm ngay trên trục đường Lê Văn Khương gần Mega
                        Market Q12, các tuyến đường lớn: QL1A, Lê Đức Thọ. Với thiết kế hiện đại đây sẽ là một không gian sống lý tưởng cho
                        các bạn tìm kiếm trọ chất lượng tại Q12.
                    </p>
                    <div class="clearfix"></div>

                    <ListingAmenities
                        :amenities="[
                            'Khoá cổng vân tay',
                            'Camera an ninh',
                            'Wifi tốc độ cao',
                            'Chỗ phơi quần áo',
                            'Vệ sinh hằng ngày',
                            'Chỗ đậu xe máy'
                        ]"
                    />
                </div>

                <ListingMap
                    map-url="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3545.5226208148133!2d106.64193467597549!3d10.892922029375793!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174d6172c7d1dc9%3A0xfbe5f76c85c8e807!2zMSBMw6ogVsSDbiBLaMawxqFuZywgVGjhu5tpIEFuLCBRdeG6rW4gMTIsIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e1!3m2!1svi!2s!4v1746094291379!5m2!1svi!2s"
                />
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <ListingShare :like-count="159" @toggle-like="handleToggleLike" />
                <ListingPricing
                    :title="'Phí hàng tháng'"
                    :fees="[
                        { name: 'Tiền điện', price: '3.000đ / số' },
                        { name: 'Tiền nước', price: '100.000đ / tháng' },
                        { name: 'Tiền giữ xe', price: '80.000đ / tháng' },
                        { name: 'Tiền rác', price: '70.000đ / tháng' },
                        { name: 'Tiền internet', price: '70.000đ / tháng' },
                        { name: 'Phí dịch vụ', price: '50.000đ / tháng' }
                    ]"
                />
            </div>
        </div>
        <!-- Food Menu -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Danh sách phòng trống</h3>

            <div class="show-more">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <NuxtLink to="/nha-tro/nha-tro-1/5" class="listing-item-container">
                            <div class="listing-item">
                                <img src="/images/listing-item-03.jpg" alt="" />
                                <div class="listing-item-details">
                                    <ul>
                                        <li>Ban công</li>
                                        <li>Gác lửng</li>
                                    </ul>
                                </div>
                                <div class="listing-item-content">
                                    <span class="tag">Đang trống</span>
                                    <h3>Phòng 310</h3>
                                    <span>Giá: 2.600.000đ / tháng</span>
                                </div>
                            </div>
                            <div class="star-rating">
                                <div class="rating-counter">Diện tích: 16m<sup>2</sup></div>
                            </div>
                        </NuxtLink>
                    </div>
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
    }
]);
</script>
