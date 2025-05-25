<?php
namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Xác minh địa chỉ email')
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Vui lòng nhấn vào nút bên dưới để xác minh địa chỉ email của bạn.')
            ->action('Xác minh Email', $verificationUrl)
            ->line('Nếu bạn không tạo tài khoản, vui lòng bỏ qua email này.')
            ->salutation('Trân trọng, ' . Config::get('app.name'));
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            true // URL tuyệt đối
        );
    }
}
