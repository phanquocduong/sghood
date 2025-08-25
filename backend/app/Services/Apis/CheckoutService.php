<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendCheckoutNotification;
use App\Models\Checkout;
use App\Models\Contract;
use App\Models\ContractTenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến trả phòng.
 */
class CheckoutService
{
    /**
     * Gửi yêu cầu trả phòng cho hợp đồng.
     *
     * @param int $id ID của hợp đồng
     * @param array|null $bankInfo Thông tin ngân hàng (nếu có)
     * @param string $checkOutDate Ngày dự kiến trả phòng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function requestReturn(int $id, ?array $bankInfo, string $checkOutDate): array
    {
        try {
            // Tìm hợp đồng đang hoạt động của người dùng
            $contract = Contract::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền trả phòng',
                    'status' => 404,
                ];
            }

            // Kiểm tra xem hợp đồng có yêu cầu gia hạn đang chờ duyệt không
            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể trả phòng',
                    'status' => 400,
                ];
            }

            // Kiểm tra xem đã có yêu cầu trả phòng chưa hủy
            $existingCheckout = $contract->checkouts()
                ->whereNull('canceled_at')
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng',
                    'status' => 400,
                ];
            }

            // Tạo mã QR cho chuyển khoản (nếu có thông tin ngân hàng)
            $qrUrl = null;
            if ($bankInfo) {
                $qrUrl = sprintf(
                    'https://qr.sepay.vn/img?acc=%s&bank=%s&amount=&des=&template=qronly',
                    urlencode($bankInfo['account_number']),
                    urlencode($bankInfo['bank_name']),
                );
            }

            // Tạo yêu cầu trả phòng mới
            $checkout = Checkout::create([
                'contract_id' => $contract->id,
                'check_out_date' => $checkOutDate,
                'bank_info' => $bankInfo,
                'qr_code_path' => $qrUrl,
                'inventory_status' => 'Chờ kiểm kê',
                'user_confirmation_status' => 'Chưa xác nhận',
                'refund_status' => 'Chờ xử lý',
                'has_left' => false,
            ]);

            // Xác định phương thức hoàn tiền
            $method = $bankInfo ? 'chuyển khoản' : 'tiền mặt';
            // Gửi thông báo trả phòng đến quản trị viên
            SendCheckoutNotification::dispatch(
                $checkout,
                'pending',
                'Yêu cầu trả phòng #' . $checkout->id,
                "Người dùng {$contract->user->name} đã tạo yêu cầu trả phòng #{$checkout->id} cho hợp đồng #{$contract->id} vào ngày {$checkOutDate} với phương thức hoàn tiền {$method}."
            );

            // Trả về dữ liệu hợp đồng và yêu cầu trả phòng
            return [
                'data' => [
                    'contract' => $contract->fresh(),
                    'checkout' => $checkout,
                ],
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi yêu cầu trả phòng: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách yêu cầu trả phòng của người dùng.
     *
     * @return \Illuminate\Support\Collection Danh sách yêu cầu trả phòng đã được định dạng
     */
    public function getCheckouts()
    {
        // Tạo query lấy danh sách yêu cầu trả phòng của người dùng hiện tại
        $query = Checkout::query()
            ->whereHas('contract', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['contract.room.motel', 'contract.room.images']) // Nạp quan hệ contract, room, motel, images
            ->orderBy('created_at', 'desc'); // Sắp xếp theo thời gian tạo giảm dần

        // Lấy và định dạng dữ liệu yêu cầu trả phòng
        $checkouts = $query->get()->map(function ($checkout) {
            return [
                'id' => $checkout->id,
                'contract_id' => $checkout->contract_id,
                'check_out_date' => $checkout->check_out_date, // Ngày dự kiến trả phòng
                'inventory_details' => $checkout->inventory_details, // Chi tiết kiểm kê
                'deduction_amount' => $checkout->deduction_amount, // Số tiền khấu trừ
                'final_refunded_amount' => $checkout->final_refunded_amount, // Số tiền hoàn cuối cùng
                'inventory_status' => $checkout->inventory_status, // Trạng thái kiểm kê
                'user_confirmation_status' => $checkout->user_confirmation_status, // Trạng thái xác nhận của người dùng
                'user_rejection_reason' => $checkout->user_rejection_reason, // Lý do từ chối của người dùng
                'canceled_at' => $checkout->canceled_at, // Thời gian hủy yêu cầu
                'has_left' => $checkout->has_left, // Trạng thái đã rời phòng
                'images' => $checkout->images, // Hình ảnh liên quan
                'note' => $checkout->note, // Ghi chú
                'bank_info' => $checkout->bank_info, // Thông tin ngân hàng
                'qr_code_path' => $checkout->qr_code_path, // Đường dẫn mã QR
                'refund_status' => $checkout->refund_status, // Trạng thái hoàn tiền
                'room_name' => $checkout->contract->room->name, // Tên phòng
                'motel_name' => $checkout->contract->room->motel->name, // Tên nhà trọ
                'motel_slug' => $checkout->contract->room->motel->slug, // Slug nhà trọ
                'room_image' => $checkout->contract->room->main_image->image_url, // URL hình ảnh chính của phòng
                'contract' => [
                    'deposit_amount' => $checkout->contract->deposit_amount, // Số tiền cọc của hợp đồng
                ],
            ];
        });

        return $checkouts->values();
    }

    /**
     * Hủy yêu cầu trả phòng theo ID.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @return Checkout Mô hình yêu cầu trả phòng đã được hủy
     */
    public function cancelCheckout($id)
    {
        // Tìm yêu cầu trả phòng theo ID
        $checkout = Checkout::findOrFail($id);

        // Cập nhật thời gian hủy
        $checkout->update([
            'canceled_at' => now(),
        ]);

        // Gửi thông báo hủy yêu cầu trả phòng đến quản trị viên
        SendCheckoutNotification::dispatch(
            $checkout,
            'canceled',
            'Yêu cầu trả phòng #' . $checkout->id . ' đã bị hủy',
            "Người dùng {$checkout->contract->user->name} đã hủy yêu cầu trả phòng #{$checkout->id} cho hợp đồng #{$checkout->contract->id}."
        );

        return $checkout;
    }

    /**
     * Xác nhận hoặc từ chối kết quả kiểm kê trả phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @param string $status Trạng thái xác nhận (Đồng ý, Từ chối)
     * @param string|null $userRejectionReason Lý do từ chối (nếu có)
     * @return Checkout Mô hình yêu cầu trả phòng đã được cập nhật
     */
    public function confirmCheckout($id, string $status, ?string $userRejectionReason = null)
    {
        // Tìm yêu cầu trả phòng theo ID
        $checkout = Checkout::findOrFail($id);

        // Cập nhật trạng thái xác nhận và lý do từ chối (nếu có)
        $checkout->update([
            'user_confirmation_status' => $status,
            'user_rejection_reason' => $userRejectionReason,
        ]);

        // Xác định hành động và tiêu đề, nội dung thông báo
        $action = $status === 'Đồng ý' ? 'confirm' : 'reject';
        $title = $action === 'confirm'
            ? "Kết quả kiểm kê trả phòng #{$checkout->id} đã được người dùng đồng ý"
            : "Kết quả kiểm kê #{$checkout->id} bị người dùng từ chối";
        $body = $action === 'confirm'
            ? "Kết quả kiểm kê trả phòng #{$checkout->id} (Hợp đồng: #{$checkout->contract->id}) đã được người dùng {$checkout->contract->user->name} xác nhận đồng ý."
            : "Kết quả kiểm kê trả phòng #{$checkout->id} (Hợp đồng: #{$checkout->contract_id}) bị người dùng {$checkout->contract->user->name} từ chối. Lý do: " . ($userRejectionReason ?? 'Không cung cấp') . ".";

        // Gửi thông báo xác nhận/từ chối đến quản trị viên
        SendCheckoutNotification::dispatch($checkout, $action, $title, $body);

        return $checkout;
    }

    /**
     * Xác nhận người dùng đã rời phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @return Checkout Mô hình yêu cầu trả phòng đã được cập nhật
     */
    public function confirmLeftRoom($id)
    {
        // Tìm yêu cầu trả phòng theo ID
        $checkout = Checkout::findOrFail($id);

        // Kiểm tra xem người dùng đã đồng ý với kết quả kiểm kê chưa
        if ($checkout->user_confirmation_status !== 'Đồng ý') {
            throw new \Exception('Không thể xác nhận rời phòng khi chưa đồng ý với kết quả kiểm kê.');
        }

        // Cập nhật trạng thái đã rời phòng
        $checkout->update(['has_left' => true]);

        // Cập nhật trạng thái của các ContractTenant liên kết với hợp đồng
        ContractTenant::where('contract_id', $checkout->contract_id)
            ->where('status', 'Đang ở')
            ->update(['status' => 'Đã rời đi']);

        // Gửi thông báo xác nhận rời phòng đến quản trị viên
        SendCheckoutNotification::dispatch(
            $checkout,
            'left-room',
            "Người dùng đã xác nhận rời phòng #{$checkout->id}",
            "Người dùng {$checkout->contract->user->name} đã xác nhận rời phòng #{$checkout->id} cho hợp đồng #{$checkout->contract->id}."
        );

        return $checkout;
    }

    /**
     * Cập nhật thông tin ngân hàng cho yêu cầu trả phòng.
     *
     * @param int $id ID của yêu cầu trả phòng
     * @param array|null $bankInfo Thông tin ngân hàng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function updateBankInfo(int $id, ?array $bankInfo): array
    {
        try {
            // Tìm yêu cầu trả phòng của người dùng với trạng thái hoàn tiền "Chờ xử lý"
            $checkout = Checkout::query()
                ->where('id', $id)
                ->where('refund_status', 'Chờ xử lý')
                ->whereHas('contract', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if (!$checkout) {
                return [
                    'error' => 'Không tìm thấy yêu cầu trả phòng hoặc bạn không có quyền chỉnh sửa.',
                    'status' => 404,
                ];
            }

            // Cập nhật thông tin ngân hàng
            $checkout->update(['bank_info' => $bankInfo]);

            // Gửi thông báo cập nhật thông tin ngân hàng đến quản trị viên
            SendCheckoutNotification::dispatch(
                $checkout,
                'update-bank',
                "Thông tin hoàn tiền yêu cầu trả phòng #{$checkout->id} đã được cập nhật",
                "Người dùng {$checkout->contract->user->name} đã cập nhật thông tin hoàn tiền cho yêu cầu trả phòng #{$checkout->id}."
            );

            // Trả về dữ liệu yêu cầu trả phòng đã cập nhật
            return [
                'data' => [
                    'checkout' => $checkout,
                ],
                'message' => 'Cập nhật thông tin hoàn tiền thành công',
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi chỉnh sửa thông tin hoàn tiền', [
                'checkout_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
