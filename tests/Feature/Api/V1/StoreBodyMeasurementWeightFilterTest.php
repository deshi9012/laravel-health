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

it('compares a historical measurement against the previous chronological record, not the latest overall', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-10 08:00:00', 92.00);
    acceptedMeasurement('2026-09-18 08:00:00', 90.70);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 94.00,
        'source' => 'home_assistant',
    ])->assertCreated();

    $this->assertDatabaseHas('body_measurements', [
        'weight_kg' => 94.00,
    ]);
    expect(BodyMeasurement::query()->count())->toBe(4);
});

it('accepts the oldest historical measurement when no earlier record exists', function (): void {
    acceptedMeasurement('2026-09-18 08:00:00', 90.70);

    postBodyMeasurement([
        'measured_at' => '2026-08-01T08:00:00+03:00',
        'weight_kg' => 100.00,
        'source' => 'home_assistant',
    ])->assertCreated();

    $this->assertDatabaseHas('body_measurements', [
        'weight_kg' => 100.00,
    ]);
});

it('accepts a historical measurement within 3 kg of the previous chronological record', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 90.70);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 91.50,
        'source' => 'home_assistant',
    ])->assertCreated();

    $this->assertDatabaseHas('body_measurements', [
        'weight_kg' => 91.50,
    ]);
});

it('returns 422 when a historical measurement differs by more than 3 kg from the previous chronological record', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 90.70);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 90.00,
        'source' => 'home_assistant',
    ])
        ->assertUnprocessable()
        ->assertExactJson([
            'message' => 'Body measurement rejected because weight differs too much from the last accepted measurement.',
            'difference_kg' => 4.50,
        ]);

    $this->assertDatabaseMissing('body_measurements', [
        'weight_kg' => 90.00,
    ]);
    expect(BodyMeasurement::query()->count())->toBe(2);
});

it('compares a new live measurement against the latest earlier measurement', function (): void {
    acceptedMeasurement('2026-09-18 08:00:00', 90.70);

    postBodyMeasurement([
        'measured_at' => '2026-09-19T08:00:00+03:00',
        'weight_kg' => 90.50,
        'source' => 'home_assistant',
    ])->assertCreated();

    expect(BodyMeasurement::query()->count())->toBe(2);
});

it('does not let later records affect a historical comparison', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 80.00);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 94.00,
        'source' => 'home_assistant',
    ])->assertCreated();

    expect(BodyMeasurement::query()->count())->toBe(3);
});

it('accepts a historical measurement with an exact 3.00 kg difference from the previous chronological record', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 80.00);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 91.50,
        'source' => 'home_assistant',
    ])->assertCreated();
});

it('returns 422 when a historical measurement differs by 3.01 kg from the previous chronological record', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 80.00);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 91.49,
        'source' => 'home_assistant',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('difference_kg', 3.01);

    $this->assertDatabaseMissing('body_measurements', [
        'weight_kg' => 91.49,
    ]);
});

it('uses the highest id when previous measurements share the same measured_at', function (): void {
    acceptedMeasurement('2026-08-30 08:00:00', 80.00);
    acceptedMeasurement('2026-08-30 08:00:00', 94.50);
    acceptedMeasurement('2026-09-18 08:00:00', 80.00);

    postBodyMeasurement([
        'measured_at' => '2026-09-01T08:00:00+03:00',
        'weight_kg' => 94.00,
        'source' => 'home_assistant',
    ])->assertCreated();

    expect(BodyMeasurement::query()->count())->toBe(4);
});
