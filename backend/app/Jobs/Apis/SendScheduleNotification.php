<?php

namespace App\Jobs\Apis;

use App\Mail\Apis\ScheduleEmail;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;

class SendScheduleNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schedule;
    protected $type;
    protected $title;
    protected $body;

    public function __construct(Schedule $schedule, string $type, string $title, string $body)
    {
        $this->schedule = $schedule;
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

            Mail::to($admins->pluck('email'))->send(new ScheduleEmail($this->schedule, $this->type, $this->title));
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/schedules";

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
                'schedule_id' => $this->schedule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
