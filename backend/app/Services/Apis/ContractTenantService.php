<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendContractTenantNotification;
use App\Models\Contract;
use App\Models\ContractTenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ContractTenantService
{
    public function getContractTenants(int $contractId, int $userId): array
    {
        try {
            // Kiểm tra xem hợp đồng có tồn tại và thuộc về người dùng hay không
            $contract = Contract::where('id', $contractId)
                ->where('user_id', $userId)
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền truy cập',
                    'status' => 404,
                ];
            }

            // Lấy danh sách người ở cùng
            $tenants = ContractTenant::where('contract_id', $contractId)
                ->select('id', 'contract_id', 'name', 'phone', 'email', 'gender', 'birthdate', 'address', 'identity_document', 'relation_with_primary', 'status', 'rejection_reason', 'created_at')
                ->get()
                ->map(function (ContractTenant $tenant) {
                    return [
                        'id' => $tenant->id,
                        'contract_id' => $tenant->contract_id,
                        'name' => $tenant->name,
                        'phone' => $tenant->phone,
                        'email' => $tenant->email,
                        'gender' => $tenant->gender,
                        'birthdate' => $tenant->birthdate?->toDateString(),
                        'address' => $tenant->address,
                        'identity_document' => $tenant->identity_document,
                        'relation_with_primary' => $tenant->relation_with_primary,
                        'status' => $tenant->status,
                        'rejection_reason' => $tenant->rejection_reason,
                        'created_at' => $tenant->created_at->toDateTimeString(),
                    ];
                })
                ->toArray();

            return $tenants;
        } catch (\Throwable $e) {
            Log::error('Lỗi lấy danh sách người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }

    public function storeTenant(int $contractId, int $userId, array $data, array $files): array
    {
        try {
            // Kiểm tra hợp đồng
            $contract = Contract::where('id', $contractId)
                ->where('user_id', $userId)
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền thêm',
                    'status' => 404,
                ];
            }

            // Lưu ảnh CCCD
            $identityDocumentPaths = [];
            foreach ($files['identity_images'] as $index => $image) {
                if ($image instanceof UploadedFile) {
                    $filename = "images/tenants/tenant-{$contractId}-" . time() . "-{$index}.webp.enc";
                    $imageContent = (new ImageManager(new Driver()))
                        ->read($image)
                        ->toWebp(quality: 85)
                        ->toString();
                    $encryptedContent = Crypt::encrypt($imageContent);
                    Storage::disk('private')->put($filename, $encryptedContent);
                    $identityDocumentPaths[] = $filename;
                }
            }

            // Tạo người ở cùng
            $tenant = ContractTenant::create([
                'contract_id' => $contractId,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birthdate' => isset($data['birthdate']) && $data['birthdate']
                    ? \Carbon\Carbon::createFromFormat('d/m/Y', $data['birthdate'])
                    : null,
                'address' => $data['address'] ?? null,
                'identity_document' => implode('|', $identityDocumentPaths),
                'relation_with_primary' => $data['relation_with_primary'],
                'status' => 'Chờ duyệt',
            ]);

            // Gửi thông báo
            SendContractTenantNotification::dispatch(
                $contract,
                $tenant,
                'tenant_added',
                "Người ở cùng #{$tenant->id} đã được thêm",
                "Người dùng {$contract->user->name} đã thêm người ở cùng {$tenant->name} (ID: {$tenant->id}) vào hợp đồng #{$contract->id}."
            );

            return [
                'data' => [
                    'id' => $tenant->id,
                    'contract_id' => $tenant->contract_id,
                    'name' => $tenant->name,
                    'phone' => $tenant->phone,
                    'email' => $tenant->email,
                    'gender' => $tenant->gender,
                    'birthdate' => $tenant->birthdate?->toDateString(),
                    'address' => $tenant->address,
                    'identity_document' => $tenant->identity_document,
                    'relation_with_primary' => $tenant->relation_with_primary,
                    'status' => $tenant->status,
                    'rejection_reason' => $tenant->rejection_reason,
                    'created_at' => $tenant->created_at->toDateTimeString(),
                ],
                'message' => 'Thêm người ở cùng thành công',
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi thêm người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cancelTenant(int $contractId, int $tenantId, int $userId): array
    {
        try {
            // Kiểm tra hợp đồng
            $contract = Contract::where('id', $contractId)
                ->where('user_id', $userId)
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền hủy',
                    'status' => 404,
                ];
            }

            // Kiểm tra người ở cùng
            $tenant = ContractTenant::where('id', $tenantId)
                ->where('contract_id', $contractId)
                ->first();

            if (!$tenant) {
                return [
                    'error' => 'Không tìm thấy người ở cùng hoặc bạn không có quyền hủy',
                    'status' => 404,
                ];
            }

            if ($tenant->status !== 'Chờ duyệt' && $tenant->status !== 'Đã duyệt') {
                return [
                    'error' => 'Người ở cùng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            $tenant->update(['status' => 'Huỷ bỏ']);

            // Gửi thông báo
            SendContractTenantNotification::dispatch(
                $contract,
                $tenant,
                'tenant_canceled',
                "Người ở cùng #{$tenant->id} đã bị hủy đăng ký",
                "Người dùng {$contract->user->name} đã hủy đăng ký người ở cùng {$tenant->name} trong hợp đồng #{$contract->id}."
            );

            return ['data' => $tenant->fresh()];
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }
}
