<?php

use App\Models\BodyMeasurement;

function postBodyMeasurement(array $payload)
{
    return test()->withToken(config('health.home_assistant_token'))
        ->postJson('/api/v1/measurements/body', $payload);
}

function acceptedMeasurement(string $measuredAt, float $weightKg): BodyMeasurement
{
    return BodyMeasurement::query()->create([
        'measured_at' => $measuredAt,
        'weight_kg' => $weightKg,
        'source' => 'home_assistant',
    ]);
}

it('accepts the first body measurement when none exist', function (): void {
    $response = postBodyMeasurement([
        'measured_at' => '2026-09-07T07:00:00+03:00',
        'weight_kg' => 92.75,
        'source' => 'home_assistant',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('body_measurements', [
        'id' => $response->json('id'),
        'weight_kg' => 92.75,
    ]);
});

it('accepts a measurement within the configured weight difference', function (float $newWeight): void {
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    postBodyMeasurement([
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => $newWeight,
        'source' => 'home_assistant',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('body_measurements', [
        'weight_kg' => $newWeight,
    ]);
})->with([
    93.50,
    89.75,
    95.75,
]);

it('returns 422 when the weight differs by more than the configured maximum', function (float $newWeight, float $differenceKg): void {
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    postBodyMeasurement([
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => $newWeight,
        'source' => 'home_assistant',
    ])
        ->assertUnprocessable()
        ->assertExactJson([
            'message' => 'Body measurement rejected because weight differs too much from the last accepted measurement.',
            'difference_kg' => $differenceKg,
        ]);

    $this->assertDatabaseMissing('body_measurements', [
        'weight_kg' => $newWeight,
    ]);
    expect(BodyMeasurement::query()->count())->toBe(1);
})->with([
    [89.74, 3.01],
    [95.76, 3.01],
    [83.50, 9.25],
]);

it('compares against the latest accepted measurement rather than an older day', function (): void {
    acceptedMeasurement('2026-09-01 07:00:00', 80.00);
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    postBodyMeasurement([
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => 93.10,
        'source' => 'home_assistant',
    ])
        ->assertCreated();

    expect(BodyMeasurement::query()->count())->toBe(3);
});

it('does not use a rejected measurement as the next comparison baseline', function (): void {
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    postBodyMeasurement([
        'measured_at' => '2026-09-08T07:00:00+03:00',
        'weight_kg' => 83.50,
        'source' => 'home_assistant',
    ])->assertUnprocessable();

    postBodyMeasurement([
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => 93.10,
        'source' => 'home_assistant',
    ])->assertCreated();

    expect(BodyMeasurement::query()->count())->toBe(2);
    $this->assertDatabaseMissing('body_measurements', [
        'weight_kg' => 83.50,
    ]);
});

it('stores a measurement with a null weight without inventing a fallback', function (): void {
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    $response = postBodyMeasurement([
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => null,
        'source' => 'home_assistant',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('body_measurements', [
        'id' => $response->json('id'),
        'weight_kg' => null,
    ]);
});

it('returns 401 before applying the weight difference filter', function (): void {
    acceptedMeasurement('2026-09-07 07:00:00', 92.75);

    $this->postJson('/api/v1/measurements/body', [
        'measured_at' => '2026-09-09T07:00:00+03:00',
        'weight_kg' => 83.50,
    ])
        ->assertUnauthorized()
        ->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);

    $this->assertDatabaseMissing('body_measurements', [
        'weight_kg' => 83.50,
    ]);
});
