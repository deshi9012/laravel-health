<?php

use Tests\Support\CreatesNutritionDailySchema;

uses(CreatesNutritionDailySchema::class);

beforeEach(function (): void {
    $this->createNutritionDailyTable();
});

$getEndpoints = [
    '/api/v1/measurements/body/latest',
    '/api/v1/nutrition/latest',
    '/api/v1/dashboard',
];

$postPayload = [
    'measured_at' => '2026-09-01T07:15:00+03:00',
    'weight_kg' => 82.4,
];

it('returns 401 when the body measurement POST has no token', function () use ($postPayload): void {
    $this->postJson('/api/v1/measurements/body', $postPayload)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

it('returns 401 when the body measurement POST has an invalid token', function () use ($postPayload): void {
    $this->withToken('wrong-token')
        ->postJson('/api/v1/measurements/body', $postPayload)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

it('authenticates the body measurement POST with HOME_ASSISTANT_API_TOKEN', function () use ($postPayload): void {
    $this->withToken(config('health.home_assistant_token'))
        ->postJson('/api/v1/measurements/body', $postPayload)
        ->assertCreated()
        ->assertJson([
            'success' => true,
        ]);
});

it('returns 401 when a GET endpoint has no token', function (string $path): void {
    $this->getJson($path)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
})->with($getEndpoints);

it('returns 401 when a GET endpoint has an invalid token', function (string $path): void {
    $this->withToken('wrong-token')
        ->getJson($path)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
})->with($getEndpoints);

it('authenticates GET endpoints with HEALTH_API_TOKEN', function (string $path, int $status): void {
    $this->withToken(config('health.health_api_token'))
        ->getJson($path)
        ->assertStatus($status);
})->with([
    ['/api/v1/measurements/body/latest', 404],
    ['/api/v1/nutrition/latest', 404],
    ['/api/v1/dashboard', 200],
]);

it('returns 401 when HEALTH_API_TOKEN is used on the body measurement POST', function () use ($postPayload): void {
    $this->withToken(config('health.health_api_token'))
        ->postJson('/api/v1/measurements/body', $postPayload)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});

it('returns 401 when HOME_ASSISTANT_API_TOKEN is used on a GET endpoint', function (string $path): void {
    $this->withToken(config('health.home_assistant_token'))
        ->getJson($path)
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
})->with($getEndpoints);
