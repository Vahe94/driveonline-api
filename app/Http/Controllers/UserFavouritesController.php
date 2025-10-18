<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserFavouritesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $favourites = $user->favourites()->get();
        return response()->json($favourites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): Response
    {
        $user = $request->user();
        $user->favourites()->attach($request->post_id);
        return response()->noContent(201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): Response
    {
        $user = $request->user();
        $user->favourites()->detach($id);
        return response()->noContent(200);
    }
}
