<?php

namespace App\Services;

use App\Exceptions\BodyMeasurementRejectedException;
use App\Models\BodyMeasurement;
use Carbon\CarbonImmutable;

class BodyMeasurementService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): BodyMeasurement
    {
        $this->rejectUnrealisticWeightChange($data);

        return BodyMeasurement::query()->create($data);
    }

    public function latest(): ?BodyMeasurement
    {
        return BodyMeasurement::query()
            ->latest('measured_at')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function rejectUnrealisticWeightChange(array $data): void
    {
        if (! array_key_exists('weight_kg', $data) || $data['weight_kg'] === null) {
            return;
        }

        $incomingMeasuredAt = CarbonImmutable::parse($data['measured_at']);
        $lastAccepted = $this->latestAcceptedBefore($incomingMeasuredAt);

        if ($lastAccepted === null || $lastAccepted->weight_kg === null) {
            return;
        }

        $incomingWeight = $this->decimalString($data['weight_kg']);
        $lastWeight = $this->decimalString($lastAccepted->weight_kg);
        $difference = $this->absoluteDifference($incomingWeight, $lastWeight);
        $maximum = $this->decimalString(config('health.body_weight_max_difference_kg'));

        if (bccomp($difference, $maximum, 2) === 1) {
            throw new BodyMeasurementRejectedException($difference);
        }
    }

    private function latestAcceptedBefore(CarbonImmutable $incomingMeasuredAt): ?BodyMeasurement
    {
        return BodyMeasurement::query()
            ->where('measured_at', '<', $incomingMeasuredAt)
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->first();
    }

    private function decimalString(mixed $value): string
    {
        return bcadd(sprintf('%.2F', $value), '0', 2);
    }

    private function absoluteDifference(string $left, string $right): string
    {
        $difference = bcsub($left, $right, 2);

        if (str_starts_with($difference, '-')) {
            return bcsub('0.00', $difference, 2);
        }

        return $difference;
    }
}
