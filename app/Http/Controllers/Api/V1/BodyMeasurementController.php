<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBodyMeasurementRequest;
use App\Http\Resources\Api\V1\BodyMeasurementResource;
use App\Services\BodyMeasurementService;
use Illuminate\Http\JsonResponse;

class BodyMeasurementController extends Controller
{
    public function store(StoreBodyMeasurementRequest $request, BodyMeasurementService $service): JsonResponse
    {
        $measurement = $service->store($request->validated());

        return response()->json([
            'success' => true,
            'id' => $measurement->id,
        ], 201);
    }

    public function latest(BodyMeasurementService $service): BodyMeasurementResource
    {
        $measurement = $service->latest();

        if ($measurement === null) {
            abort(404);
        }

        return new BodyMeasurementResource($measurement);
    }
}
