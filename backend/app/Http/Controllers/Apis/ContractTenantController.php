<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreContractTenantRequest;
use App\Services\Apis\ContractTenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractTenantController extends Controller
{
    public function __construct(
        private readonly ContractTenantService $contractTenantService
    ) {}

    public function index(int $contractId): JsonResponse
    {
        try {
            $tenants = $this->contractTenantService->getContractTenants($contractId, Auth::id());

            if (isset($tenants['error'])) {
                return response()->json([
                    'error' => $tenants['error'],
                    'status' => $tenants['status'],
                ], $tenants['status']);
            }

            return response()->json(['data' => $tenants]);
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy danh sách người ở cùng'], 500);
        }
    }

    public function store(StoreContractTenantRequest $request, int $contractId): JsonResponse
    {
        try {
            $result = $this->contractTenantService->storeTenant($contractId, Auth::id(), $request->validated(), $request->file());

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json([
                'message' => 'Đăng ký người ở cùng thành công',
                'data' => $result['data']
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi đăng ký người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi đăng ký người ở cùng'], 500);
        }
    }

    public function cancel(int $contractId, int $tenantId): JsonResponse
    {
        try {
            $result = $this->contractTenantService->cancelTenant($contractId, $tenantId, Auth::id());

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json(['message' => 'Hủy đăng ký người ở cùng thành công'], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy đăng ký người ở cùng'], 500);
        }
    }
}
