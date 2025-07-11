<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\Invoice;
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
     * Lấy danh sách hóa đơn của user hiện tại với bộ lọc và sắp xếp
     *
     * @param array $filters
     * @return Collection
     * @throws \Exception
     */
    public function getUserInvoices(array $filters = []): Collection
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

            return $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi lấy danh sách hóa đơn: ' . $e->getMessage());
        }
    }

    /**
     * Lấy chi tiết một hóa đơn theo ID
     *
     * @param string $code
     * @return Invoice
     * @throws \Exception
     */
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
                        $query->select('id', 'room_id', 'user_id', 'booking_id', 'start_date', 'end_date', 'deposit_amount');
                    },
                    'contract.room' => function ($query) {
                        $query->select('id', 'name'); // Giả sử model Room có trường 'name'
                    },
                    'contract.user' => function ($query) {
                        $query->select('id', 'name', 'email', 'phone'); // Giả sử model User có trường 'phone'
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
                    'created_at'
                ])
                ->firstOrFail();

            return $invoice;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.');
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi lấy chi tiết hóa đơn: ' . $e->getMessage());
        }
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
