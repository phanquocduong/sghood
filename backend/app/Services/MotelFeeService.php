<?php
namespace App\Services;

use App\Models\MotelFee;

class MotelFeeService
{
    public function create(array $data): MotelFee
    {
        return MotelFee::create($data);
    }

    public function update(MotelFee $motelFee, array $data): MotelFee
    {
        $motelFee->update($data);
        return $motelFee;
    }

    public function delete(MotelFee $motelFee): bool
    {
        return $motelFee->delete();
    }

    public function getAllByMotel($motelId)
    {
        return MotelFee::where('motel_id', $motelId)->get();
    }
}
