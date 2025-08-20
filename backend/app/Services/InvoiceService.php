<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Contract;
use App\Models\User;
use App\Models\Room;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Jobs\SendInvoiceStatusUpdatedNotification;

class InvoiceService
{
    /**
     * Lấy danh sách hóa đơn với filter và pagination
     */
    public function getAllInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::with(['contract.user', 'contract.room', 'meterReading'])
            ->select('id', 'contract_id', 'meter_reading_id', 'code', 'total_amount', 'status', 'month', 'year', 'created_at', 'refunded_at');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
            $searchTerm = $filters['search'];

            // Search by invoice code
            $q->where('code', 'like', '%' . $searchTerm . '%');

            // Search by contract ID
            $q->orWhereHas('contract', function ($contractQuery) use ($searchTerm) {
            // If query is exactly "hd" or "HD", show all contracts
            if (strtolower($searchTerm) === 'hd') {
            // No additional filtering - show all contracts
            return;
            }

            // If query starts with HD or hd, extract the numeric part
            $numericQuery = $searchTerm;
            if (preg_match('/^hd(\d+)$/i', $searchTerm, $matches)) {
            $numericQuery = $matches[1];
            }

            $contractQuery->where('id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id', 'like', '%' . $numericQuery . '%');
            });

            // Search by user name
            $q->orWhereHas('contract.user', function ($userQuery) use ($searchTerm) {
            $userQuery->where('name', 'like', '%' . $searchTerm . '%');
            });
            });
        }

        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sắp xếp để hóa đơn "Chưa trả" hiển thị đầu tiên
        $query->orderByRaw("CASE WHEN status = 'Chưa trả' THEN 0 ELSE 1 END")
              ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết hóa đơn theo ID
     */
    public function getInvoiceById(int $id): ?Invoice
    {
        return Invoice::with([
            'contract.user',
            'contract.room.motel',
            'meterReading'
        ])->find($id);
    }

    /**
     * Lấy danh sách trạng thái hóa đơn
     */
    public function getStatuses(): array
    {
        return [
            'Chưa trả' => 'Chưa trả',
            'Đã trả' => 'Đã trả',
        ];
    }

    /**
     * Lấy danh sách tháng
     */
    public function getMonths(): array
    {
        return [
            1 => 'Tháng 1',
            2 => 'Tháng 2',
            3 => 'Tháng 3',
            4 => 'Tháng 4',
            5 => 'Tháng 5',
            6 => 'Tháng 6',
            7 => 'Tháng 7',
            8 => 'Tháng 8',
            9 => 'Tháng 9',
            10 => 'Tháng 10',
            11 => 'Tháng 11',
            12 => 'Tháng 12'
        ];
    }

    public function getYears(): array
    {
        $years = Invoice::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        return array_combine($years, $years);
    }

    public function getInvoiceStats(array $filters = []): array
    {
        $query = Invoice::query();

        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
            $searchTerm = $filters['search'];

            // Search by invoice code
            $q->where('code', 'like', '%' . $searchTerm . '%');

            // Search by contract ID
            $q->orWhereHas('contract', function ($contractQuery) use ($searchTerm) {
            // If query is exactly "hd" or "HD", show all contracts
            if (strtolower($searchTerm) === 'hd') {
            // No additional filtering - show all contracts
            return;
            }

            // If query starts with HD or hd, extract the numeric part
            $numericQuery = $searchTerm;
            if (preg_match('/^hd(\d+)$/i', $searchTerm, $matches)) {
            $numericQuery = $matches[1];
            }

            $contractQuery->where('id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id', 'like', '%' . $numericQuery . '%');
            });

            // Search by user name
            $q->orWhereHas('contract.user', function ($userQuery) use ($searchTerm) {
            $userQuery->where('name', 'like', '%' . $searchTerm . '%');
            });
            });
        }

        $invoices = $query->get(['status', 'total_amount']);

        $paidInvoices = $invoices->where('status', 'Đã trả');
        $unpaidInvoices = $invoices->where('status', 'Chưa trả');
        $refundedInvoices = $invoices->where('status', 'Đã hoàn tiền');
        $canceledInvoices = $invoices->where('status', 'Đã hủy');

        return [
            'total' => $invoices->count(),
            'paid' => $paidInvoices->count(),
            'unpaid' => $unpaidInvoices->count(),
            'refunded' => $refundedInvoices->count(),
            'canceled' => $canceledInvoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $paidInvoices->sum('total_amount'),
            'unpaid_amount' => $unpaidInvoices->sum('total_amount'),
            'refunded_amount' => $refundedInvoices->sum('total_amount')
        ];
    }

    public function updateInvoiceStatus(int $id, string $newStatus, Request $request): void
    {
        // Lấy hóa đơn
        $invoice = $this->getInvoiceById($id);

        if (!$invoice) {
            throw new \Exception('Không tìm thấy hóa đơn', 404);
        }

        // Kiểm tra trạng thái hiện tại
        if ($invoice->status === 'Đã trả') {
            throw new \Exception('Hóa đơn đã ở trạng thái Đã trả', 400);
        }

        // Lưu trạng thái cũ trước khi cập nhật
        $oldStatus = $invoice->status;

        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::transaction(function () use ($invoice, $newStatus, $request, $oldStatus) {
            // Cập nhật trạng thái hóa đơn
            $invoice->status = $newStatus;
            $invoice->save();

            // Kiểm tra nếu hóa đơn có type là "đặt cọc" và trạng thái mới là "Đã trả"
            if ($invoice->type === 'Đặt cọc' && $newStatus === 'Đã trả') {
                // Cập nhật trạng thái hợp đồng sang "Hoạt động"
                $contract = Contract::find($invoice->contract_id);
                if ($contract) {
                    $contract->status = 'Hoạt động';
                    $contract->save();

                    // Cập nhật vai trò người dùng thành "Người thuê"
                    $user = User::find($contract->user_id);
                    if ($user) {
                        $user->role = 'Người thuê';
                        $user->save();
                    }
                    // Cập nhật trạng thái phòng
                    $room = Room::find($contract->room_id);
                    if ($room) {
                        $room->status = 'Đã thuê';
                        $room->save();
                    }
                }
            }

            // Tạo giao dịch nếu trạng thái là "Đã trả"
            if ($newStatus === 'Đã trả') {
                Transaction::create([
                    'invoice_id' => $invoice->id,
                    'reference_code' => $request->input('reference_code', 'INV-' . $invoice->code . '-' . now()->format('YmdHis')),
                    'transfer_amount' => $invoice->total_amount,
                    'content' => 'Thanh toán hóa đơn ' . ($invoice->code ?? 'N/A') . ' cho phòng ' . ($invoice->contract->room->name ?? 'N/A'),
                    'transfer_type' => 'in',
                    'transaction_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Dispatch job để gửi email và notification
            SendInvoiceStatusUpdatedNotification::dispatch($invoice, $oldStatus, $newStatus);

            // Ghi log hành động
            Log::info('Invoice status updated and notification job dispatched', [
                'invoice_id' => $invoice->id,
                'old_status' => $oldStatus ?? 'null',
                'new_status' => $newStatus,
                'reference_code' => $request->input('reference_code', 'INV-' . $invoice->code . '-' . now()->format('YmdHis')),
                'notification_job_dispatched' => true
            ]);
        });
    }
}
