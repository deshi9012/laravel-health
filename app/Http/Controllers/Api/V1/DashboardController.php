<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DashboardResource;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function show(DashboardService $service): DashboardResource
    {
        return new DashboardResource($service->get());
    }
}
