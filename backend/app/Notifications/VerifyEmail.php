<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Lớp thông báo tùy chỉnh để gửi email xác minh địa chỉ email của người dùng.
 */
class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Tạo và trả về email thông báo xác minh.
     *
     * @param mixed $notifiable Đối tượng người dùng nhận thông báo
     * @return MailMessage Đối tượng email được cấu hình
     */
    public function toMail($notifiable)
    {
        // Tạo URL xác minh tạm thời cho người dùng
        $verificationUrl = $this->verificationUrl($notifiable);

        // Tạo email với tiêu đề và nội dung tùy chỉnh
        return (new MailMessage)
            ->subject('🔐 Xác minh địa chỉ email của bạn') // Tiêu đề email
            ->view('emails.verify-email', [ // Sử dụng template email tùy chỉnh
                'user' => $notifiable, // Thông tin người dùng
                'verificationUrl' => $verificationUrl, // URL xác minh
                'appName' => Config::get('app.name') // Tên ứng dụng từ cấu hình
            ]);
    }

    /**
     * Tạo URL xác minh tạm thời với chữ ký bảo mật.
     *
     * @param mixed $notifiable Đối tượng người dùng nhận thông báo
     * @return string URL xác minh được tạo
     */
    protected function verificationUrl($notifiable)
    {
        // Tạo URL tạm thời với chữ ký bảo mật
        return URL::temporarySignedRoute(
            'verification.verify', // Tên route để xác minh email
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)), // Thời gian hết hạn của URL (mặc định 60 phút)
            [
                'id' => $notifiable->getKey(), // ID của người dùng
                'hash' => sha1($notifiable->getEmailForVerification()), // Hash của email để xác minh
            ],
            true // Trả về URL tuyệt đối
        );
    }
}
