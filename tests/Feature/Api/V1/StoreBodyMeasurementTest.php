<?php

use App\Models\BodyMeasurement;

it('stores a valid body measurement', function (): void {
    $payload = [
        'measured_at' => '2026-09-01T07:15:00+03:00',
        'weight_kg' => 82.4,
        'bmi' => 24.1,
        'body_fat_pct' => 18.2,
        'lean_body_mass_kg' => 67.4,
        'muscle_mass_kg' => 64.1,
        'water_pct' => 55.0,
        'protein_pct' => 18.0,
        'bone_mass_kg' => 3.4,
        'visceral_fat' => 8,
        'bmr_kcal' => 1720,
        'metabolic_age' => 31,
        'impedance_ohm' => 512,
        'source' => 'home_assistant',
    ];

    $response = $this->withToken(config('health.home_assistant_token'))
        ->postJson('/api/v1/measurements/body', $payload);

    $response->assertCreated()
        ->assertExactJson([
            'success' => true,
            'id' => $response->json('id'),
        ]);

    expect($response->json('id'))->toBeInt();

    $this->assertDatabaseHas('body_measurements', [
        'id' => $response->json('id'),
        'source' => 'home_assistant',
        'bmr_kcal' => 1720,
        'metabolic_age' => 31,
    ]);

    expect(BodyMeasurement::query()->find($response->json('id')))
        ->weight_kg->toEqual('82.40')
        ->bmi->toEqual('24.10');
});

it('stores a partial body measurement with null fields', function (): void {
    $response = $this->withToken(config('health.home_assistant_token'))
        ->postJson('/api/v1/measurements/body', [
            'measured_at' => '2026-09-01T07:15:00+03:00',
            'weight_kg' => 82.4,
            'bmi' => null,
            'body_fat_pct' => null,
            'source' => 'home_assistant',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('body_measurements', [
        'id' => $response->json('id'),
        'weight_kg' => 82.4,
        'bmi' => null,
        'body_fat_pct' => null,
        'lean_body_mass_kg' => null,
        'source' => 'home_assistant',
    ]);
});

it('rejects an invalid home assistant api token', function (): void {
    $this->withToken('wrong-token')
        ->postJson('/api/v1/measurements/body', [
            'measured_at' => '2026-09-01T07:15:00+03:00',
            'weight_kg' => 82.4,
        ])
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);

    $this->postJson('/api/v1/measurements/body', [
        'measured_at' => '2026-09-01T07:15:00+03:00',
        'weight_kg' => 82.4,
    ])
        ->assertUnauthorized();
});

it('rejects a body measurement without measured_at', function (): void {
    $this->withToken(config('health.home_assistant_token'))
        ->postJson('/api/v1/measurements/body', [
            'weight_kg' => 82.4,
            'source' => 'home_assistant',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['measured_at']);
});
