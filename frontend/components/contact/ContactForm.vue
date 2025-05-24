<template>
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
</template>

<script setup></script>

<style scoped></style>
