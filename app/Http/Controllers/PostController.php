<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Enums\PostStatus;
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

        $post->details()->create([
            'description' => $request['description'],
            'year' => $request['year'],
            'color' => $request['color'],
            'wheel_position' => $request['wheel_position'],
            'condition' => $request['condition'],
            'transmission' => $request['transmission'],
            'drive_type' => $request['drive_type'],
            'body_type' => $request['body_type'],
            'mileage_amount' => $request['mileage']['amount'],
            'mileage_unit' => $request['mileage']['unit'],
            'engine_capacity' => $request['engine']['volume'] ?? null,
            'fuel_type' => $request['engine']['fuel_type'],
            'power' => $request['engine']['hp'],
        ]);

        $photos = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
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
            Post::ofStatus(PostStatus::APPROVED)->with(['photos', 'author', 'details'])->findOrFail($postId)
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

    public function archive(Post $post): Response
    {
        $post->delete();
        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
