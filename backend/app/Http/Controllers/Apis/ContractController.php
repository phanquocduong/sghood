<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\UpdateContractRequest;
use App\Models\Contract;
use App\Services\Apis\ContractService;
use App\Services\Apis\InvoiceService;
use App\Services\Apis\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractController extends Controller
{
    public function __construct(
        private readonly ContractService $contractService,
        private readonly UserService $userService,
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $contracts = $this->contractService->getUserContracts();

            return response()->json(['data' => $contracts]);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hợp đồng', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $contract = $this->contractService->getContractDetail($id);

            if (isset($contract['error'])) {
                return response()->json([
                    'error' => $contract['error'],
                    'status' => $contract['status'],
                ], $contract['status']);
            }

            return response()->json(['data' => $contract]);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy chi tiết hợp đồng'], 500);
        }
    }

    public function reject(int $id): JsonResponse
    {
        try {
            $result = $this->contractService->rejectContract($id);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json(['message' => 'Hủy hợp đồng thành công'], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy hợp đồng'], 500);
        }
    }

    public function extractIdentityImages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'identity_images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            $cccdData = $this->contractService->extractIdentityImages($request->file('identity_images'));

            return response()->json([
                'data' => $cccdData,
                'message' => 'Trích xuất thông tin CCCD thành công',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Lỗi trích xuất CCCD', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }

    public function update(UpdateContractRequest $request, int $id): JsonResponse
    {
        try {
            $contract = Contract::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
            $updatedContract = $this->contractService->saveContract($request->input('contract_content'), $id);

            if ($contract->status === 'Chờ xác nhận' && $request->hasFile('identity_images')) {
                $this->userService->extractAndSaveIdentityImages(
                    Auth::user(),
                    $request->file('identity_images')
                );
            }

            return response()->json([
                'message' => $this->contractService->getSuccessMessage($contract->status),
                'data' => $updatedContract,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Đã có lỗi xảy ra khi cập nhật hợp đồng.'], 500);
        }
    }

    public function sign(Request $request, int $id): JsonResponse
    {
        try {
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $id)
                ->where('status', 'Chờ ký')
                ->firstOrFail();

            $updatedContract = $this->contractService->signContract(
                $id,
                $request->input('signature'),
                $request->input('content')
            );

            $invoice = $this->invoiceService->createDepositInvoice($contract);

            return response()->json([
                'message' => 'Hợp đồng đã được ký thành công. Vui lòng thanh toán tiền cọc.',
                'data' => $updatedContract,
                'invoice_id' => $invoice->id
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc không ở trạng thái chờ ký.'], 404);
        } catch (\Throwable $e) {
            Log::error('Lỗi ký hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã có lỗi xảy ra khi ký hợp đồng. Vui lòng thử lại.'], 500);
        }
    }

    public function downloadPdf(int $id): JsonResponse
    {
        try {
            $contract = Contract::where('user_id', Auth::id())->where('id', $id)->firstOrFail();

            if ($contract->status !== 'Hoạt động') {
                return response()->json(['error' => 'Hợp đồng chưa thể tải PDF.'], 400);
            }

            // Tạo và lưu PDF nếu chưa có
            if (!$contract->file) {
                $this->contractService->generateAndSaveContractPdf($id);
                $contract->refresh();
            }

            // Trả về URL của file (sử dụng route để phục vụ file private)
            $fileUrl = url('/contract/pdf/' . $id);

            return response()->json(['data' => ['file_url' => $fileUrl]]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.'], 404);
        } catch (\Throwable $e) {
            Log::error('Lỗi tải PDF hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Đã có lỗi xảy ra khi tải PDF.'], 500);
        }
    }
}
