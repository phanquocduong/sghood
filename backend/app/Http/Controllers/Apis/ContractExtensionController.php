<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractExtensionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractExtensionController extends Controller
{
    public function __construct(
        private readonly ContractExtensionService $contractExtensionService,
    ) {}

    public function reject(int $id): JsonResponse
    {
        try {
            $result = $this->contractExtensionService->rejectContractExtension($id);

            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error'],
                    'status' => $result['status'],
                ], $result['status']);
            }

            return response()->json(['message' => 'Hủy gia hạn thành công'], 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy gia hạn', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Đã xảy ra lỗi khi hủy gia hạn'], 500);
        }
    }
}
