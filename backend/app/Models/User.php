<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Message;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    // Các trường có thể được gán giá trị hàng loạt
    protected $fillable = [
        'phone',
        'name',
        'email',
        'birthdate',
        'gender',
        'address',
        'avatar',
        'password',
        'identity_document',
        'role',
        'fcm_token'
    ];

    // Các trường ẩn khỏi phản hồi JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Ép kiểu dữ liệu cho các trường
    protected $casts = [
        'email_verified_at' => 'datetime', // Chuyển đổi thành đối tượng datetime
        'birthdate' => 'date', // Chuyển đổi thành định dạng ngày
        'role' => 'string', // Đảm bảo role là chuỗi
        'status' => 'string', // Đảm bảo status là chuỗi
    ];

    /**
     * Accessor để định dạng trường birthdate.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getBirthdateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    /**
     * Quan hệ một-nhiều với model Note.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Quan hệ một-nhiều với tin nhắn đã gửi.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Quan hệ một-nhiều với tin nhắn đã nhận.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Quan hệ một-nhiều với model Schedule.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'user_id');
    }
}
