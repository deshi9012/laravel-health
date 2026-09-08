<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nutrition' => NutritionDailyResource::collection($this->resource['nutrition']),
            'body_measurements' => BodyMeasurementResource::collection($this->resource['body_measurements']),
        ];
    }
}
