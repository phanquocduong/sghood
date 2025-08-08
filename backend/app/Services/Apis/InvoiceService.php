<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{
    /**
     * Lấy danh sách tháng và năm duy nhất từ hóa đơn của user
     *
     * @return array
     * @throws \Exception
     */
    public function getInvoiceMonthsAndYears(): array
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            $results = Invoice::whereHas('contract', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->select(['month', 'year'])
                ->distinct()
                ->get();

            $months = $results->pluck('month')->unique()->values()->toArray();
            $years = $results->pluck('year')->unique()->values()->toArray();

            return [
                'months' => $months,
                'years' => $years
            ];
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi lấy danh sách tháng và năm: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách hóa đơn của user hiện tại với bộ lọc và phân trang
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function getUserInvoices(array $filters = [], $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            $query = Invoice::whereHas('contract', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->with([
                    'contract' => function ($query) {
                        $query->select('id', 'room_id', 'user_id', 'booking_id', 'start_date', 'end_date', 'deposit_amount');
                    },
                    'contract.room' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'contract.user' => function ($query) {
                        $query->select('id', 'name', 'email');
                    },
                    'meterReading'
                ])
                ->select([
                    'id',
                    'code',
                    'contract_id',
                    'type',
                    'month',
                    'year',
                    'electricity_fee',
                    'water_fee',
                    'parking_fee',
                    'junk_fee',
                    'internet_fee',
                    'service_fee',
                    'total_amount',
                    'status',
                    'refunded_at',
                    'created_at'
                ]);

            // Xử lý lọc theo type
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            // Xử lý lọc theo month
            if (!empty($filters['month'])) {
                $query->whereRaw('COALESCE(month, MONTH(created_at)) = ?', [$filters['month']]);
            }

            // Xử lý lọc theo year
            if (!empty($filters['year'])) {
                $query->whereRaw('COALESCE(year, YEAR(created_at)) = ?', [$filters['year']]);
            }

            // Xử lý sắp xếp
            if (isset($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'latest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            }

            // Sử dụng paginate thay vì get
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi lấy danh sách hóa đơn: ' . $e->getMessage());
        }
    }

    public function getInvoiceById(string $code): Invoice
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            $invoice = Invoice::where('code', $code)
                ->whereHas('contract', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with([
                    'contract' => function ($query) {
                        $query->select('id', 'room_id', 'user_id', 'booking_id', 'start_date', 'end_date', 'deposit_amount', 'rental_price');
                    },
                    'contract.room' => function ($query) {
                        $query->select('id', 'name', 'motel_id');
                    },
                    'contract.room.motel' => function ($query) {
                        $query->select('id', 'electricity_fee', 'water_fee', 'parking_fee', 'junk_fee', 'internet_fee', 'service_fee');
                    },
                    'contract.user' => function ($query) {
                        $query->select('id', 'name', 'email', 'phone');
                    },
                    'meterReading' => function ($query) {
                        $query->select('id', 'room_id', 'month', 'year', 'electricity_kwh', 'water_m3');
                    }
                ])
                ->select([
                    'id',
                    'code',
                    'contract_id',
                    'meter_reading_id',
                    'type',
                    'month',
                    'year',
                    'room_fee',
                    'electricity_fee',
                    'water_fee',
                    'parking_fee',
                    'junk_fee',
                    'internet_fee',
                    'service_fee',
                    'total_amount',
                    'status',
                    'refunded_at',
                    'created_at'
                ])
                ->firstOrFail();

            // Gán chỉ số tháng trước vào thuộc tính tạm thời trên $invoice
            if ($invoice->meterReading && $invoice->type === 'Hàng tháng') {
                $prevReading = $this->getPreviousMeterReading($invoice->contract->room_id, $invoice->month, $invoice->year);
                $invoice->prev_electricity_kwh = $prevReading ? $prevReading->electricity_kwh : 0;
                $invoice->prev_water_m3 = $prevReading ? $prevReading->water_m3 : 0;
            } else {
                $invoice->prev_electricity_kwh = 0;
                $invoice->prev_water_m3 = 0;
            }

            return $invoice;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.');
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi lấy chi tiết hóa đơn: ' . $e->getMessage());
        }
    }

    /**
     * Lấy chỉ số đồng hồ gần nhất phía trước theo thời gian
     *
     * @param int $roomId
     * @param int $month
     * @param int $year
     * @return MeterReading|null
     */
    private function getPreviousMeterReading(int $roomId, int $month, int $year): ?MeterReading
    {
        // Tính ngày đầu tháng của hóa đơn hiện tại
        $currentMonthDate = now()->setDate($year, $month, 1)->startOfDay();

        return MeterReading::where('room_id', $roomId)
            ->where('created_at', '<', $currentMonthDate)
            ->orderByDesc('created_at') // Lấy bản ghi gần nhất phía trước
            ->first();
    }

    public function createDepositInvoice(Contract $contract): Invoice
    {
        $code = 'INV' . $contract->id . now()->format('YmdHis');
        return Invoice::create([
            'contract_id' => $contract->id,
            'type' => 'Đặt cọc',
            'month' => now()->month,
            'year' => now()->year,
            'total_amount' => $contract->deposit_amount,
            'status' => 'Chưa trả',
            'code' => $code,
        ]);
    }

    // Trong InvoiceService.php
    public function checkStatus(string $code, int $userId): array
    {
        $invoice = Invoice::where('code', $code)
            ->whereHas('contract', fn($query) => $query->where('user_id', $userId))
            ->select('status', 'type')
            ->firstOrFail();

        return [
            'status' => $invoice->status,
            'type' => $invoice->type
        ];
    }

    public function processWebhook(object $data): Invoice
    {
        if (!$data->code) {
            throw new \Exception('Invalid invoice number format');
        }

        $invoice = Invoice::where('code', $data->code)
            ->where('status', 'Chưa trả')
            ->firstOrFail();

        if ($invoice->total_amount != $data->transferAmount) {
            throw new \Exception('Amount mismatch');
        }

        $transaction = Transaction::create([
            'id' => $data->id,
            'invoice_id' => $invoice->id,
            'transaction_date' => $data->transactionDate,
            'content' => $data->content,
            'transfer_type' => $data->transferType,
            'transfer_amount' => $data->transferAmount,
            'reference_code' => $data->referenceCode,
        ]);

        $invoice->update(['status' => 'Đã trả']);

        if ($invoice->type === 'Đặt cọc') {
            $invoice->contract->update(['status' => 'Hoạt động']);
            $invoice->contract->room->update(['status' => 'Đã thuê']);
            $invoice->contract->user->update(['role' => 'Người thuê']);
        }

        Log::info('Thanh toán thành công', ['invoice_id' => $invoice->id, 'transaction_id' => $transaction]);
        return $invoice;
    }
}
