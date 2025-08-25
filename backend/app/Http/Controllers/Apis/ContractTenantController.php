<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\StoreContractTenantRequest;
use App\Services\Apis\ContractTenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu API liên quan đến quản lý người ở cùng hợp đồng.
 */
class ContractTenantController extends Controller
{
    /**
     * Khởi tạo controller với dịch vụ quản lý người ở cùng.
     *
     * @param ContractTenantService $contractTenantService Dịch vụ xử lý logic người ở cùng
     */
    public function __construct(
        private readonly ContractTenantService $contractTenantService
    ) {}

    /**
     * Lấy danh sách người ở cùng của một hợp đồng.
     *
     * @param int $contractId ID của hợp đồng
     * @return JsonResponse Phản hồi JSON chứa danh sách người ở cùng hoặc thông báo lỗi
     */
    public function index(int $contractId): JsonResponse
    {
        try {
            // Gọi dịch vụ để lấy danh sách người ở cùng
            $tenants = $this->contractTenantService->getContractTenants($contractId, Auth::id());

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($tenants['error'])) {
                return response()->json([
                    'error' => $tenants['error'],
                    'status' => $tenants['status'],
                ], $tenants['status']);
            }

            // Trả về phản hồi JSON với danh sách người ở cùng
            return response()->json(['data' => $tenants]);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi lấy danh sách người ở cùng'], 500);
        }
    }

    /**
     * Thêm mới một người ở cùng cho hợp đồng.
     *
     * @param StoreContractTenantRequest $request Yêu cầu chứa dữ liệu đã xác thực
     * @param int $contractId ID của hợp đồng
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function store(StoreContractTenantRequest $request, int $contractId): JsonResponse
    {
        try {
            // Gọi dịch vụ để lưu thông tin người ở cùng
            $result = $this->contractTenantService->storeTenant($contractId, Auth::id(), $request->validated(), $request->file());

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công và dữ liệu người ở cùng
            return response()->json([
                'message' => 'Đăng ký người ở cùng thành công',
                'data' => $result['data']
            ], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi đăng ký người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi đăng ký người ở cùng'], 500);
        }
    }

    /**
     * Hủy đăng ký người ở cùng.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $tenantId ID của người ở cùng
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function cancel(int $contractId, int $tenantId): JsonResponse
    {
        try {
            // Gọi dịch vụ để hủy đăng ký người ở cùng
            $result = $this->contractTenantService->cancelTenant($contractId, $tenantId, Auth::id());

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['message' => 'Hủy đăng ký người ở cùng thành công'], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy đăng ký người ở cùng'], 500);
        }
    }

    /**
     * Xác nhận người ở cùng vào ở chính thức.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $tenantId ID của người ở cùng
     * @return JsonResponse Phản hồi JSON với thông báo thành công hoặc lỗi
     */
    public function confirm(int $contractId, int $tenantId): JsonResponse
    {
        try {
            // Gọi dịch vụ để xác nhận người ở cùng
            $result = $this->contractTenantService->confirmTenant($contractId, $tenantId, Auth::id());

            // Kiểm tra nếu có lỗi trong kết quả
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            // Trả về phản hồi JSON với thông báo thành công
            return response()->json(['message' => 'Xác nhận người ở cùng vào ở thành công'], 200);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi xác nhận người ở cùng: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xác nhận người ở cùng'], 500);
        }
    }
}
