<?php

namespace App\Http\Controllers;

use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $message = 'Trạng thái hợp đồng đã được cập nhật thành công! và đã gửi email thông báo đến người dùng.';
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

    // Hiển thị hình ảnh căn cước công dân
    public function showIdentityDocument(Request $request, $contractId, $imagePath)
    {
        try {
            Log::info('Attempting to show identity document', ['contractId' => $contractId, 'imagePath' => $imagePath]);

            $contract = $this->contractService->getContractById($contractId);
            if (isset($contract['error'])) {
                Log::error('Contract not found', ['error' => $contract['error']]);
                abort(404, $contract['error']);
            }

            $contract = $contract['data'];

            if (!$contract->user || !$contract->user->identity_document) {
                Log::error('No identity document found for user');
                abort(404, 'Không tìm thấy hình ảnh căn cước công dân');
            }

            $imagePaths = explode('|', $contract->user->identity_document);
            $fullImagePath = 'images/identity_document/' . $imagePath;

            if (!in_array($fullImagePath, $imagePaths)) {
                Log::error('Invalid image path', ['fullImagePath' => $fullImagePath, 'imagePaths' => $imagePaths]);
                abort(404, 'Hình ảnh không hợp lệ');
            }

            // Đọc file từ disk private
            Log::info('Reading encrypted file from private disk', ['fullImagePath' => $fullImagePath]);
            $encryptedContent = Storage::disk('private')->get($fullImagePath);
            Log::info('Decrypting content', ['contentLength' => strlen($encryptedContent)]);

            $decryptedContent = decrypt($encryptedContent);

            return response($decryptedContent)
                ->header('Content-Type', 'image/webp')
                ->header('Cache-Control', 'no-cache, private');

        } catch (\Throwable $e) {
            Log::error('Error displaying identity document: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'image_path' => $imagePath,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Đã xảy ra lỗi khi hiển thị hình ảnh căn cước công dân');
        }
    }
}
