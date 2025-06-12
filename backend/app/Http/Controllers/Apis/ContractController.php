<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\ContractService;
use Illuminate\Http\Request;

class ContractController extends Controller
{
     protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function getContractsByUser($userId)
    {
        $contracts = $this->contractService->getContractByUser($userId);
        if ($contracts->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy hợp đồng cho người dùng này'], 404);
        }
        return response()->json($contracts, 200);
    }
}
