<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Enums\PostStatus;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Post::ofStatus(PostStatus::APPROVED)->with('mainPhoto')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $user = $request->user();

        $post = $user->posts()->create(
            $request->validated()
        );

        $photos = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $user->id . '/photos/' . $post->id . '/';
                $path = $file->store($path, 'public');
                $photos[] = ['url' => $path];
            }
        }

        $photos = $post->photos()->createMany($photos);

        return response()->json($post->load(['photos', 'author']));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $postId): JsonResponse
    {
        return response()->json(
            Post::ofStatus(PostStatus::APPROVED)->with(['photos', 'author'])->findOrFail($postId)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, Post $post)
    {
        //
        //set status to waiting and rejection_message to null
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
