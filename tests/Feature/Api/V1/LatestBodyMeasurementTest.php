<?php

use App\Models\BodyMeasurement;

it('returns the latest body measurement', function (): void {
    BodyMeasurement::query()->create([
        'measured_at' => '2026-08-31 07:00:00',
        'weight_kg' => 83.0,
        'source' => 'home_assistant',
    ]);

    $latest = BodyMeasurement::query()->create([
        'measured_at' => '2026-09-01 07:15:00',
        'weight_kg' => 82.4,
        'source' => 'home_assistant',
    ]);

    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/measurements/body/latest')
        ->assertOk()
        ->assertJsonPath('data.id', $latest->id)
        ->assertJsonPath('data.weight_kg', '82.40');
});

it('returns 404 when no body measurements exist', function (): void {
    $this->withToken(config('health.health_api_token'))
        ->getJson('/api/v1/measurements/body/latest')
        ->assertNotFound();
});

it('rejects an invalid api token for the latest body measurement', function (): void {
    $this->withToken('wrong-token')
        ->getJson('/api/v1/measurements/body/latest')
        ->assertUnauthorized();
});
