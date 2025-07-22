<?php

namespace App\Jobs\Apis;

use App\Mail\Apis\BookingEmail;
use App\Models\Booking;
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

class SendBookingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $type;
    protected $title;
    protected $body;

    public function __construct(Booking $booking, string $type, string $title, string $body)
    {
        $this->booking = $booking;
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
    }

    public function handle()
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();
            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            Mail::to($admins->pluck('email'))->send(new BookingEmail($this->booking, $this->type, $this->title));
            $messaging = app('firebase.messaging');
            $baseUrl = config('app.url');
            $link = "$baseUrl/bookings";

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
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
