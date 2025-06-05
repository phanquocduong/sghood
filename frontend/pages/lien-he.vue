<template>
    <!-- Map Container -->
    <div class="contact-map margin-bottom-60">
        <!-- Google Maps -->
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3545.98648316269!2d106.62092318328997!3d10.853900716383444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752b6c59ba4c97%3A0x535e784068f1558b!2zVHLGsOG7nW5nIENhbyDEkeG6s25nIEZQVCBQb2x5dGVjaG5pYw!5e1!3m2!1svi!2s!4v1746097904850!5m2!1svi!2s"
            width="100%"
            height="450"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>

        <!-- Office -->
        <div class="address-box-container">
            <div class="address-container" data-background-image="/images/our-office.jpg">
                <div class="office-address">
                    <h3>Văn phòng chúng tôi</h3>
                    <ul>
                        <li>141 - 143, Trung Mỹ Tây, Quận 12</li>
                        <li>TP.HCM</li>
                        <li>Điện thoại (123) 123-456</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Container / Start -->
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h4 class="headline margin-bottom-30">Tìm Chúng Tôi Tại Đây</h4>
                <div class="sidebar-textbox">
                    <p>
                        Quản lý thông minh các kênh thuê trọ tại TP.HCM. Tối ưu hóa trải nghiệm thuê trọ với giải pháp hiện đại từ Trọ Việt.
                    </p>
                    <ul class="contact-details">
                        <li><i class="im im-icon-Phone-2"></i> <strong>Phone:</strong> <span>(123) 123-456</span></li>
                        <li><i class="im im-icon-Fax"></i> <strong>Fax:</strong> <span>(123) 123-456</span></li>
                        <li>
                            <i class="im im-icon-Globe"></i> <strong>Web:</strong> <span><a href="#">www.troviet.com</a></span>
                        </li>
                        <li>
                            <i class="im im-icon-Envelope"></i> <strong>Email:</strong> <span><a href="#">troviet@gmail.com</a></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-md-8">
                <section id="contact">
                    <h4 class="headline margin-bottom-35">Liên Hệ Với Chúng Tôi</h4>
                    <div id="contact-message"></div>
                    <form method="post" action="contact.php" name="contactform" id="contactform" autocomplete="on">
                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <input name="name" type="text" id="name" placeholder="Họ và tên" required="required" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <input
                                        name="email"
                                        type="email"
                                        id="email"
                                        placeholder="Email"
                                        pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$"
                                        required="required"
                                    />
                                </div>
                            </div>
                        </div>
                        <div>
                            <input name="subject" type="text" id="subject" placeholder="Chủ đề" required="required" />
                        </div>
                        <div>
                            <textarea
                                name="comments"
                                cols="40"
                                rows="3"
                                id="comments"
                                placeholder="Lời nhắn"
                                spellcheck="true"
                                required="required"
                            ></textarea>
                        </div>
                        <input type="submit" class="submit button" id="submit" value="Gửi tin nhắn" />
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup></script>

<style scoped></style>
