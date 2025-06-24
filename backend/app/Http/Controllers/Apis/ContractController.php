<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\Apis\ContractService;
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
        private readonly UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $contracts = $this->contractService->getUserContracts();

            return response()->json([
                'data' => $contracts,
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách hợp đồng', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng',
                'status' => 500,
            ], 500);
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

            return response()->json([
                'data' => $contract,
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy chi tiết hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi lấy chi tiết hợp đồng',
                'status' => 500,
            ], 500);
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

            return response()->json([
                'message' => 'Hủy hợp đồng thành công',
                'status' => 200,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Đã xảy ra lỗi khi hủy hợp đồng',
                'status' => 500,
            ], 500);
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

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'contract_content' => ['required', 'string'],
                'identity_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            // Lấy thông tin hợp đồng hiện tại
            $contract = Contract::where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            // Xác định trạng thái mới dựa trên trạng thái hiện tại
            $newStatus = $this->determineNewStatus($contract->status);

            // Lưu hợp đồng với trạng thái mới
            $updatedContract = $this->contractService->saveContract(
                $request->input('contract_content'),
                $newStatus,
                $id
            );

            // Chỉ xử lý ảnh căn cước khi trạng thái là "Chờ xác nhận" và có file được gửi lên
            if ($contract->status === 'Chờ xác nhận' && $request->hasFile('identity_images')) {
                $this->userService->extractAndSaveIdentityImages(
                    Auth::user(),
                    $request->file('identity_images')
                );
            }

            return response()->json([
                'message' => $this->getSuccessMessage($contract->status),
                'data' => $updatedContract,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Hợp đồng không tồn tại hoặc bạn không có quyền truy cập.',
                'status' => 404,
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật hợp đồng', [
                'user_id' => Auth::id(),
                'contract_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Đã có lỗi xảy ra khi cập nhật hợp đồng. Vui lòng thử lại.',
                'status' => 500,
            ], 500);
        }
    }

     /**
     * Xác định trạng thái mới dựa trên trạng thái hiện tại
     */
    private function determineNewStatus(string $currentStatus): string
    {
        switch ($currentStatus) {
            case 'Chờ xác nhận':
                return 'Chờ duyệt';
            case 'Chờ chỉnh sửa':
                return 'Chờ duyệt';
            default:
                return $currentStatus; // Giữ nguyên trạng thái nếu không phải 2 trạng thái trên
        }
    }

    /**
     * Lấy thông báo thành công phù hợp
     */
    private function getSuccessMessage(string $oldStatus): string
    {
        if ($oldStatus === 'Chờ xác nhận') {
            return 'Hợp đồng đã được lưu và đang chờ duyệt';
        } elseif ($oldStatus === 'Chờ chỉnh sửa') {
            return 'Hợp đồng đã được chỉnh sửa và gửi lại để duyệt';
        } else {
            return 'Hợp đồng đã được cập nhật thành công';
        }
    }
}
