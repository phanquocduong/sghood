<template>
    <!-- Banner -->
    <div class="main-search-container" data-background-image="/images/main-search-background-01.jpg">
        <div class="main-search-inner">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Tìm Phòng Trọ Phù Hợp Gần Bạn</h2>
                        <h4>Khám phá khu trọ chính chủ, giá tốt, cập nhật mỗi ngày tại Trọ Việt</h4>

                        <div class="main-search-input">
                            <div class="main-search-input-item">
                                <input type="text" placeholder="Nhập từ khoá..." value="" />
                            </div>

                            <div class="main-search-input-item">
                                <select data-placeholder="Tất cả khu vực" class="chosen-select">
                                    <option>Tất cả khu vực</option>
                                    <option>Thủ Đức</option>
                                    <option>Quận 1</option>
                                    <option>Quận 3</option>
                                    <option>Quận 4</option>
                                    <option>Quận 5</option>
                                    <option>Quận 6</option>
                                    <option>Quận 7</option>
                                    <option>Quận 8</option>
                                    <option>Quận 10</option>
                                    <option>Quận 11</option>
                                    <option>Quận 12</option>
                                    <option>Tân Bình</option>
                                    <option>Bình Tân</option>
                                    <option>Bình Thạnh</option>
                                    <option>Tân Phú</option>
                                    <option>Gò Vấp</option>
                                    <option>Phú Nhuận</option>
                                    <option>Bình Chánh</option>
                                    <option>Hóc Môn</option>
                                    <option>Cần Giờ</option>
                                    <option>Củ Chi</option>
                                    <option>Nhà Bè</option>
                                </select>
                            </div>

                            <div class="main-search-input-item">
                                <select data-placeholder="Tất cả mức giá" class="chosen-select">
                                    <option>Tất cả mức giá</option>
                                    <option>Dưới 1 triệu</option>
                                    <option>1 - 2 triệu</option>
                                    <option>2 - 3 triệu</option>
                                    <option>3 - 5 triệu</option>
                                    <option>Trên 5 triệu</option>
                                </select>
                            </div>
                            <ClientOnly> </ClientOnly>
                            <ClientOnly> </ClientOnly>

                            <button class="button" onclick="">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Carousel -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="headline centered margin-top-75">
                    <strong class="headline-with-separator">Khu Vực Nổi Bật</strong>
                </h3>
            </div>
        </div>
    </div>
    <div class="fullwidth-carousel-container margin-top-25">
        <div class="fullwidth-slick-carousel category-carousel">
            <!-- Item -->
            <div class="fw-carousel-item">
                <div class="category-box-container">
                    <a href="listings-half-screen-map-grid-1.html" class="category-box" data-background-image="images/category-box-01.jpg">
                        <div class="category-box-content">
                            <h3>Gò Vấp</h3>
                            <span>64 nhà trọ</span>
                        </div>
                        <span class="category-box-btn">Xem</span>
                    </a>
                </div>
            </div>

            <!-- Item -->
            <div class="fw-carousel-item">
                <div class="category-box-container">
                    <a href="listings-half-screen-map-grid-1.html" class="category-box" data-background-image="images/category-box-03.jpg">
                        <div class="category-box-content">
                            <h3>Quận 12</h3>
                            <span>67 nhà trọ</span>
                        </div>
                        <span class="category-box-btn">Xem</span>
                    </a>
                </div>
            </div>

            <!-- Item -->
            <div class="fw-carousel-item">
                <div class="category-box-container">
                    <a href="listings-half-screen-map-grid-1.html" class="category-box" data-background-image="images/category-box-04.jpg">
                        <div class="category-box-content">
                            <h3>Quận 7</h3>
                            <span>27 nhà trọ</span>
                        </div>
                        <span class="category-box-btn">Xem</span>
                    </a>
                </div>
            </div>

            <!-- Item -->
            <div class="fw-carousel-item">
                <div class="category-box-container">
                    <a href="listings-half-screen-map-list.html" class="category-box" data-background-image="images/category-box-05.jpg">
                        <div class="category-box-content">
                            <h3>Quận 4</h3>
                            <span>22 nhà trọ</span>
                        </div>
                        <span class="category-box-btn">Xem</span>
                    </a>
                </div>
            </div>

            <!-- Item -->
            <div class="fw-carousel-item">
                <div class="category-box-container">
                    <a href="listings-half-screen-map-list.html" class="category-box" data-background-image="images/category-box-06.jpg">
                        <div class="category-box-content">
                            <h3>Quận Tân Bình</h3>
                            <span>130 nhà trọ</span>
                        </div>
                        <span class="category-box-btn">Xem</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Visited Places -->
    <section class="fullwidth margin-top-65 padding-top-75 padding-bottom-70" data-background-color="#f8f8f8">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="headline centered margin-bottom-45">
                        <strong class="headline-with-separator">Nhà Trọ Nổi Bật</strong>
                    </h3>
                </div>

                <div class="col-md-12">
                    <div class="simple-slick-carousel dots-nav">
                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->

                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->

                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->

                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->

                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->

                        <!-- Listing Item -->
                        <div class="carousel-item">
                            <a href="listings-single-page.html" class="listing-item-container">
                                <div class="listing-item">
                                    <img src="public/images/listing-item-03.jpg" alt="" />
                                    <div class="listing-item-details">
                                        <ul>
                                            <li>Còn 14 phòng trống</li>
                                        </ul>
                                    </div>
                                    <div class="listing-item-content">
                                        <span class="tag">Quận 12</span>
                                        <h3>01 đường Lê Văn Khương...</h3>
                                        <span>Giá từ 3.500.000đ / tháng</span>
                                    </div>
                                    <span class="like-icon"></span>
                                </div>
                                <div class="star-rating">
                                    <div class="rating-counter">Diện tích từ 25m<sup>2</sup></div>
                                </div>
                            </a>
                        </div>
                        <!-- Listing Item / End -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Section -->
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h2 class="headline centered margin-top-80">
                    <strong class="headline-with-separator">Tìm Phòng Trọ Dễ Dàng Chỉ Với Vài Bước</strong>
                    <span class="margin-top-25"
                        >Chọn phòng phù hợp, đặt lịch xem trực tiếp và ký hợp đồng an tâm chỉ trong vài bước đơn giản.</span
                    >
                </h2>
            </div>
        </div>

        <div class="row icons-container">
            <!-- Stage -->
            <div class="col-md-4">
                <div class="icon-box-2 with-line">
                    <i class="im im-icon-Map2"></i>
                    <h3>Tìm Phòng Phù Hợp</h3>
                    <p>Sử dụng bộ lọc để tìm trọ theo khu vực, giá, diện tích, tiện ích...</p>
                </div>
            </div>

            <!-- Stage -->
            <div class="col-md-4">
                <div class="icon-box-2 with-line">
                    <i class="im im-icon-Mail-withAtSign"></i>
                    <h3>Đặt Lịch Xem Phòng</h3>
                    <p>Gửi yêu cầu hẹn giờ xem phòng với Trọ Việt ngay trên hệ thống.</p>
                </div>
            </div>

            <!-- Stage -->
            <div class="col-md-4">
                <div class="icon-box-2">
                    <i class="im im-icon-Checked-User"></i>
                    <h3>Ký Hợp Đồng & Đặt Cọc</h3>
                    <p>Sau khi xem phòng ưng ý, tiến hành đặt phòng, ký hợp đồng và đặt cọc nhanh chóng.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup></script>

<style scoped></style>
