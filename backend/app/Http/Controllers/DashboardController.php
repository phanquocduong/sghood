<?php
namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Contract;
use App\Models\RepairRequest;
use App\Services\NoteService;
use App\Services\RoomService;
use App\Services\RepairRequestService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $noteService;
    protected $roomService;
    protected $repairRequestService;

    public function __construct(NoteService $noteService, RoomService $roomService, RepairRequestService $repairRequestService)
    {
        $this->noteService = $noteService;
        $this->roomService = $roomService;
        $this->repairRequestService = $repairRequestService;
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

        // Lấy repair requests cần xử lý (pending và in_progress) - chỉ lấy 5 cái mới nhất
        $repairRequests = $this->repairRequestService->getPendingRequests(5);

        // Debug: Kiểm tra dữ liệu repair requests
        Log::info('Repair Requests Count: ' . $repairRequests->count());
        Log::info('Repair Requests Data: ' . $repairRequests->toJson());

        // Fallback: Nếu không có pending/in_progress, lấy tất cả repair requests để test
        if ($repairRequests->isEmpty()) {
            $allRepairRequests = RepairRequest::with(['contract.user', 'contract.room'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $repairRequests = $allRepairRequests;
            Log::info('Using fallback - All Repair Requests Count: ' . $repairRequests->count());
        }

        // Thống kê người bắt đầu thuê hôm nay
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
