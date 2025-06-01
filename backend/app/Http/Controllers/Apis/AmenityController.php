<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\AmenityService;

class AmenityController extends Controller
{
    protected $amenityService;

    public function __construct(AmenityService $amenityService)
    {
        $this->amenityService = $amenityService;
    }

    public function index()
    {
        $amenities = $this->amenityService->getAmenities();
        return response()->json(['data' => $amenities]);
    }
}
