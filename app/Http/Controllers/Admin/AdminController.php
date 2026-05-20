<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function getPost(Post $post): JsonResponse
    {
        return response()->json($post->load(['mainPhoto', 'photos', 'author', 'details']));
    }

    public function getPosts(PostStatus $status): JsonResponse
    {
        return response()->json(
            Post::ofStatus($status)
                ->with(['mainPhoto', 'author', 'details'])
                ->latest('updated_at')
                ->get()
        );
    }

    public function approvePost(Post $post): Response
    {
        $post->status = PostStatus::APPROVED;
        $post->save();

        return response()->noContent(200);
    }

    public function rejectPost(Request $request, Post $post): Response
    {
        $post->status = PostStatus::REJECTED;
        $post->rejection_reason = $request->reason;
        $post->save();

        return response()->noContent(200);
    }

    public function toggleFeaturedPost(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'payed' => ['required', 'boolean'],
        ]);

        $post->payed = $validated['payed'];
        $post->save();

        return response()->json($post->fresh(['mainPhoto', 'author', 'details']));
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

    public function updatePassword(Request $request): Response
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The provided password does not match your current password.')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->noContent(204);
    }
}
