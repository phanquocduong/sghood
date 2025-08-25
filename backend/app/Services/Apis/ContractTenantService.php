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

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến người ở cùng hợp đồng.
 */
class ContractTenantService
{
    /**
     * Lấy danh sách người ở cùng của một hợp đồng.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $userId ID của người dùng
     * @return array Danh sách người ở cùng hoặc thông báo lỗi
     */
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
                ->select('id', 'contract_id', 'name', 'phone', 'email', 'gender', 'birthdate', 'address', 'relation_with_primary', 'status', 'rejection_reason', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function (ContractTenant $tenant) {
                    // Định dạng dữ liệu người ở cùng
                    return [
                        'id' => $tenant->id,
                        'contract_id' => $tenant->contract_id,
                        'name' => $tenant->name,
                        'phone' => $tenant->phone,
                        'email' => $tenant->email,
                        'gender' => $tenant->gender,
                        'birthdate' => $tenant->birthdate?->toDateString(),
                        'address' => $tenant->address,
                        'relation_with_primary' => $tenant->relation_with_primary,
                        'status' => $tenant->status,
                        'rejection_reason' => $tenant->rejection_reason,
                        'created_at' => $tenant->created_at->toDateTimeString(),
                    ];
                })
                ->toArray();

            // Trả về danh sách người ở cùng và số lượng tối đa người ở
            return [
                'tenants' => $tenants,
                'max_occupants' => $contract->room->max_occupants ?? 0
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Thêm mới một người ở cùng cho hợp đồng.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $userId ID của người dùng
     * @param array $data Dữ liệu đã xác thực của người ở cùng
     * @param array $files Các tệp ảnh CCCD
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
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

            // Kiểm tra số lượng người ở
            $currentTenantsCount = ContractTenant::where('contract_id', $contractId)
                ->where('status', 'Đang ở')
                ->count();
            $currentOccupants = $currentTenantsCount + 1; // +1 cho người thuê chính
            $maxOccupants = $contract->room->max_occupants ?? 0;

            if ($currentOccupants + 1 > $maxOccupants) {
                return [
                    'error' => 'Số lượng người ở đã đạt tối đa cho phòng này',
                    'status' => 400,
                ];
            }

            // Lưu ảnh CCCD
            $identityDocumentPaths = [];
            foreach ($files['identity_images'] as $index => $image) {
                if ($image instanceof UploadedFile) {
                    // Tạo tên tệp duy nhất với định dạng webp mã hóa
                    $filename = "images/tenants/tenant-{$contractId}-" . time() . "-{$index}.webp.enc";
                    // Chuyển đổi ảnh sang định dạng webp và mã hóa
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

            // Gửi thông báo thêm người ở cùng đến quản trị viên
            SendContractTenantNotification::dispatch(
                $contract,
                $tenant,
                'tenant_added',
                "Người ở cùng #{$tenant->id} đã được đăng ký thêm",
                "Người dùng {$contract->user->name} đã đăng ký thêm người ở cùng {$tenant->name} (ID: {$tenant->id}) vào hợp đồng #{$contract->id}."
            );

            // Trả về dữ liệu người ở cùng đã tạo
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
                    'relation_with_primary' => $tenant->relation_with_primary,
                    'status' => $tenant->status,
                    'rejection_reason' => $tenant->rejection_reason,
                    'created_at' => $tenant->created_at->toDateTimeString(),
                ],
                'message' => 'Thêm người ở cùng thành công',
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi thêm người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hủy đăng ký người ở cùng.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $tenantId ID của người ở cùng
     * @param int $userId ID của người dùng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
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

            // Kiểm tra trạng thái người ở cùng
            if ($tenant->status !== 'Chờ duyệt' && $tenant->status !== 'Đã duyệt') {
                return [
                    'error' => 'Người ở cùng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            // Cập nhật trạng thái người ở cùng thành "Huỷ bỏ"
            $tenant->update(['status' => 'Huỷ bỏ']);

            // Gửi thông báo hủy người ở cùng đến quản trị viên
            SendContractTenantNotification::dispatch(
                $contract,
                $tenant,
                'tenant_canceled',
                "Người ở cùng #{$tenant->id} đã bị hủy đăng ký",
                "Người dùng {$contract->user->name} đã hủy đăng ký người ở cùng {$tenant->name} trong hợp đồng #{$contract->id}."
            );

            // Trả về dữ liệu người ở cùng đã cập nhật
            return ['data' => $tenant->fresh()];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xác nhận người ở cùng vào ở chính thức.
     *
     * @param int $contractId ID của hợp đồng
     * @param int $tenantId ID của người ở cùng
     * @param int $userId ID của người dùng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function confirmTenant(int $contractId, int $tenantId, int $userId): array
    {
        try {
            // Kiểm tra hợp đồng
            $contract = Contract::where('id', $contractId)
                ->where('user_id', $userId)
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền xác nhận',
                    'status' => 404,
                ];
            }

            // Kiểm tra người ở cùng
            $tenant = ContractTenant::where('id', $tenantId)
                ->where('contract_id', $contractId)
                ->first();

            if (!$tenant) {
                return [
                    'error' => 'Không tìm thấy người ở cùng hoặc bạn không có quyền xác nhận',
                    'status' => 404,
                ];
            }

            // Kiểm tra trạng thái người ở cùng
            if ($tenant->status !== 'Đã duyệt') {
                return [
                    'error' => 'Người ở cùng không ở trạng thái có thể xác nhận vào ở',
                    'status' => 400,
                ];
            }

            // Cập nhật trạng thái người ở cùng thành "Đang ở"
            $tenant->update(['status' => 'Đang ở']);

            // Gửi thông báo xác nhận người ở cùng đến quản trị viên
            SendContractTenantNotification::dispatch(
                $contract,
                $tenant,
                'tenant_confirmed',
                "Người ở cùng #{$tenant->id} đã được xác nhận vào ở",
                "Người dùng {$contract->user->name} đã xác nhận người ở cùng {$tenant->name} (ID: {$tenant->id}) vào ở chính thức trong hợp đồng #{$contract->id}."
            );

            // Trả về dữ liệu người ở cùng đã cập nhật
            return ['data' => $tenant->fresh()];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi xác nhận người ở cùng: ' . $e->getMessage());
            throw $e;
        }
    }
}
