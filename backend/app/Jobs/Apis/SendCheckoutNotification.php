<?php

namespace App\Jobs\Apis;

use App\Mail\Apis\CheckoutEmail;
use App\Models\Checkout;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;

/**
 * Job xử lý gửi thông báo liên quan đến yêu cầu trả phòng cho quản trị viên.
 */
class SendCheckoutNotification implements ShouldQueue
{
    // Sử dụng các trait để hỗ trợ hàng đợi và tuần tự hóa mô hình
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $checkout;
    protected $type;
    protected $title;
    protected $body;

    /**
     * Khởi tạo job với dữ liệu thông báo.
     *
     * @param Checkout $checkout Mô hình yêu cầu trả phòng
     * @param string $type Loại thông báo (pending, canceled, confirm, reject, left-room, update-bank)
     * @param string $title Tiêu đề thông báo
     * @param string $body Nội dung thông báo
     */
    public function __construct(Checkout $checkout, string $type, string $title, string $body)
    {
        $this->checkout = $checkout;
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Xử lý gửi thông báo qua email và Firebase Cloud Messaging (FCM).
     */
    public function handle()
    {
        try {
            // Lấy danh sách quản trị viên (Quản trị viên hoặc Super admin)
            $admins = User::where('role', 'Quản trị viên')->orWhere('role', 'Super admin')->get();
            if ($admins->isEmpty()) {
                // Ghi log cảnh báo nếu không tìm thấy quản trị viên
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            // Gửi email thông báo đến tất cả quản trị viên
            Mail::to($admins->pluck('email'))->send(new CheckoutEmail($this->checkout, $this->type, $this->title));

            // Tạo URL cơ bản để liên kết đến trang yêu cầu trả phòng
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/checkouts";

            // Gửi thông báo đến từng quản trị viên
            foreach ($admins as $admin) {
                // Tạo bản ghi thông báo trong cơ sở dữ liệu
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $this->title,
                    'content' => $this->body,
                ]);

                // Gửi thông báo qua FCM nếu quản trị viên có fcm_token
                if ($admin->fcm_token) {
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $this->title, 'body' => $this->body],
                        'data' => ['link' => $link],
                    ]);
                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error("Lỗi gửi thông báo trả phòng: {$this->title}", [
                'checkout_id' => $this->checkout->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
