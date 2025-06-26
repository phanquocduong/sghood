<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $querySearch = $request->get('querySearch', '');
        $status = $request->get('status', '');
        $sort = $request->get('sort', 'desc');
        $perPage = $request->get('perPage', 10);

        $notifications = Notification::query()
            ->whereHas('user', function ($query) {
                $query->where('role', "Quản trị viên");
            })
            ->when($querySearch, function ($query, $querySearch) {
                return $query->where('title', 'like', "%$querySearch%");
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($sort === 'asc', function ($query) {
                return $query->orderBy('created_at', 'asc');
            }, function ($query) {
                return $query->orderBy('created_at', 'desc');
            })
            ->paginate($perPage);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        if ($notification->status === 'Chưa đọc') {
            $notification->status = 'Đã đọc';
            $notification->save();
        }
        return response()->json(['message' => 'Marked as read']);
    }


    public function headerData()
    {
        $unreadCount = Notification::where('status', 'Chưa đọc')
        ->whereHas('user', function ($query) {
            $query->where('role', 'Quản trị viên');
        })
        ->count();
        $latest = Notification::latest()
        ->whereHas('user', function ($query) {
            $query->where('role', 'Quản trị viên');
        })
        ->take(3)->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'latest' => $latest->map(function ($n) {
                return [
                    'title' => $n->title,
                    'created_at' => $n->created_at->diffForHumans(),
                    'url' => route('notifications.index'),
                    'status' => $n->status,
                ];
            }),
        ]);
    }

}
