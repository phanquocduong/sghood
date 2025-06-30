<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractService;
use App\Services\Apis\InvoiceService;
use App\Services\Apis\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    public function __construct(
        private readonly ContractService $contractService,
        private readonly InvoiceService $invoiceService,
        private readonly NotificationService $notificationService
    ) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $data = $request->getContent() ? json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR) : null;

            $apiKey = $request->header('Authorization');
            $expectedApiKey = config('services.sepay.api_key');
            if (!$apiKey || stripos($apiKey, 'Apikey ' . $expectedApiKey) === false) {
                Log::error('API Key không hợp lệ', ['header' => $request->header(), 'expected' => $expectedApiKey]);
                return response()->json(['success' => false, 'message' => 'Invalid API Key'], 401);
            }

            $invoice = $this->invoiceService->processWebhook($data);

            // Thông báo admin
            $this->notificationService->notifyContractForAdmins($invoice->contract, 'Chờ thanh toán tiền cọc');

            return response()->json(['success' => true], 200);
        } catch (\JsonException) {
            Log::error('Lỗi parse JSON webhook', ['data' => $request->getContent()]);
            return response()->json(['success' => false, 'message' => 'Invalid JSON format'], 400);
        } catch (\Throwable $e) {
            Log::error('Lỗi xử lý webhook SePay', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            return response()->json(['success' => false, 'message' => 'Webhook processing failed'], 500);
        }
    }
}
