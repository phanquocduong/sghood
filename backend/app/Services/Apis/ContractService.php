<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendContractNotification;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến hợp đồng.
 */
class ContractService
{
    /**
     * Khởi tạo dịch vụ với ContractPdfService.
     *
     * @param ContractPdfService $contractPdfService Dịch vụ xử lý PDF hợp đồng
     */
    public function __construct(
        private readonly ContractPdfService $contractPdfService
    ) {}

    /**
     * Lấy danh sách hợp đồng của người dùng hiện tại.
     *
     * @return array Danh sách hợp đồng đã được định dạng
     */
    public function getUserContracts(): array
    {
        try {
            // Tạo query lấy danh sách hợp đồng của người dùng hiện tại
            return Contract::query()
                ->where('user_id', Auth::id())
                ->select('id', 'room_id', 'start_date', 'end_date', 'status', 'deposit_amount', 'rental_price', 'signed_at', 'early_terminated_at', 'created_at')
                ->with([
                    'room' => fn($query) => $query->select('id', 'name', 'motel_id', 'price')
                        ->with(['motel' => fn($query) => $query->select('id', 'name', 'slug')]), // Nạp thông tin phòng và nhà trọ
                    'invoices' => fn($query) => $query->select('id', 'contract_id')
                        ->where('type', 'Đặt cọc'), // Nạp hóa đơn đặt cọc
                    'extensions' => fn($query) => $query->select('id', 'contract_id', 'status')
                        ->latest(), // Nạp yêu cầu gia hạn mới nhất
                    'checkouts' => fn($query) => $query->select(
                        'id',
                        'contract_id',
                        'check_out_date',
                        'canceled_at',
                        'has_left'
                    )->latest(), // Nạp yêu cầu trả phòng mới nhất
                ])
                ->orderBy('created_at', 'desc') // Sắp xếp theo thời gian tạo giảm dần
                ->get()
                ->map(fn (Contract $contract) => [
                    'id' => $contract->id,
                    'room_name' => $contract->room->name, // Tên phòng
                    'room_price' => $contract->room->price, // Giá phòng
                    'motel_name' => $contract->room->motel->name, // Tên nhà trọ
                    'motel_slug' => $contract->room->motel->slug, // Slug nhà trọ
                    'room_image' => $contract->room->main_image->image_url, // URL hình ảnh chính của phòng
                    'start_date' => $contract->start_date->toDateString(), // Ngày bắt đầu hợp đồng
                    'end_date' => $contract->end_date->toDateString(), // Ngày kết thúc hợp đồng
                    'status' => $contract->status, // Trạng thái hợp đồng
                    'deposit_amount' => $contract->deposit_amount, // Số tiền cọc
                    'rental_price' => $contract->rental_price, // Giá thuê hàng tháng
                    'signed_at' => $contract->signed_at?->toDateTimeString(), // Thời gian ký hợp đồng
                    'early_terminated_at' => $contract->early_terminated_at?->toDateTimeString(), // Thời gian kết thúc sớm
                    'invoice_id' => $contract->invoices->first()?->id, // ID hóa đơn đặt cọc
                    'latest_extension_status' => $contract->extensions->first()?->status, // Trạng thái yêu cầu gia hạn mới nhất
                    'has_checkout' => $contract->checkouts->first()?->id, // ID yêu cầu trả phòng
                    'latest_checkout_status' => $contract->checkouts->first()?->canceled_at, // Trạng thái yêu cầu trả phòng
                ])
                ->toArray();
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy danh sách hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy chi tiết hợp đồng theo ID.
     *
     * @param int $id ID của hợp đồng
     * @return array Chi tiết hợp đồng hoặc thông báo lỗi
     */
    public function getContractDetail(int $id): array
    {
        try {
            // Tìm hợp đồng của người dùng hiện tại
            $contract = Contract::query()
                ->with(['user', 'extensions']) // Nạp thông tin người dùng và yêu cầu gia hạn
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            // Kiểm tra xem hợp đồng có tồn tại không
            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền truy cập',
                    'status' => 404,
                ];
            }

            // Trả về chi tiết hợp đồng đã được định dạng
            return [
                'id' => $contract->id,
                'room_id' => $contract->room_id, // ID phòng
                'user_id' => $contract->user_id, // ID người dùng
                'booking_id' => $contract->booking_id, // ID đặt phòng
                'start_date' => $contract->start_date->toIso8601String(), // Ngày bắt đầu hợp đồng
                'end_date' => $contract->end_date->toIso8601String(), // Ngày kết thúc hợp đồng
                'rental_price' => $contract->rental_price, // Giá thuê hàng tháng
                'deposit_amount' => $contract->deposit_amount, // Số tiền cọc
                'content' => $contract->content, // Nội dung hợp đồng
                'signature' => $contract->signature, // Chữ ký hợp đồng
                'status' => $contract->status, // Trạng thái hợp đồng
                'file' => $contract->file ? url($contract->file) : null, // URL file hợp đồng
                'signed_at' => $contract->signed_at?->toDateTimeString(), // Thời gian ký hợp đồng
                'user_phone' => $contract->user->phone, // Số điện thoại người dùng
                'active_extensions' => $contract->extensions
                    ->filter(fn($ext) => $ext->status === 'Hoạt động') // Lọc các yêu cầu gia hạn đang hoạt động
                    ->map(fn($ext) => [
                        'id' => $ext->id,
                        'new_end_date' => $ext->new_end_date->toIso8601String(), // Ngày kết thúc mới
                        'new_rental_price' => $ext->new_rental_price, // Giá thuê mới
                        'content' => $ext->content, // Nội dung gia hạn
                        'status' => $ext->status, // Trạng thái gia hạn
                    ])->values()->toArray(),
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi lấy chi tiết hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hủy hợp đồng theo ID.
     *
     * @param int $id ID của hợp đồng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function cancelContract(int $id): array
    {
        try {
            // Tìm hợp đồng của người dùng hiện tại
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            // Kiểm tra xem hợp đồng có tồn tại không
            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền hủy',
                    'status' => 404,
                ];
            }

            // Kiểm tra trạng thái hợp đồng
            if ($contract->status !== 'Chờ xác nhận') {
                return [
                    'error' => 'Hợp đồng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            // Cập nhật trạng thái hợp đồng thành "Huỷ bỏ"
            $contract->update(['status' => 'Huỷ bỏ']);

            // Gửi thông báo hủy hợp đồng đến quản trị viên
            SendContractNotification::dispatch(
                $contract,
                'canceled',
                "Hợp đồng #{$contract->id} đã bị hủy",
                "Người dùng {$contract->user->name} đã hủy hợp đồng #{$contract->id}."
            );

            // Trả về dữ liệu hợp đồng đã cập nhật
            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lưu nội dung hợp đồng.
     *
     * @param string $content Nội dung hợp đồng
     * @param int $id ID của hợp đồng
     * @return Contract Mô hình hợp đồng đã được cập nhật
     */
    public function saveContract(string $content, int $id): Contract
    {
        try {
            // Tìm hợp đồng của người dùng hiện tại
            $contract = Contract::query()
                ->where('user_id', Auth::id())
                ->where('id', $id)
                ->firstOrFail();

            // Cập nhật nội dung và trạng thái hợp đồng
            $contract->update([
                'content' => $content,
                'status' => 'Chờ duyệt'
            ]);

            // Gửi thông báo cập nhật hợp đồng đến quản trị viên
            SendContractNotification::dispatch(
                $contract,
                "pending",
                "Hợp đồng #{$contract->id} đang chờ duyệt",
                "Người dùng {$contract->user->name} đã hoàn thiện thông tin cá nhân vào hợp đồng #{$contract->id} và đang chờ duyệt."
            );

            // Trả về hợp đồng đã cập nhật
            return $contract->fresh();
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi cập nhật hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ký hợp đồng.
     *
     * @param int $contractId ID của hợp đồng
     * @param string $signature Chữ ký dưới dạng base64
     * @param string $content Nội dung hợp đồng
     * @return Contract Mô hình hợp đồng đã được ký
     */
    public function signContract(int $contractId, string $signature, string $content): Contract
    {
        try {
            // Tìm hợp đồng ở trạng thái chờ ký
            $contract = Contract::query()
                ->where('user_id', Auth::id())
                ->where('id', $contractId)
                ->where('status', 'Chờ ký')
                ->firstOrFail();

            // Lưu chữ ký dưới dạng file
            $signaturePath = $this->saveSignature($signature, $contractId);

            // Cập nhật thông tin hợp đồng
            $contract->update([
                'signature' => $signaturePath,
                'content' => $content,
                'status' => 'Chờ thanh toán tiền cọc',
                'signed_at' => now(),
            ]);

            // Gửi thông báo ký hợp đồng đến quản trị viên
            SendContractNotification::dispatch(
                $contract,
                'signed',
                "Hợp đồng #{$contract->id} đã được ký",
                "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được ký và đang chờ thanh toán tiền cọc."
            );

            // Trả về hợp đồng đã cập nhật
            return $contract->fresh();
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi ký hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lưu chữ ký dưới dạng file hình ảnh.
     *
     * @param string $signature Chữ ký dưới dạng base64
     * @param int $contractId ID của hợp đồng
     * @return string Đường dẫn file chữ ký
     */
    private function saveSignature(string $signature, int $contractId): string
    {
        // Xóa phần đầu của chuỗi base64
        $signature = preg_replace('#^data:image/\w+;base64,#i', '', $signature);
        $signatureData = base64_decode($signature);

        // Tạo đường dẫn file chữ ký
        $path = "images/signatures/contract-{$contractId}-" . time() . '.png';
        // Lưu file vào disk private
        Storage::disk('private')->put($path, $signatureData);

        return $path;
    }

    /**
     * Kết thúc hợp đồng sớm.
     *
     * @param int $id ID của hợp đồng
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function earlyTermination(int $id): array
    {
        try {
            // Tìm hợp đồng đang hoạt động của người dùng
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->with(['invoices']) // Nạp thông tin hóa đơn
                ->first();

            // Kiểm tra xem hợp đồng có tồn tại không
            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền kết thúc sớm',
                    'status' => 404,
                ];
            }

            // Kiểm tra nếu hợp đồng đã hết hạn
            if ($contract->end_date <= now()) {
                return [
                    'error' => 'Hợp đồng đã hết hạn, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            // Kiểm tra yêu cầu gia hạn đang chờ duyệt
            $latestExtension = $contract->extensions()->where('status', 'Chờ duyệt')->first();
            if ($latestExtension) {
                return [
                    'error' => 'Hợp đồng đang có yêu cầu gia hạn chờ duyệt, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            // Kiểm tra yêu cầu trả phòng đang tồn tại
            $existingCheckout = $contract->checkouts()
                ->whereNull('canceled_at')
                ->first();

            if ($existingCheckout) {
                return [
                    'error' => 'Hợp đồng đã có yêu cầu trả phòng, không thể kết thúc sớm',
                    'status' => 400,
                ];
            }

            // Kiểm tra hóa đơn chưa thanh toán
            $unpaidInvoices = $contract->invoices()->where('status', '!=', 'Đã trả')->exists();
            if ($unpaidInvoices) {
                return [
                    'error' => 'Hợp đồng có hóa đơn chưa thanh toán. Vui lòng thanh toán tất cả hóa đơn trước khi kết thúc sớm.',
                    'status' => 400,
                ];
            }

            // Kiểm tra hóa đơn cho tháng hiện tại hoặc tháng trước
            $currentDate = Carbon::now();
            $currentMonth = $currentDate->month;
            $currentYear = $currentDate->year;

            if ($currentDate->day > 5) {
                // Nếu ngày hiện tại > 5, kiểm tra hóa đơn tháng hiện tại
                $hasCurrentMonthInvoice = $contract->invoices()
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->exists();
                if (!$hasCurrentMonthInvoice) {
                    return [
                        'error' => 'Hóa đơn cho tháng hiện tại chưa được tạo. Vui lòng chờ đến khi hóa đơn được tạo và thanh toán thành công.',
                        'status' => 400,
                    ];
                }
            } else {
                // Nếu ngày hiện tại từ 1-5, kiểm tra hóa đơn tháng trước
                $previousMonth = $currentDate->copy()->subMonth();
                $hasPreviousMonthInvoice = $contract->invoices()
                    ->where('month', $previousMonth->month)
                    ->where('year', $previousMonth->year)
                    ->exists();
                if (!$hasPreviousMonthInvoice) {
                    return [
                        'error' => 'Hóa đơn cho tháng trước chưa được tạo. Vui lòng chờ đến khi hóa đơn được tạo và thanh toán thành công.',
                        'status' => 400,
                    ];
                }
            }

            // Cập nhật trạng thái hợp đồng thành "Kết thúc sớm"
            $contract->update([
                'status' => 'Kết thúc sớm',
                'early_terminated_at' => now(),
            ]);

            // Gửi thông báo kết thúc sớm đến quản trị viên
            SendContractNotification::dispatch(
                $contract,
                'early_terminated',
                "Hợp đồng #{$contract->id} đã được kết thúc sớm",
                "Người dùng {$contract->user->name} đã kết thúc sớm hợp đồng #{$contract->id}."
            );

            // Trả về dữ liệu hợp đồng đã cập nhật
            return ['data' => $contract->fresh()];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi kết thúc hợp đồng sớm:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo và lưu file PDF hợp đồng.
     *
     * @param int $contractId ID của hợp đồng
     * @return array Kết quả với đường dẫn file hoặc lỗi
     */
    public function generateAndSaveContractPdf(int $contractId): array
    {
        return $this->contractPdfService->generateAndSaveContractPdf($contractId);
    }
}
