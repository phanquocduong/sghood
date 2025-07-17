<?php
namespace App\Services;
use App\Models\ContractExtensions;

class ContractExtensionsService
{
    protected $contractExtensions;

    public function __construct(ContractExtensions $contractExtensions)
    {
        $this->contractExtensions = $contractExtensions;
    }

    public function getAllExtensions()
    {
        return $this->contractExtensions->all();
    }

    public function getExtensionById($id)
    {
        return $this->contractExtensions->find($id);
    }

    public function getPendingApprovals()
    {
        return $this->contractExtensions->where('status', 'chờ duyệt')->get();
    }
}