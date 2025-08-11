<?php

namespace App\Services;

use App\Models\Invoice;
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

    // Tìm kiếm theo code hóa đơn
    if (!empty($filters['search'])) {
        $query->where('code', 'like', '%' . $filters['search'] . '%');
    }

    // Lọc theo tháng (chỉ khi user chọn tháng cụ thể)
    if (!empty($filters['month'])) {
        $query->where('month', $filters['month']);
    }

    // Lọc theo năm (chỉ khi user chọn năm cụ thể)
    if (!empty($filters['year'])) {
        $query->where('year', $filters['year']);
    }

    // Lọc theo trạng thái
    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    // Sắp xếp
    $query->orderBy('created_at', 'desc');

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

        // Áp dụng filter tháng nếu có (chỉ khi user chọn tháng cụ thể)
        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        // Áp dụng filter năm nếu có (chỉ khi user chọn năm cụ thể)
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        // Nếu không có filter tháng và năm → lấy tất cả hóa đơn
        // Nếu có filter tháng/năm → lấy theo filter đó
        // Không có default filter tháng/năm hiện tại nữa

        // Thêm filter theo trạng thái nếu có
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Thêm filter tìm kiếm nếu có
        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
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
            // Cập nhật trạng thái
            $invoice->status = $newStatus;
            $invoice->save();

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
