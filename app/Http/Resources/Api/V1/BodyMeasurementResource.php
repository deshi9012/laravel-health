<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BodyMeasurementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'measured_at' => $this->measured_at,
            'weight_kg' => $this->weight_kg,
            'bmi' => $this->bmi,
            'body_fat_pct' => $this->body_fat_pct,
            'lean_body_mass_kg' => $this->lean_body_mass_kg,
            'muscle_mass_kg' => $this->muscle_mass_kg,
            'water_pct' => $this->water_pct,
            'protein_pct' => $this->protein_pct,
            'bone_mass_kg' => $this->bone_mass_kg,
            'visceral_fat' => $this->visceral_fat,
            'bmr_kcal' => $this->bmr_kcal,
            'metabolic_age' => $this->metabolic_age,
            'impedance_ohm' => $this->impedance_ohm,
            'source' => $this->source,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
