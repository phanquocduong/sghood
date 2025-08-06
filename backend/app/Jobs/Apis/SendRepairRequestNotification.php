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

class SendRepairRequestNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repairRequest;
    protected $type;
    protected $title;
    protected $body;

    public function __construct(RepairRequest $repairRequest, string $type, string $title, string $body)
    {
        $this->repairRequest = $repairRequest;
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
    }

    public function handle()
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->orWhere('role', 'Super admin')->get();
            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            Mail::to($admins->pluck('email'))->send(new RepairRequestEmail($this->repairRequest, $this->type, $this->title));
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/repair-requests";

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $this->title,
                    'content' => $this->body,
                ]);

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
            Log::error("Lỗi gửi thông báo: {$this->title}", [
                'repair_request_id' => $this->repairRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
