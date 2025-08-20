<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Services\RepairRequestService;
use Illuminate\Http\Request;

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

    public function updateNote(Request $request, $id)
    {
        try {
            $repairRequest = RepairRequest::findOrFail($id);

            $request->validate([
                'note' => 'nullable|string|max:1000'
            ]);

            $repairRequest->update([
                'note' => $request->input('note')
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ghi chú đã được cập nhật thành công!'
                ]);
            }

            return redirect()->back()->with('success', 'Ghi chú đã được cập nhật thành công!');

        } catch (\Exception $e) {
            \Log::error('Error updating repair request note: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật ghi chú: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật ghi chú');
        }
    }
    public function updateStatusDetail($id)
    {
        $repair = RepairRequest::findOrFail($id);

        switch ($repair->status) {
            case 'Chờ xác nhận':
                $repair->status = 'Đang thực hiện';
                break;
            case 'Đang thực hiện':
                $repair->status = 'Hoàn thành';
                $repair->repaired_at = now();
                break;
            default:
                return redirect()->back()->with('error', 'Không thể cập nhật trạng thái.');
        }

        $repair->save();

        return redirect()->route('repair_requests.show', $repair->id)
            ->with('success', 'Cập nhật trạng thái thành công!');
    }
}
