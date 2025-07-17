<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractExtensions extends Model
{
    protected $table = 'contract_extensions';

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
