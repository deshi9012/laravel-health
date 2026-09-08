<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBodyMeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'measured_at' => ['required', 'date'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'bmi' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'body_fat_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lean_body_mass_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'muscle_mass_kg' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'water_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'protein_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bone_mass_kg' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'visceral_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bmr_kcal' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'metabolic_age' => ['nullable', 'numeric', 'min:0', 'max:150'],
            'impedance_ohm' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'source' => ['nullable', 'string', 'max:32'],
        ];
    }
}
