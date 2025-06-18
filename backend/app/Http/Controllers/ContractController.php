<?php

namespace App\Http\Controllers;

use App\Services\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function index(Request $request)
    {
        $contracts = $this->contractService->getAllContracts(
            $request->get('querySearch', '') ?? '',
            $request->get('status', '') ?? '',
            (int) ($request->get('perPage', 10) ?? 10)
        );

        if (isset($contracts['error'])) {
            return redirect()->route('contracts.index')->with('error', $contracts['error']);
        }

        return view('contracts.index', [
            'contracts' => $contracts['data'],
            'querySearch' => $request->get('querySearch', '') ?? '',
            'status' => $request->get('status', '') ?? '',
            'perPage' => (int) ($request->get('perPage', 10) ?? 10),
        ]);
    }

    public function show($id)
    {
        $result = $this->contractService->getContractById($id);

        if (isset($result['error'])) {
            return redirect()->route('contracts.index')->with('error', $result['error']);
        }

        return view('contracts.detail-contracts', [
            'contract' => $result['data']
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Chờ xác nhận,Chờ duyệt,Chờ chỉnh sửa,Chờ ký,Hoạt động,Kết thúc,Huỷ bỏ',
        ]);

        $result = $this->contractService->updateContractStatus($id, $request->input('status'));

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Thông báo thành công với thông tin PDF nếu được tạo
        $message = 'Trạng thái hợp đồng đã được cập nhật thành công!';
        if ($request->input('status') === 'Hoạt động') {
            $message .= ' File PDF đã được tạo tự động.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function download($id)
    {
        $result = $this->contractService->downloadContractPdf($id);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $fileData = $result['data'];

        return response()->download(
            $fileData['file_path'],
            $fileData['file_name'],
            ['Content-Type' => $fileData['mime_type']]
        );
    }
}
