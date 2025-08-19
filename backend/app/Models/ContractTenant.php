<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTenant extends Model
{
    use HasFactory;

    protected $table = 'contract_tenants';

    protected $fillable = [
        'contract_id',
        'name',
        'phone',
        'email',
        'gender',
        'birthdate',
        'address',
        'identity_document',
        'relation_with_primary',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'gender' => 'string',
        'status' => 'string',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
