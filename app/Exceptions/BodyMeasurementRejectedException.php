<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BodyMeasurementRejectedException extends Exception implements ShouldntReport
{
    public function __construct(private readonly string $differenceKg)
    {
        parent::__construct('Body measurement rejected because weight differs too much from the last accepted measurement.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'difference_kg' => (float) $this->differenceKg,
        ], 422);
    }
}
