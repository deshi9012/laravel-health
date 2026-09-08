<?php

namespace App\Services;

use App\Models\BodyMeasurement;

class BodyMeasurementService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): BodyMeasurement
    {
        return BodyMeasurement::query()->create($data);
    }

    public function latest(): ?BodyMeasurement
    {
        return BodyMeasurement::query()
            ->latest('measured_at')
            ->first();
    }
}
