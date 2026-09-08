<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NutritionDailyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'calories_kcal' => $this->calories_kcal,
            'protein_g' => $this->protein_g,
            'carbs_g' => $this->carbs_g,
            'fat_g' => $this->fat_g,
            'fiber_g' => $this->fiber_g,
            'sugar_g' => $this->sugar_g,
            'source' => $this->source,
            'synced_at' => $this->synced_at,
        ];
    }
}
