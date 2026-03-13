<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

    public function getPost(Request $request, Post $post): JsonResponse
    {
        return response()->json($post->load(['details', 'photos']));
    }

    public function getArchivedPosts(Request $request): JsonResponse
    {
        return response()->json($request->user()->posts()->onlyTrashed()->get());
    }
}
