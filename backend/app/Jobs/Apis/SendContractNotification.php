<?php

namespace App\Jobs\Apis;

use App\Mail\Apis\ContractEmail;
use App\Models\Contract;
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
 * Job xử lý gửi thông báo liên quan đến hợp đồng cho quản trị viên.
 */
class SendContractNotification implements ShouldQueue
{
    // Sử dụng các trait để hỗ trợ hàng đợi và tuần tự hóa mô hình
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contract;
    protected $type;
    protected $title;
    protected $body;

    /**
     * Khởi tạo job với dữ liệu thông báo.
     *
     * @param Contract $contract Mô hình hợp đồng
     * @param string $type Loại thông báo (pending, signed, canceled, early_terminated)
     * @param string $title Tiêu đề thông báo
     * @param string $body Nội dung thông báo
     */
    public function __construct(Contract $contract, string $type, string $title, string $body)
    {
        $this->contract = $contract;
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
            Mail::to($admins->pluck('email'))->send(new ContractEmail($this->contract, $this->type, $this->title));

            // Tạo URL cơ bản để liên kết đến trang hợp đồng
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/contracts/{$this->contract->id}";

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
            Log::error("Lỗi gửi thông báo hợp đồng: {$this->title}", [
                'contract_id' => $this->contract->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
