<?php

namespace App\Services;

use App\Models\RepairRequest;
use Illuminate\Support\Str;

class RepairRequestService
{
    public function filter($querySearch = null, $status = null, $sortOption = 'created_at_desc', $perPage = 10)
    {
        // $query = RepairRequest::with('contract');
         $query = RepairRequest::with(['contract.user', 'contract.room']);

        if ($querySearch) {
            $query->where(function ($q) use ($querySearch) {
                $q->where('title', 'like', "%{$querySearch}%")
                    ->orWhere('description', 'like', "%{$querySearch}%")
                    ->orWhere('note', 'like', "%{$querySearch}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sortOption) {
            if (Str::endsWith($sortOption, '_asc')) {
                $column = Str::beforeLast($sortOption, '_asc');
                $direction = 'asc';
            } elseif (Str::endsWith($sortOption, '_desc')) {
                $column = Str::beforeLast($sortOption, '_desc');
                $direction = 'desc';
            } else {
                $column = 'created_at'; // fallback
                $direction = 'desc';
            }

            if (in_array($column, ['created_at', 'updated_at', 'title'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->paginate($perPage);
    }


    public function getRepairRequestById($id)
    {
        return RepairRequest::with(['contract.user', 'contract.room'])->find($id);
    }

    public function updateStatus($id, $status, $cancelReason = null)
    {
        $repair = RepairRequest::find($id);

        if (!$repair) {
            return false;
        }

        $repair->status = $status;

        if ($status === 'Huỷ bỏ') {
            $repair->cancellation_reason = $cancelReason;
            $repair->repaired_at = null;
        } elseif ($status === 'Hoàn thành') {
            $repair->cancellation_reason = null;
            $repair->repaired_at = now();
        } else {
            $repair->cancellation_reason = null;
            $repair->repaired_at = null;
        }

        return $repair->save();
    }

}
