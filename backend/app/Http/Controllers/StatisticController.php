<?php
namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\Contract;
use App\Services\RoomService;

class StatisticController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(): View|RedirectResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $roomsCount = $this->roomService->getAllRoomsCount();
        $roomsRentedCount = $this->roomService->getRentedRoomsCount();

        $countUsersToday = Contract::whereDate('start_date', '=', Carbon::today())
            ->distinct()
            ->count('user_id');

        // Thống kê người bắt đầu hợp đồng trong tháng này
        $countUsersThisMonth = Contract::whereBetween('start_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->distinct()->count('user_id');


        return view('dashboard', compact('notes', 'countUsersToday', 'countUsersThisMonth', 'roomsCount', 'roomsRentedCount', 'repairRequests'));
    }

}
