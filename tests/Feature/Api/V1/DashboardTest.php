<?php

use App\Models\BodyMeasurement;
use App\Models\NutritionDaily;
use Tests\Support\CreatesNutritionDailySchema;

uses(CreatesNutritionDailySchema::class);

beforeEach(function (): void {
    $this->createNutritionDailyTable();
});

it('returns recent nutrition and body measurement data', function (): void {
    NutritionDaily::query()->create([
        'date' => now()->toDateString(),
        'calories_kcal' => 1950.5,
        'protein_g' => 155,
        'source' => 'myfitnesspal',
        'synced_at' => now(),
    ]);

    NutritionDaily::query()->create([
        'date' => now()->subDays(20)->toDateString(),
        'calories_kcal' => 1800,
        'source' => 'myfitnesspal',
        'synced_at' => now(),
    ]);

    $measurement = BodyMeasurement::query()->create([
        'measured_at' => now(),
        'weight_kg' => 82.4,
        'source' => 'home_assistant',
    ]);

    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonCount(1, 'data.nutrition')
        ->assertJsonCount(1, 'data.body_measurements')
        ->assertJsonPath('data.nutrition.0.calories_kcal', '1950.50')
        ->assertJsonPath('data.body_measurements.0.id', $measurement->id);
});

it('returns empty collections when no dashboard data exists', function (): void {
    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.nutrition', [])
        ->assertJsonPath('data.body_measurements', []);
});

it('rejects an invalid api token for the dashboard', function (): void {
    $this->withToken('wrong-token')
        ->getJson('/api/v1/dashboard')
        ->assertUnauthorized();
});
