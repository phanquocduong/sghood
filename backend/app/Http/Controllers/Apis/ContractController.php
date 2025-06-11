<?php
namespace App\Http\Controllers\Apis;
use App\Http\Controllers\Controller;
use App\Services\Apis\ContractService;

class ContractController extends Controller
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function getContractById($id)
    {
        $contract = $this->contractService->getContractById($id);
        if (!$contract) {
            return response()->json(['message' => 'Hợp đồng không tồn tại'], 404);
        }
        return response()->json($contract, 200);
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
