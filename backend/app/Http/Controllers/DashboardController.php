<?php
namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Services\ContractExtensionService;
use App\Services\ContractService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\Contract;
use App\Models\RepairRequest;
use App\Services\NoteService;
use App\Services\RoomService;
use App\Services\RepairRequestService;
use App\Services\ScheduleService;
use App\Services\MessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $noteService;
    protected $repairRequestService;
    protected $scheduleService;
    protected $contractService;
    protected $contractExtensionsService;
    protected $checkoutService;

    public function __construct(NoteService $noteService, RepairRequestService $repairRequestService, ScheduleService $scheduleService, ContractService $contractService, ContractExtensionService $contractExtensionsService, CheckoutService $checkoutService)
    {
        $this->noteService = $noteService;
        $this->repairRequestService = $repairRequestService;
        $this->scheduleService = $scheduleService;
        $this->contractService = $contractService;
        $this->contractExtensionsService = $contractExtensionsService;
        $this->checkoutService = $checkoutService;
    }

    public function index(): View|RedirectResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $result = $this->noteService->getAllNotes();
        $schedules = $this->scheduleService->getSchedules('', '', 5, 'created_at_desc');
        $contracts = $this->contractService->getContractsEndingSoon();
        $justSignedContracts = $this->contractService->signedContracts();
        $contractExtensions = $this->contractExtensionsService->getPendingApprovals();
        $checkouts = $this->checkoutService->getCheckoutsByStatus();

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('error', $result['error']);
        }

        $notes = $result['data']->take(3);
        $schedules = $schedules['data']->take(3);
        $contracts = collect($contracts['data'])->take(3);
        $justSignedContracts = collect($justSignedContracts['data'])->take(3);
        $contractExtensions = collect($contractExtensions)->take(3);
       $checkouts = collect($checkouts)->take(3);

        // Lấy repair requests cần xử lý (pending và in_progress) - chỉ lấy 5 cái mới nhất
        $repairRequests = $this->repairRequestService->getPendingRequests(5);

        if ($repairRequests->isEmpty()) {
            $allRepairRequests = RepairRequest::whereIn('status', ['Chờ xác nhận', 'Đang thực hiện'])
                ->with(['contract.user', 'contract.room'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $repairRequests = $allRepairRequests;
            Log::info('Using fallback - All Repair Requests Count: ' . $repairRequests->count());
        }
        //messages
        $authId = Auth::id();
        if (!$authId) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để xem tin nhắn.');
        }
        $messages = MessageService::getUnreadMessagesDashboard();
        if (isset($messages['error'])) {
            return redirect()->route('dashboard')->with('error', $messages['error']);
        }

        $messages = $messages['data'];
        return view('dashboard', compact('notes', 'repairRequests','schedules','contracts','justSignedContracts', 'contractExtensions', 'messages', 'checkouts'));
    }

}
