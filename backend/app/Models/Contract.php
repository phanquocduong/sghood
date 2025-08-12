<?php

namespace App\Models;

use App\Services\ContractService;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contracts';

    protected $fillable = [
        'room_id',
        'user_id',
        'booking_id',
        'start_date',
        'end_date',
        'rental_price',
        'deposit_amount',
        'content',
        'signature',
        'status',
        'file',
        'signed_at',
        'early_terminated_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_price' => 'integer',
        'deposit_amount' => 'integer',
        'signed_at' => 'datetime',
        'early_terminated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }

    public function extensions()
    {
        return $this->hasMany(ContractExtension::class, 'contract_id');
    }

    public function checkouts()
    {
        return $this->hasMany(Checkout::class, 'contract_id');
    }

    public function checkOverdueInvoices(): bool
    {
        return app(ContractService::class)->checkOverdueInvoices($this->id);
    }
}
