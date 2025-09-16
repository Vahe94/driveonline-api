<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class VincodeChecker extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $vin): JsonResponse
    {
        return response()->json([
            'vin' => $vin,
            'Title' => 'Car one',
            'Year' => 2005
        ]);
    }
}
