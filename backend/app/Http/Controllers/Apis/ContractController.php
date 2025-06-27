<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractService;
use App\Services\Apis\UserService;
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

    public function save(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'contract_content' => ['required', 'string'],
                'identity_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            $contract = $this->contractService->saveContract(
                $request->input('contract_content'),
                'Chờ duyệt'
            );

            if ($request->hasFile('identity_images')) {
                $this->userService->extractAndSaveIdentityImages(
                    Auth::user(),
                    $request->file('identity_images')
                );
            }

            return response()->json([
                'message' => 'Hợp đồng đã được lưu và đang chờ duyệt',
                'data' => $contract,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Lỗi lưu hợp đồng', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'status' => 422,
            ], 422);
        }
    }
}
