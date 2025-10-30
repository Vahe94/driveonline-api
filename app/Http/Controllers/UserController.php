<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function getPost(Request $request, int $postId): JsonResponse
    {
        return response()->json($request->user()->posts()->findOrFail($postId));
    }
}
