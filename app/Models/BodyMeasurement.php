<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'measured_at',
    'weight_kg',
    'bmi',
    'body_fat_pct',
    'lean_body_mass_kg',
    'muscle_mass_kg',
    'water_pct',
    'protein_pct',
    'bone_mass_kg',
    'visceral_fat',
    'bmr_kcal',
    'metabolic_age',
    'impedance_ohm',
    'source',
])]
class BodyMeasurement extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime',
            'weight_kg' => 'decimal:2',
            'bmi' => 'decimal:2',
            'body_fat_pct' => 'decimal:2',
            'lean_body_mass_kg' => 'decimal:2',
            'muscle_mass_kg' => 'decimal:2',
            'water_pct' => 'decimal:2',
            'protein_pct' => 'decimal:2',
            'bone_mass_kg' => 'decimal:2',
            'visceral_fat' => 'decimal:2',
            'bmr_kcal' => 'integer',
            'metabolic_age' => 'integer',
            'impedance_ohm' => 'decimal:2',
        ];
    }
}
