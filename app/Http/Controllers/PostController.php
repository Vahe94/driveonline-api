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
    public function show(Request $request, Post $post): JsonResponse
    {
        return response()->json(
            $post->load(['photos', 'author', 'details'])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): Response
    {
        $data = $request->all();
        $data['status'] = PostStatus::WAITING;
        $data['rejection_reason'] = null;

        $post->update($data);
        return response()->noContent(200);
    }

    public function archive(Request $request, Post $post): Response
    {
        $post->delete();
        return response()->noContent(200);
    }

    public function restore(Request $request, Post $post): Response
    {
        $post->restore();

        $data['status'] = PostStatus::WAITING;
        $data['rejection_reason'] = null;
        $post->update($data);

        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post): Response
    {
        $post->forceDelete();
        return response()->noContent(200);
    }
}
