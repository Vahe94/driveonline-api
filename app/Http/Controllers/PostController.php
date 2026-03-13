<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        $request = $request->all();

        $post = $user->posts()->create(
            [
                'title' => $request['title'],
                'price' => $request['price'],
            ]
        );

        $post->details()->create($request['details']);

        $photos = [];

        if (isset($request['photos'])) {
            foreach ($request['photos'] as $file) {
                $path = $user->id . '/photos/' . $post->id . '/';
                $path = $file->store($path, 'public');
                $photos[] = ['url' => $path];
            }
        }

        if (!empty($photos)) {
            $post->photos()->createMany($photos);
        }

        return response()->json($post->load(['photos', 'author']));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $postId): JsonResponse
    {
        return response()->json(
            Post::withTrashed()::with(['photos', 'author', 'details'])->findOrFail($postId)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $postId): Response
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $data = $request->all();
        if (!$post->trashed()) {
            $data['status'] = PostStatus::WAITING;
            $data['rejection_reason'] = null;
        }

        $post->update($data);
        return response()->noContent(200);
    }

    public function archive(Post $post): Response
    {
        $post->delete();
        return response()->noContent(200);
    }

    public function restore(int $postId): Response
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $post->restore();
        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $postId): Response
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $post->forceDelete();
        return response()->noContent(200);
    }
}
