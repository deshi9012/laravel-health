<?php

use App\Models\NutritionDaily;
use Tests\Support\CreatesNutritionDailySchema;

uses(CreatesNutritionDailySchema::class);

beforeEach(function (): void {
    $this->createNutritionDailyTable();
});

it('returns the latest nutrition day', function (): void {
    NutritionDaily::query()->create([
        'date' => '2026-08-31',
        'calories_kcal' => 2100,
        'protein_g' => 140,
        'source' => 'myfitnesspal',
        'synced_at' => now(),
    ]);

    $latest = NutritionDaily::query()->create([
        'date' => '2026-09-01',
        'calories_kcal' => 1950.5,
        'protein_g' => 155.25,
        'carbs_g' => 180,
        'fat_g' => 70,
        'fiber_g' => 30,
        'sugar_g' => 40,
        'source' => 'myfitnesspal',
        'synced_at' => now(),
    ]);

    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/nutrition/latest')
        ->assertOk()
        ->assertJsonPath('data.id', $latest->id)
        ->assertJsonPath('data.calories_kcal', '1950.50')
        ->assertJsonPath('data.source', 'myfitnesspal');
});

it('returns 404 when no nutrition data exists', function (): void {
    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/nutrition/latest')
        ->assertNotFound();
});

it('rejects an invalid api token for the latest nutrition day', function (): void {
    $this->withToken('wrong-token')
        ->getJson('/api/v1/nutrition/latest')
        ->assertUnauthorized();
});
