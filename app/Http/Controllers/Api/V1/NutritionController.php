<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NutritionDailyResource;
use App\Models\NutritionDaily;

class NutritionController extends Controller
{
    public function latest(): NutritionDailyResource
    {
        $nutrition = NutritionDaily::query()
            ->orderByDesc('date')
            ->first();

        if ($nutrition === null) {
            abort(404);
        }

        return new NutritionDailyResource($nutrition);
    }
}
