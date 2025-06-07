<template>
    <div class="page-wrapper">
        <!-- VỀ CHÚNG TÔI -->
        <section class="section bg-gray">
            <div class="section-inner">
                <h2 class="section-title title-large">VỀ CHÚNG TÔI</h2>
                <div class="flex-row">
                    <div class="text-block">
                        <h3 class="sub-title">CHÚNG TÔI LÀ AI</h3>
                        <p>
                            SGHood là nền tảng quản lý thuê trọ thông minh, được phát triển nhằm kết nối người thuê và chủ trọ tại TP. Hồ
                            Chí Minh. Chúng tôi mang đến giải pháp toàn diện, giúp tiết kiệm thời gian, chi phí và công sức trong việc tìm
                            kiếm và quản lý phòng trọ.
                        </p>
                    </div>
                    <img src="public/images/Questions-pana.png" alt="About" class="image" />
                </div>
            </div>
        </section>

        <!-- SỨ MỆNH & TẦM NHÌN -->
        <section class="section bg-white">
            <div class="section-inner">
                <h2 class="section-title">SỨ MỆNH & TẦM NHÌN</h2>
                <div class="text-box">
                    <p>
                        SGHood hướng tới việc số hóa thị trường cho thuê trọ, giúp người dùng dễ dàng tìm được chỗ ở phù hợp, minh bạch và
                        an toàn. Đồng thời, chúng tôi hỗ trợ chủ trọ quản lý thông tin phòng, người thuê, thanh toán và vận hành trơn tru
                        qua nền tảng trực tuyến.
                    </p>
                    <p>
                        Trong tương lai, SGHood đặt mục tiêu trở thành hệ sinh thái cho thuê nhà trọ lớn nhất tại Việt Nam, là cầu nối uy
                        tín giữa hàng triệu người thuê trọ và các đơn vị quản lý nhà trọ chuyên nghiệp.
                    </p>
                </div>
            </div>
        </section>

        <!-- CHÚNG TÔI LÀM GÌ -->
        <section class="section bg-gray">
            <div class="section-inner">
                <h2 class="section-title">CHÚNG TÔI LÀM GÌ</h2>
                <div class="text-box">
                    <p>✔ Tìm kiếm & lọc phòng theo khu vực, giá cả, tiện ích và đánh giá.</p>
                    <p>✔ Đặt lịch xem phòng & thanh toán trực tuyến một cách nhanh chóng, an toàn.</p>
                    <p>✔ Kết nối trực tiếp với chủ trọ thông qua tin nhắn hoặc cuộc gọi.</p>
                    <p>✔ Cung cấp thông tin rõ ràng, hình ảnh thực tế, hợp đồng minh bạch.</p>
                </div>
            </div>
        </section>

        <!-- ĐỘI NGŨ PHÁT TRIỂN -->
        <section class="section bg-white">
            <div class="section-inner">
                <h2 class="section-title">ĐỘI NGŨ PHÁT TRIỂN</h2>
                <div class="text-box">
                    <p>
                        SGHood được xây dựng bởi những người từng là sinh viên và người đi thuê trọ. Chúng tôi thấu hiểu nỗi lo, sự phiền
                        phức và bất tiện trong việc tìm chỗ ở phù hợp. Với niềm tin vào công nghệ, đội ngũ phát triển mong muốn thay đổi thị
                        trường thuê trọ theo hướng hiện đại, minh bạch và thuận tiện cho tất cả mọi người.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Tổng thể */
.page-wrapper {
    padding: 40px 16px;
}

/* Section & nền */
.section {
    padding: 40px 0;
}

.bg-gray {
    background-color: rgb(248, 248, 248);
}

.bg-white {
    background-color: rgb(255, 255, 255);
}

/* Giới hạn chiều rộng */
.section-inner {
    max-width: 800px;
    margin: 0 auto;
}

/* Tiêu đề */
.section-title {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 24px;
    text-align: center;
    color: #f91942;
}

.title-large {
    font-size: 40px;
}

/* Tiêu đề nhỏ */
.sub-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 16px;
    color: #333;
}

/* Nội dung + ảnh */
.flex-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.text-block {
    flex: 1 1 300px;
}

.image {
    width: 300px;
    height: auto; /* tự động theo nội dung */
    max-height: 100%; /* không vượt quá chiều cao dòng */
    object-fit: contain;
    border-radius: 8px;
    flex-shrink: 0;
}

/* Văn bản mô tả */
.text-box p {
    margin-bottom: 12px;
    line-height: 1.6;
    color: #333;
}
</style>
