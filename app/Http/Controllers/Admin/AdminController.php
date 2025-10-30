<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class AdminController extends Controller
{
    public function getPost(Post $post): JsonResponse
    {
        return response()->json($post->load(['photos', 'author']));
    }

    public function getPosts(PostStatus $status): JsonResponse
    {
        return response()->json(Post::ofStatus($status)->get());
    }

    public function approvePost(Post $post): Response
    {
        $post->status = PostStatus::APPROVED;
        $post->save();
        return response()->noContent(200);
    }

    public function rejectPost(Post $post, Request $request): Response
    {
        $post->status = PostStatus::REJECTED;
        $post->rejection_reason = $request->reason;
        $post->save();
        return response()->noContent(200);
    }

    public function login(Request $request): Response
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['is_admin'] = true;

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            return response()->noContent(200);
        }

        return response()->noContent(401);
    }
}
