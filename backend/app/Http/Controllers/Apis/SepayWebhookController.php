<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Jobs\Apis\SendContractNotification;
use App\Services\Apis\ContractService;
use App\Services\Apis\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller xử lý các yêu cầu webhook từ SePay để cập nhật trạng thái thanh toán.
 */
class SepayWebhookController extends Controller
{
    /**
     * Khởi tạo controller với các dịch vụ liên quan.
     *
     * @param ContractService $contractService Dịch vụ xử lý logic hợp đồng
     * @param InvoiceService $invoiceService Dịch vụ xử lý logic hóa đơn
     */
    public function __construct(
        private readonly ContractService $contractService,
        private readonly InvoiceService $invoiceService
    ) {}

    /**
     * Xử lý webhook từ SePay để cập nhật trạng thái hóa đơn.
     *
     * @param Request $request Yêu cầu chứa dữ liệu webhook từ SePay
     * @return JsonResponse Phản hồi JSON chỉ trạng thái xử lý webhook
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            // Phân tích dữ liệu JSON từ nội dung yêu cầu
            $data = $request->getContent() ? json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR) : null;

            // Kiểm tra API Key trong header để xác thực yêu cầu
            $apiKey = $request->header('Authorization');
            $expectedApiKey = config('services.sepay.api_key');
            if (!$apiKey || stripos($apiKey, 'Apikey ' . $expectedApiKey) === false) {
                // Ghi log lỗi nếu API Key không hợp lệ
                Log::error('API Key không hợp lệ', ['header' => $request->header(), 'expected' => $expectedApiKey]);
                return response()->json(['success' => false, 'message' => 'Invalid API Key'], 401);
            }

            // Gọi dịch vụ để xử lý webhook và cập nhật trạng thái hóa đơn
            $invoice = $this->invoiceService->processWebhook($data);

            // Nếu hóa đơn là loại đặt cọc, gửi thông báo đến quản trị viên
            if ($invoice->type === 'Đặt cọc') {
                SendContractNotification::dispatch(
                    $invoice->contract,
                    'deposit_paid',
                    "Hợp đồng #{$invoice->contract->id} đã thanh toán tiền cọc",
                    "Hợp đồng #{$invoice->contract->id} từ người dùng {$invoice->contract->user->name} đã thanh toán tiền cọc và đã được kích hoạt."
                );
            }

            // Trả về phản hồi JSON thành công
            return response()->json(['success' => true], 200);
        } catch (\JsonException) {
            // Ghi log lỗi nếu không thể phân tích JSON
            Log::error('Lỗi parse JSON webhook', ['data' => $request->getContent()]);
            return response()->json(['success' => false, 'message' => 'Invalid JSON format'], 400);
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra trong quá trình xử lý
            Log::error('Lỗi xử lý webhook SePay', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Webhook processing failed'], 500);
        }
    }
}
