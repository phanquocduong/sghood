<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến hóa đơn.
 */
class InvoiceService
{
    /**
     * Lấy danh sách tháng và năm duy nhất từ các hóa đơn của người dùng.
     *
     * @return array Mảng chứa danh sách tháng và năm
     * @throws \Exception Nếu không tìm thấy người dùng hoặc xảy ra lỗi
     */
    public function getInvoiceMonthsAndYears(): array
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();

            // Kiểm tra xem người dùng có đăng nhập hay không
            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            // Truy vấn các hóa đơn của người dùng, lấy tháng và năm duy nhất
            $results = Invoice::whereHas('contract', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->select(['month', 'year'])
                ->distinct()
                ->get();

            // Tách danh sách tháng và năm
            $months = $results->pluck('month')->unique()->values()->toArray();
            $years = $results->pluck('year')->unique()->values()->toArray();

            // Trả về mảng chứa danh sách tháng và năm
            return [
                'months' => $months,
                'years' => $years
            ];
        } catch (\Exception $e) {
            // Ném ngoại lệ nếu có lỗi xảy ra
            throw new \Exception('Lỗi khi lấy danh sách tháng và năm: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách hóa đơn của người dùng với bộ lọc và phân trang.
     *
     * @param array $filters Các tham số lọc (sắp xếp, loại, tháng, năm)
     * @param int $perPage Số lượng hóa đơn mỗi trang
     * @return \Illuminate\Pagination\LengthAwarePaginator Danh sách hóa đơn phân trang
     * @throws \Exception Nếu không tìm thấy người dùng hoặc xảy ra lỗi
     */
    public function getUserInvoices(array $filters = [], $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();

            // Kiểm tra xem người dùng có đăng nhập hay không
            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            // Xây dựng truy vấn lấy hóa đơn của người dùng
            $query = Invoice::whereHas('contract', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->with([
                    'contract' => function ($query) {
                        // Lấy thông tin hợp đồng liên quan
                        $query->select('id', 'room_id', 'user_id', 'booking_id', 'start_date', 'end_date', 'deposit_amount');
                    },
                    'contract.room' => function ($query) {
                        // Lấy thông tin phòng liên quan
                        $query->select('id', 'name');
                    },
                    'contract.user' => function ($query) {
                        // Lấy thông tin người dùng liên quan
                        $query->select('id', 'name', 'email');
                    },
                    'meterReading' => function ($query) {
                        // Lấy thông tin chỉ số đồng hồ liên quan
                        $query->select('id', 'room_id', 'month', 'year', 'electricity_kwh', 'water_m3');
                    }
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

            // Áp dụng bộ lọc theo loại hóa đơn
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            // Áp dụng bộ lọc theo tháng
            if (!empty($filters['month'])) {
                $query->whereRaw('COALESCE(month, MONTH(created_at)) = ?', [$filters['month']]);
            }

            // Áp dụng bộ lọc theo năm
            if (!empty($filters['year'])) {
                $query->whereRaw('COALESCE(year, YEAR(created_at)) = ?', [$filters['year']]);
            }

            // Áp dụng sắp xếp
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

            // Trả về danh sách hóa đơn phân trang
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            // Ném ngoại lệ nếu có lỗi xảy ra
            throw new \Exception('Lỗi khi lấy danh sách hóa đơn: ' . $e->getMessage());
        }
    }

    /**
     * Lấy chi tiết một hóa đơn theo mã hóa đơn.
     *
     * @param string $code Mã hóa đơn
     * @return Invoice Mô hình hóa đơn
     * @throws \Exception Nếu hóa đơn không tồn tại hoặc người dùng không có quyền
     */
    public function getInvoiceById(string $code): Invoice
    {
        try {
            // Lấy ID người dùng đang đăng nhập
            $userId = Auth::id();

            // Kiểm tra xem người dùng có đăng nhập hay không
            if (!$userId) {
                throw new \Exception('Không tìm thấy người dùng đang đăng nhập.');
            }

            // Truy vấn hóa đơn theo mã và đảm bảo thuộc về người dùng
            $invoice = Invoice::where('code', $code)
                ->whereHas('contract', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->with([
                    'contract' => function ($query) {
                        // Lấy thông tin hợp đồng liên quan
                        $query->select('id', 'room_id', 'user_id', 'booking_id', 'start_date', 'end_date', 'deposit_amount', 'rental_price');
                    },
                    'contract.room' => function ($query) {
                        // Lấy thông tin phòng liên quan
                        $query->select('id', 'name', 'motel_id');
                    },
                    'contract.room.motel' => function ($query) {
                        // Lấy thông tin nhà trọ liên quan
                        $query->select('id', 'electricity_fee', 'water_fee', 'parking_fee', 'junk_fee', 'internet_fee', 'service_fee');
                    },
                    'contract.user' => function ($query) {
                        // Lấy thông tin người dùng liên quan
                        $query->select('id', 'name', 'email', 'phone');
                    },
                    'meterReading' => function ($query) {
                        // Lấy thông tin chỉ số đồng hồ liên quan
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

            // Nếu hóa đơn là loại hàng tháng, lấy chỉ số đồng hồ trước đó
            if ($invoice->meterReading && $invoice->type === 'Hàng tháng') {
                $prevReading = $this->getPreviousMeterReading($invoice->contract->room_id, $invoice->month, $invoice->year);
                $invoice->prev_electricity_kwh = $prevReading ? $prevReading->electricity_kwh : 0;
                $invoice->prev_water_m3 = $prevReading ? $prevReading->water_m3 : 0;
            } else {
                $invoice->prev_electricity_kwh = 0;
                $invoice->prev_water_m3 = 0;
            }

            // Trả về mô hình hóa đơn
            return $invoice;
        } catch (ModelNotFoundException $e) {
            // Ném ngoại lệ nếu hóa đơn không tồn tại hoặc người dùng không có quyền
            throw new \Exception('Hóa đơn không tồn tại hoặc bạn không có quyền truy cập.');
        } catch (\Exception $e) {
            // Ném ngoại lệ nếu có lỗi khác
            throw new \Exception('Lỗi khi lấy chi tiết hóa đơn: ' . $e->getMessage());
        }
    }

    /**
     * Lấy chỉ số đồng hồ gần nhất trước thời điểm hóa đơn.
     *
     * @param int $roomId ID của phòng
     * @param int $month Tháng của hóa đơn
     * @param int $year Năm của hóa đơn
     * @return MeterReading|null Bản ghi chỉ số đồng hồ trước đó hoặc null
     */
    private function getPreviousMeterReading(int $roomId, int $month, int $year): ?MeterReading
    {
        // Tính ngày đầu tháng của hóa đơn hiện tại
        $currentMonthDate = now()->setDate($year, $month, 1)->startOfDay();

        // Truy vấn bản ghi chỉ số đồng hồ gần nhất trước thời điểm hiện tại
        return MeterReading::where('room_id', $roomId)
            ->where('created_at', '<', $currentMonthDate)
            ->orderByDesc('created_at') // Sắp xếp theo thời gian giảm dần
            ->first();
    }

    /**
     * Tạo hóa đơn đặt cọc cho hợp đồng.
     *
     * @param Contract $contract Mô hình hợp đồng
     * @return Invoice Mô hình hóa đơn được tạo
     */
    public function createDepositInvoice(Contract $contract): Invoice
    {
        // Tạo mã hóa đơn duy nhất
        $code = 'INV' . $contract->id . now()->format('YmdHis');

        // Tạo và trả về mô hình hóa đơn
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

    /**
     * Kiểm tra trạng thái thanh toán của một hóa đơn.
     *
     * @param string $code Mã hóa đơn
     * @param int $userId ID người dùng
     * @return array Trạng thái và loại hóa đơn
     * @throws ModelNotFoundException Nếu hóa đơn không tồn tại hoặc người dùng không có quyền
     */
    public function checkStatus(string $code, int $userId): array
    {
        // Truy vấn hóa đơn theo mã và đảm bảo thuộc về người dùng
        $invoice = Invoice::where('code', $code)
            ->whereHas('contract', fn($query) => $query->where('user_id', $userId))
            ->select('status', 'type')
            ->firstOrFail();

        // Trả về trạng thái và loại hóa đơn
        return [
            'status' => $invoice->status,
            'type' => $invoice->type
        ];
    }

    /**
     * Xử lý webhook thanh toán để cập nhật trạng thái hóa đơn.
     *
     * @param object $data Dữ liệu từ webhook
     * @return Invoice Mô hình hóa đơn đã cập nhật
     * @throws \Exception Nếu dữ liệu không hợp lệ hoặc số tiền không khớp
     */
    public function processWebhook(object $data): Invoice
    {
        // Kiểm tra mã hóa đơn
        if (!$data->code) {
            throw new \Exception('Invalid invoice number format');
        }

        // Truy vấn hóa đơn theo mã và trạng thái chưa trả
        $invoice = Invoice::where('code', $data->code)
            ->where('status', 'Chưa trả')
            ->firstOrFail();

        // Kiểm tra số tiền thanh toán
        if ($invoice->total_amount != $data->transferAmount) {
            throw new \Exception('Amount mismatch');
        }

        // Tạo giao dịch thanh toán
        $transaction = Transaction::create([
            'id' => $data->id,
            'invoice_id' => $invoice->id,
            'transaction_date' => $data->transactionDate,
            'content' => $data->content,
            'transfer_type' => $data->transferType,
            'transfer_amount' => $data->transferAmount,
            'reference_code' => $data->referenceCode,
        ]);

        // Cập nhật trạng thái hóa đơn thành đã trả
        $invoice->update(['status' => 'Đã trả']);

        // Nếu là hóa đơn đặt cọc, cập nhật trạng thái hợp đồng, phòng và vai trò người dùng
        if ($invoice->type === 'Đặt cọc') {
            $invoice->contract->update(['status' => 'Hoạt động']);
            $invoice->contract->room->update(['status' => 'Đã thuê']);
            $invoice->contract->user->update(['role' => 'Người thuê']);
        }

        // Trả về mô hình hóa đơn đã cập nhật
        return $invoice;
    }
}
