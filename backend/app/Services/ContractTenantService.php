<?php
namespace App\Services;

use App\Models\ContractTenant;
use App\Jobs\SendContractTenantStatusNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContractTenantService
{
    protected $contractTenants;

    public function __construct(ContractTenant $contractTenants)
    {
        $this->contractTenants = $contractTenants;
    }

    public function getAllContractTenants(string $querySearch = '', string $status = '', string $sort = 'desc', string $room_id = ''): array
    {
        try {
            DB::enableQueryLog();
            $query = $this->contractTenants->with(['contract', 'contract.user', 'contract.room']);

            // Apply search filter
            if (!empty($querySearch)) {
                $query->where(function ($q) use ($querySearch) {
                    if (strtolower($querySearch) === 'ng') {
                        return;
                    }

                    $numericQuery = $querySearch;
                    if (preg_match('/^ng(\d+)$/i', $querySearch, $matches)) {
                        $numericQuery = $matches[1];
                    }

                    $q->where('name', 'like', "%{$querySearch}%")
                      ->orWhere('phone', 'like', "%{$querySearch}%")
                      ->orWhere('email', 'like', "%{$querySearch}%")
                      ->orWhere('name', 'like', "%{$numericQuery}%")
                      ->orWhere('phone', 'like', "%{$numericQuery}%")
                      ->orWhere('email', 'like', "%{$numericQuery}%")
                      ->orWhereHas('contract.user', function ($userQuery) use ($querySearch, $numericQuery) {
                          $userQuery->where('name', 'like', "%{$querySearch}%")
                                   ->orWhere('name', 'like', "%{$numericQuery}%");
                      });
                });
            }

            // Apply room filter
            if (!empty($room_id)) {
                $query->whereHas('contract', function ($q) use ($room_id) {
                    $q->where('room_id', $room_id);
                });
            }

            // Apply status filter
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $sortDirection = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
            $contractTenants = $query->orderBy('created_at', $sortDirection)->paginate(15);

            Log::info('SQL Query for contract tenants', DB::getQueryLog());
            return ['data' => $contractTenants];
        } catch (\Throwable $e) {
            Log::error('Error getting contract tenants: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'sort' => $sort,
                'room_id' => $room_id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách người ở chung', 'status' => 500];
        }
    }

    public function getContractTenantById($id): array
    {
        try {
            $contractTenant = $this->contractTenants->with(['contract', 'contract.user', 'contract.room'])->find($id);

            if (!$contractTenant) {
                return ['error' => 'Không tìm thấy người ở chung', 'status' => 404];
            }

            return ['data' => $contractTenant];
        } catch (\Throwable $e) {
            Log::error('Error getting contract tenant by ID: ' . $e->getMessage(), [
                'contract_tenant_id' => $id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy thông tin người ở chung', 'status' => 500];
        }
    }

    public function updateContractTenantStatus($id, string $status, ?string $rejectionReason = null): array
    {
        try {
            $contractTenant = $this->contractTenants->with(['contract', 'contract.user', 'contract.room'])->find($id);

            if (!$contractTenant) {
                return ['error' => 'Không tìm thấy người ở chung', 'status' => 404];
            }

            $oldStatus = $contractTenant->status;

            Log::info('Updating contract tenant status', [
                'contract_tenant_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'rejection_reason' => $rejectionReason
            ]);

            $data = ['status' => $status];
            if ($status === 'Từ chối' && $rejectionReason) {
                $data['rejection_reason'] = $rejectionReason;
                // Xóa identity_document khi từ chối
                $this->deleteTenantIdentityDocument($id);
            } elseif ($status === 'Từ chối' && !$rejectionReason) {
                return ['error' => 'Lý do từ chối là bắt buộc', 'status' => 422];
            } elseif ($status !== 'Từ chối') {
                $data['rejection_reason'] = null;
            }

            $contractTenant->update($data);
            $contractTenant->refresh();

            // Dispatch notification job for all status changes
            if ($oldStatus !== $status) {
                SendContractTenantStatusNotification::dispatch($contractTenant, $status, $rejectionReason);

                Log::info('Contract tenant status notification job dispatched', [
                    'contract_tenant_id' => $contractTenant->id,
                    'user_id' => $contractTenant->contract->user_id,
                    'new_status' => $status,
                    'rejection_reason' => $rejectionReason ?? 'Không có lý do cụ thể'
                ]);
            }

            return ['data' => $contractTenant];
        } catch (\Throwable $e) {
            Log::error('Error updating contract tenant status: ' . $e->getMessage(), [
                'contract_tenant_id' => $id,
                'status' => $status,
                'rejection_reason' => $rejectionReason
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái người ở chung', 'status' => 500];
        }
    }

    public function getAllTenants()
    {
        return $this->contractTenants->all();
    }

    public function getPendingApprovals()
    {
        return $this->contractTenants->where('status', 'Chờ duyệt')->get();
    }

    public function deleteTenantIdentityDocument($tenantId): array
    {
        try {
            $tenant = ContractTenant::find($tenantId);

            if (!$tenant) {
                return ['success' => false, 'message' => 'Không tìm thấy người ở chung'];
            }

            if (!$tenant->identity_document) {
                return ['success' => false, 'message' => 'Người ở chung chưa có thông tin căn cước công dân'];
            }

            $imagePaths = explode('|', $tenant->identity_document);
            foreach ($imagePaths as $path) {
                if (Storage::disk('private')->exists($path)) {
                    Storage::disk('private')->delete($path);
                }
            }

            $tenant->update(['identity_document' => null]);

            Log::info('Deleted tenant identity document successfully', [
                'tenant_id' => $tenantId,
                'deleted_files' => $imagePaths
            ]);

            return ['success' => true, 'message' => 'Đã xóa thông tin căn cước công dân của người ở chung thành công'];
        } catch (\Throwable $e) {
            Log::error('Error deleting tenant identity document: ' . $e->getMessage(), [
                'tenant_id' => $tenantId,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xóa thông tin căn cước công dân'];
        }
    }
}
