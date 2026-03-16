<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\VinCodeChecker;

class UserController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function getPosts(Request $request): JsonResponse
    {
        return response()->json($request->user()->posts()->get());
    }

    public function getArchivedPosts(Request $request): JsonResponse
    {
        return response()->json($request->user()->posts()->onlyTrashed()->get());
    }

    public function checkVin(string $vin): JsonResponse
    {
        $response = VinCodeChecker::check($vin);
        return response()->json($response);
    }
}
