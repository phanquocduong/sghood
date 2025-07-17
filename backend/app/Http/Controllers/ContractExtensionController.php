<?php
namespace App\Http\Controllers;

use App\Services\ContractExtensionService;
use Illuminate\Http\Request;

class ContractExtensionController extends Controller
{
    protected $contractExtensionService;

    public function __construct(ContractExtensionService $contractExtensionService)
    {
        $this->contractExtensionService = $contractExtensionService;
    }

    public function index(Request $request)
    {
        $contractExtensions = $this->contractExtensionService->getAllContractExtensions(
            $request->get('querySearch', '') ?? '',
            $request->get('status', '') ?? '',
            $request->get('sort', 'desc') ?? 'desc'
        );

        if (isset($contractExtensions['error'])) {
            return redirect()->route('contracts.index')->with('error', $contractExtensions['error']);
        }

        return view('contracts.contract-extensions', [
            'contractExtensions' => $contractExtensions['data'],
            'querySearch' => $request->get('querySearch', '') ?? '',
            'status' => $request->get('status', '') ?? '',
            'sort' => $request->get('sort', 'desc') ?? 'desc',
        ]);
    }

    public function showExtension($id)
    {
        $result = $this->contractExtensionService->getContractExtensionById($id);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 500);
        }

        if (request()->ajax()) {
            return response()->json([
                'html' => view('contract_extensions.partials.detail-modal', [
                    'contractExtension' => $result['data']
                ])->render()
            ]);
        }

        return view('contract_extensions.show', [
            'contractExtension' => $result['data']
        ]);
    }

    public function updateExtensionStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:Hoạt động,Từ chối',
        'rejection_reason' => 'required_if:status,Từ chối|max:255',
    ]);

    $result = $this->contractExtensionService->updateContractExtensionStatus(
        $id,
        $request->input('status'),
        $request->input('rejection_reason')
    );

    if (isset($result['error'])) {
        return redirect()->back()->with('error', $result['error']);
    }

    $message = 'Trạng thái gia hạn hợp đồng đã được cập nhật thành công!';
    if ($request->input('status') === 'Từ chối') {
        $message .= ' Lý do từ chối đã được lưu.';
    }

    return redirect()->back()->with('success', $message);
}
}
