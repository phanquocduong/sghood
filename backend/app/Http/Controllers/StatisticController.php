<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Services\TransactionService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\Contract;
use App\Services\RoomService;
use App\Services\ContractService;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    protected $roomService;
    protected $transactionService;
    protected $contractService;

    public function __construct(RoomService $roomService, TransactionService $transactionService, ContractService $contractService)
    {
        $this->roomService = $roomService;
        $this->transactionService = $transactionService;
        $this->contractService = $contractService;
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
        $monthlyRevenue = $this->getMonthlyRevenue();

        $countUsersToday = Contract::whereDate('start_date', '=', Carbon::today())
            ->distinct()
            ->count('user_id');

        // Thống kê người bắt đầu hợp đồng trong tháng này
        $countUsersThisMonth = Contract::whereBetween('start_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->distinct()->count('user_id');

        // Thống kê số lượng phòng trống motel
        $availableRoomsCount = $this->transactionService->getAvailableRoomsCount();
        $availableRoomsByMotel = $this->roomService->getAvailableRoomsPerMotel();

        $tenants = $this->contractService->getTenantsByContractStatus();

        $todayRevenue = DB::table('transactions')
            ->where('transfer_type', 'in')
            ->whereDate('transaction_date', $today)
            ->sum('transfer_amount');

        $monthRevenue = DB::table('transactions')
            ->where('transfer_type', 'in')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('transfer_amount');

        $isNearExpiration = (int) Config::getValue('is_near_expiration', 30);


        return view('statistics.index', [
            'countUsersToday' => $countUsersToday,
            'countUsersThisMonth' => $countUsersThisMonth,
            'roomsCount' => $roomsCount,
            'roomsRentedCount' => $roomsRentedCount,
            'transactions' => $transactions,
            'availableRoomsCount' => $availableRoomsCount,
            'availableRoomsByMotel' => $availableRoomsByMotel,
            'currentTenants' => $tenants['current'],
            'expiringTenants' => $tenants['expiring'],
            'expiredTenants' => $tenants['expired'],
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,
            'isNearExpiration' => $isNearExpiration,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }


    private function getMonthlyRevenue()
    {
        $currentYear = date('Y');
        $monthlyData = [];

        // Lấy dữ liệu từ bảng invoices hoặc payments (tùy theo cấu trúc database của bạn)
        for ($month = 1; $month <= 12; $month++) {
            $revenue = DB::table('transactions')
                ->where('transfer_type', 'in')
                ->whereYear('transaction_date', $currentYear) // Năm hiện tại
                ->whereMonth('transaction_date', $month) // Tháng cụ thể
                ->sum('transfer_amount');
            // Nếu không có giao dịch trong tháng, đặt doanh thu là 0
            if ($revenue === null) {
                $revenue = 0;
            }
            $monthlyData[] = (float) $revenue;
        }

        return $monthlyData;
    }
}
