<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Motel;

class ChatBoxAIController extends Controller
{
    public function index()
    {
        // Eager load tất cả quan hệ
        $motels = Motel::with([
            'district',           // Quận/huyện
            'images',             // Ảnh motel
            'amenities',          // Tiện ích motel
            'rooms.images',       // Ảnh phòng
            'rooms.amenities'     // Tiện ích phòng
        ])->get();

        return response()->json([
            'status' => 'success',
            'data' => $motels
        ]);
    }
}
