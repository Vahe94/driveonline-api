<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class VincodeChecker extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $vin): JsonResponse
    {
        $data = $this->makeRequest($vin);
        return response()->json($data);
    }

    private function makeRequest(string $vin): array
    {
        $api_key = config('services.gibdd_api_key');
        $api_url = 'https://service.api-assist.com/parser/gibdd_api/history?key='. $api_key .'&vin='. $vin;
        $response = Http::get($api_url);
        return $response->json();
    }
}
