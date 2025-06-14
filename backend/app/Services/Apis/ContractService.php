<?php
namespace App\Services\Apis;
use App\Models\Contract;

class ContractService
{
    public function getContractById($id)
    {
        return Contract::find($id);
    }
    public function getContractByUser($userId)
    {
        return Contract::where('user_id', $userId)->get();
    }

}
