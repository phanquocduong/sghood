<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\DistrictService as ApisDistrictService;

class DistrictController extends Controller
{
    protected $districtService;

    public function __construct(ApisDistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index()
    {
        $districts = $this->districtService->getDistrictsWithMotelCount();
        return response()->json(['data' => $districts]);
    }
}
