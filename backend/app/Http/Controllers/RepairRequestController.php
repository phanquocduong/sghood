<?php

namespace App\Http\Controllers;

use App\Services\RepairRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RepairRequestController extends Controller
{
    protected $repairRequestService;

    public function __construct(RepairRequestService $repairRequestService)
    {
        $this->repairRequestService = $repairRequestService;
    }

    public function index(Request $request)
    {
        $searchQuery = $request->input('querySearch', '');
        $status = $request->input('status', null);
        $sortOption = $request->input('sort_by', 'created_at_desc');
        $perPage = $request->input('perPage', 10);

        $repairRequests = $this->repairRequestService->filter($searchQuery, $status, $sortOption, $perPage);

        return view('repair_requests.index', [
            'repairRequests' => $repairRequests,
            'searchQuery' => $searchQuery,
            'sortOption' => $sortOption,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }
    public function show($id)
    {
        $repair = $this->repairRequestService->getRepairRequestById($id);

        if (!$repair) {
            return redirect()->route('repair_requests.index')->with('error', 'Yêu cầu sửa chữa không tồn tại.');
        }

        return view('repair_requests.show', compact('repair'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Chờ xác nhận,Đang thực hiện,Hoàn thành,Huỷ bỏ',
        ]);

        $result = $this->repairRequestService->updateStatus($id, $request->status);

        if ($result) {
            return redirect()->route('repair_requests.index')->with('success', 'Cập nhật trạng thái thành công.');
        }

        return redirect()->route('repair_requests.index')->with('error', 'Cập nhật trạng thái thất bại.');
    }
}
