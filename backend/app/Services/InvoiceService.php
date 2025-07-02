<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceService
{
    /**
     * Lấy danh sách hóa đơn với filter và pagination
     */
    public function getAllInvoices(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::with(['contract.user', 'contract.room', 'meterReading'])
            ->select('id', 'contract_id', 'meter_reading_id', 'code', 'total_amount', 'status', 'month', 'year', 'created_at');

        // Tìm kiếm theo code hóa đơn
        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        // Lọc theo tháng
        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        // Lọc theo năm
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
            'Quá hạn' => 'Quá hạn',
            'Đã hủy' => 'Đã hủy'
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

    public function getInvoiceStats(): array
    {
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::where('status', 'Đã trả')->count();
        $unpaidInvoices = Invoice::where('status', 'Chưa trả')->count();
        $overdueInvoices = Invoice::where('status', 'Đã hoàn tiền')->count();

        return [
            'total' => $totalInvoices,
            'paid' => $paidInvoices,
            'unpaid' => $unpaidInvoices,
            'overdue' => $overdueInvoices
        ];
    }
}
