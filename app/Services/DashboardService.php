<?php

namespace App\Services;

use App\Models\BodyMeasurement;
use App\Models\NutritionDaily;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array{nutrition: Collection<int, NutritionDaily>, body_measurements: Collection<int, BodyMeasurement>}
     */
    public function get(): array
    {
        return [
            'nutrition' => NutritionDaily::query()
                ->where('date', '>=', now()->subDays(14)->toDateString())
                ->orderByDesc('date')
                ->get(),
            'body_measurements' => BodyMeasurement::query()
                ->orderByDesc('measured_at')
                ->limit(14)
                ->get(),
        ];
    }
}
