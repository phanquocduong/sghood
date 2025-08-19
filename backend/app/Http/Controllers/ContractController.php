<?php

namespace App\Http\Controllers;

use App\Services\ContractService;
use App\Models\Contract;
use App\Models\ContractExtension;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
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
            $request->get('sort', 'desc') ?? 'desc'
        );

        if (isset($contracts['error'])) {
            return redirect()->route('contracts.index')->with('error', $contracts['error']);
        }

        return view('contracts.index', [
            'contracts' => $contracts['data'],
            'querySearch' => $request->get('querySearch', '') ?? '',
            'status' => $request->get('status', '') ?? '',
            'sort' => $request->get('sort', 'desc') ?? 'desc',
        ]);
    }

    public function show($id)
    {
        $result = $this->contractService->getContractById($id);

        if (isset($result['error'])) {
            return redirect()->route('contracts.index')->with('error', $result['error']);
        }

        // Lấy các gia hạn hợp đồng đã được duyệt
        $contractExtensions = ContractExtension::where('contract_id', $id)
            ->where('status', 'Hoạt động')
            ->orderBy('created_at', 'desc')
            ->get();

        // Kiểm tra hóa đơn chưa thanh toán quá ngày
        $hasOverdueInvoices = $this->contractService->checkOverdueInvoices($id);

        $config = Config::where('config_key', 'rental_contract_terms')->first();
        $terminationRights = $config ? json_decode($config->config_value, true)[0]['termination_rights'] : [];

        return view('contracts.detail-contracts', [
            'contract' => $result['data'],
            'contractExtensions' => $contractExtensions,
            'hasOverdueInvoices' => $hasOverdueInvoices,
            'terminationRights' => $terminationRights
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

    public function terminateEarly(Request $request, $id)
    {
        $request->validate([
            'termination_reason' => 'nullable|string|max:1000',
        ]);

        $result = $this->contractService->terminateContractEarly($id, $request->input('termination_reason'));

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
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

    // Hiển thị hình ảnh căn cước công dân với cache để tối ưu hiệu suất
    public function showIdentityDocument(Request $request, $contractId, $imagePath)
    {
        try {
            // Cache key để tránh decrypt nhiều lần
            $cacheKey = "identity_doc_{$contractId}_{$imagePath}";

            // Kiểm tra cache trước
            $cachedContent = Cache::get($cacheKey);
            if ($cachedContent) {
                Log::info('Đang phục vụ tài liệu nhận dạng từ bộ nhớ đệm', [
                    'contractId' => $contractId,
                    'imagePath' => $imagePath
                ]);

                return Response::make($cachedContent)
                    ->header('Content-Type', 'image/webp')
                    ->header('Cache-Control', 'public, max-age=3600')
                    ->header('Expires', now()->addHour()->toRfc7231String());
            }

            Log::info('Cố gắng xuất trình giấy tờ tùy thân', [
                'contractId' => $contractId,
                'imagePath' => $imagePath
            ]);

            $contract = $this->contractService->getContractById($contractId);
            if (isset($contract['error'])) {
                Log::error('Contract not found', ['error' => $contract['error']]);
                abort(404, $contract['error']);
            }

            $contract = $contract['data'];

            if (!$contract->user || !$contract->user->identity_document) {
                Log::error('Không tìm thấy giấy tờ tùy thân cho người dùng');
                abort(404, 'Không tìm thấy hình ảnh căn cước công dân');
            }

            $imagePaths = explode('|', $contract->user->identity_document);
            $fullImagePath = 'images/identity_document/' . $imagePath;

            if (!in_array($fullImagePath, $imagePaths)) {
                Log::error('Invalid image path', [
                    'fullImagePath' => $fullImagePath,
                    'imagePaths' => $imagePaths
                ]);
                abort(404, 'Hình ảnh không hợp lệ');
            }

            // Kiểm tra file tồn tại trước khi đọc
            if (!Storage::disk('private')->exists($fullImagePath)) {
                Log::error('Không tìm thấy tệp giấy tờ tùy thân', ['fullImagePath' => $fullImagePath]);
                abort(404, 'File hình ảnh không tồn tại');
            }

            // Đọc file từ disk private
            Log::info('Đọc tập tin được mã hóa từ đĩa riêng', ['fullImagePath' => $fullImagePath]);
            $encryptedContent = Storage::disk('private')->get($fullImagePath);

            if (!$encryptedContent) {
                Log::error('Không đọc được nội dung được mã hóa');
                abort(500, 'Không thể đọc file hình ảnh');
            }

            Log::info('Giải mã nội dung', ['contentLength' => strlen($encryptedContent)]);
            $decryptedContent = decrypt($encryptedContent);

            // Cache decrypted content trong 1 giờ
            Cache::put($cacheKey, $decryptedContent, 3600);

            Log::info('Đã gửi thành công giấy tờ tùy thân', [
                'contractId' => $contractId,
                'imagePath' => $imagePath,
                'contentSize' => strlen($decryptedContent)
            ]);

            return Response::make($decryptedContent)
                ->header('Content-Type', 'image/webp')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Expires', now()->addHour()->toRfc7231String())
                ->header('Last-Modified', now()->toRfc7231String());

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Giải mã không thành công cho tài liệu nhận dạng', [
                'contract_id' => $contractId,
                'image_path' => $imagePath,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Không thể giải mã hình ảnh căn cước công dân');
        } catch (\Throwable $e) {
            Log::error('Lỗi hiển thị giấy tờ tùy thân: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'image_path' => $imagePath,
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Đã xảy ra lỗi khi hiển thị hình ảnh căn cước công dân');
        }
    }

    public function sendRevisionEmail(Request $request, $contractId)
    {
        $result = $this->contractService->sendRevisionEmail($contractId, $request->revision_reason);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function updateContent(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $data = $request->input('content', []);

            // Ensure data is an array
            if (!is_array($data)) {
                throw new \Exception('Dữ liệu nhập vào không hợp lệ');
            }

            // Update the contract content using the service
            $newContent = $this->contractService->updateContractContent($contract, $data);

            $statusResult = $this->contractService->updateContractStatus($id, 'Chờ ký');

            if (isset($statusResult['error'])) {
                throw new \Exception($statusResult['error']);
            }

            return response()->json([
                'success' => true,
                'newContent' => $newContent,
                'message' => 'Cập nhật hợp đồng thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating contract content: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật hợp đồng thất bại: ' . $e->getMessage()
            ], 500);
        }
    }
    public function reactivate(Request $request, $id)
    {
        $result = $this->contractService->reactivateContract($id);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
