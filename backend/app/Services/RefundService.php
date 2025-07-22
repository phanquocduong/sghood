<?php

namespace App\Services;

use App\Models\RefundRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;

class RefundService
{
    /**
     * Lấy danh sách yêu cầu hoàn tiền với tìm kiếm, lọc và sắp xếp
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRefunds(Request $request)
    {
        $querySearch = $request->input('querySearch');
        $status = $request->input('status');
        $sort = $request->input('sort', 'desc');

        $query = RefundRequest::query()->with(['checkout.contract.room']);

        if ($querySearch) {
            $query->where(function ($q) use ($querySearch) {
                $q->whereHas('checkout.contract.room', function ($q) use ($querySearch) {
                    $q->where('name', 'like', '%' . $querySearch . '%');
                })->orWhere('bank_info->account_holder', 'like', '%' . $querySearch . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', $sort);

        return $query->paginate(10);
    }

    /**
     * Xác nhận yêu cầu hoàn tiền và cập nhật qr_code_path, tạo transaction
     *
     * @param int $id
     * @param Request $request
     * @return RefundRequest
     * @throws \Exception
     */
    public function confirmRefund($id, Request $request)
    {
        $refund = RefundRequest::with(['checkout.contract.room'])->findOrFail($id);

        // Kiểm tra trạng thái hợp lệ
        if ($refund->status !== 'Chờ xử lý' || $refund->checkout->user_confirmation_status !== 'Đồng ý') {
            throw new \Exception('Yêu cầu hoàn tiền không hợp lệ để xác nhận.');
        }

        // Cập nhật trạng thái
        $refund->status = 'Đã xử lý';

        // Tạo và lưu URL mã QR
        $qrCodeUrl = $this->generateQrCodeUrl($refund);
        $refund->qr_code_path = $qrCodeUrl;

        // Lưu thay đổi trước khi tạo transaction
        $refund->save();

        // Tạo transaction với mã tham chiếu và số tiền từ request
        $this->createTransaction($refund, $request->input('reference_code'), $request->input('transfer_amount'));

        return $refund;
    }

    /**
     * Tạo URL mã QR cho Sepay
     *
     * @param RefundRequest $refund
     * @return string
     */
    protected function generateQrCodeUrl(RefundRequest $refund)
    {
        $bankInfo = $refund->bank_info ?? [
            'account_number' => '123',
            'bank_name' => '123',
            'account_holder' => 'Không xác định',
        ];
        $accountNumber = $bankInfo['account_number'] ?? '';
        $bankName = $bankInfo['bank_name'] ?? '';
        $amount = $refund->checkout->final_refunded_amount ?? 0;
        $roomName = $refund->checkout->contract->room->name ?? '';
        $description = urlencode("Hoan tien phong {$roomName}");

        return "https://qr.sepay.vn/img?acc={$accountNumber}&bank={$bankName}&amount={$amount}&des={$description}&template=compact";
    }

    /**
     * Tạo bản ghi transaction cho yêu cầu hoàn tiền
     *
     * @param RefundRequest $refund
     * @param string $referenceCode
     * @param float $transferAmount
     * @return void
     */
    protected function createTransaction(RefundRequest $refund, $referenceCode, $transferAmount)
    {
        Transaction::create([
            'reference_code' => $referenceCode,
            'transfer_amount' => $transferAmount,
            'content' => 'Hoàn tiền cho phòng ' . ($refund->checkout->contract->room->name ?? 'N/A'),
            'transfer_type' => 'out',
            'refund_request_id' => $refund->id,
        ]);
    }
}
