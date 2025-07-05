<?php
namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Contract;
use App\Services\NoteService;
use App\Services\RoomService;

class DashboardController extends Controller
{
    protected $noteService;
    protected $roomService;

    public function __construct(NoteService $noteService, RoomService $roomService)
    {
        $this->noteService = $noteService;
        $this->roomService = $roomService;
    }

    public function index(): View|RedirectResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $result = $this->noteService->getAllNotes();
        $roomsCount = $this->roomService->getAllRoomsCount();
        $roomsRentedCount = $this->roomService->getRentedRoomsCount();

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('error', $result['error']);
        }

        $notes = $result['data']->take(3);



        // Thống kê người bắt đầu thuê hôm nay
        $countUsersToday = Contract::whereDate('start_date', '=', Carbon::today())
            ->distinct()
            ->count('user_id');

        // Thống kê người bắt đầu hợp đồng trong tháng này
        $countUsersThisMonth = Contract::whereBetween('start_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->distinct()->count('user_id');


        return view('dashboard', compact('notes', 'countUsersToday', 'countUsersThisMonth', 'roomsCount', 'roomsRentedCount'));
    }

}
