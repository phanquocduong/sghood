<?php
namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
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
    protected $repairRequestService;

    public function __construct(NoteService $noteService, RepairRequestService $repairRequestService)
    {
        $this->noteService = $noteService;
        $this->repairRequestService = $repairRequestService;
    }

    public function index(): View|RedirectResponse
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $result = $this->noteService->getAllNotes();

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('error', $result['error']);
        }

        $notes = $result['data']->take(3);

        // Lấy repair requests cần xử lý (pending và in_progress) - chỉ lấy 5 cái mới nhất
        $repairRequests = $this->repairRequestService->getPendingRequests(5);

        if ($repairRequests->isEmpty()) {
            $allRepairRequests = RepairRequest::with(['contract.user', 'contract.room'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            $repairRequests = $allRepairRequests;
            Log::info('Using fallback - All Repair Requests Count: ' . $repairRequests->count());
        }

        return view('dashboard', compact('notes', 'repairRequests'));
    }

}
