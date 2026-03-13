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
        $url = 'https://service.api-assist.com/parser/gibdd_api/accident?key=b1656d17713600b360f0457b06603290&vin=JN1FANF15U0109775';
        $api_url = 'https://service.api-assist.com/parser/gibdd_api/history?key='. $api_key .'&vin='. $vin;
        $response = Http::get($url);
        return $response->json();
    }
}
