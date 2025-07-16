<?php
namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\Contract;
use App\Services\RoomService;

class StatisticController extends Controller
{
    protected $roomService;
    protected $transactionService;

    public function __construct(RoomService $roomService, TransactionService $transactionService)
    {
        $this->roomService = $roomService;
        $this->transactionService = $transactionService;
    }

    public function index(): View|RedirectResponse
    {
        $filters = [
            'search' => request()->get('search'),
            'transfer_type' => request()->get('transfer_type'),
            'month' => request()->get('month'),
            'year' => request()->get('year'),
        ];
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $roomsCount = $this->roomService->getAllRoomsCount();
        $roomsRentedCount = $this->roomService->getRentedRoomsCount();
        $transactions = $this->transactionService->getTransactionStats($filters);

        $countUsersToday = Contract::whereDate('start_date', '=', Carbon::today())
            ->distinct()
            ->count('user_id');

        // Thống kê người bắt đầu hợp đồng trong tháng này
        $countUsersThisMonth = Contract::whereBetween('start_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->distinct()->count('user_id');


        return view('statistics.index', compact( 'countUsersToday', 'countUsersThisMonth', 'roomsCount', 'roomsRentedCount', 'transactions'));
    }

}
