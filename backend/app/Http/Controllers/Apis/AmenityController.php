<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\AmenityService;

class AmenityController extends Controller
{
    public function __construct(
        private readonly AmenityService $amenityService
    ) {}

    public function index()
    {
        $amenities = $this->amenityService->getAmenities();
        return response()->json(['data' => $amenities]);
    }
}
