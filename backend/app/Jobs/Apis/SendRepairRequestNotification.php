<?php

namespace App\Jobs\Apis;

use App\Mail\Apis\RepairRequestEmail;
use App\Models\Notification;
use App\Models\RepairRequest;
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
 * Job xử lý gửi thông báo về yêu cầu sửa chữa đến quản trị viên.
 */
class SendRepairRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var RepairRequest Yêu cầu sửa chữa
     */
    protected $repairRequest;

    /**
     * @var string Loại thông báo (pending/canceled)
     */
    protected $type;

    /**
     * @var string Tiêu đề thông báo
     */
    protected $title;

    /**
     * @var string Nội dung thông báo
     */
    protected $body;

    /**
     * Khởi tạo job với dữ liệu thông báo.
     *
     * @param RepairRequest $repairRequest Yêu cầu sửa chữa
     * @param string $type Loại thông báo
     * @param string $title Tiêu đề thông báo
     * @param string $body Nội dung thông báo
     */
    public function __construct(RepairRequest $repairRequest, string $type, string $title, string $body)
    {
        $this->repairRequest = $repairRequest;
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Xử lý gửi thông báo qua email và push notification.
     */
    public function handle()
    {
        try {
            // Tìm tất cả quản trị viên
            $admins = User::where('role', 'Quản trị viên')->orWhere('role', 'Super admin')->get();
            if ($admins->isEmpty()) {
                // Ghi log cảnh báo nếu không tìm thấy quản trị viên
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            // Gửi email thông báo đến quản trị viên
            Mail::to($admins->pluck('email'))->send(new RepairRequestEmail($this->repairRequest, $this->type, $this->title));

            // Lấy URL cơ bản của ứng dụng
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/repair-requests";

            // Gửi thông báo đẩy và lưu thông báo vào cơ sở dữ liệu
            foreach ($admins as $admin) {
                // Lưu thông báo vào cơ sở dữ liệu
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $this->title,
                    'content' => $this->body,
                ]);

                // Gửi thông báo đẩy qua Firebase nếu có FCM token
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
            // Ghi log lỗi nếu gửi thông báo thất bại
            Log::error("Lỗi gửi thông báo: {$this->title}", [
                'repair_request_id' => $this->repairRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
