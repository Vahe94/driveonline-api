<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Query\Builder;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = Post::ofStatus(PostStatus::APPROVED)
            ->when($request->has('title'), function (Builder $query, Request $request) {
                $query->whereFullText('title', $request->title);
            })
            ->when($request->has('price'), function (Builder $query, Request $request) {
                $query->whereBetween('price', [$request->price['min'], $request->price['max']]);
            })
            ->when($request->has('year'), function (Builder $query, Request $request) {
                $query->whereBetween('year', [$request->year['min'], $request->year['max']]);
            })
            ->when($request->has('color'), function (Builder $query, Request $request) {
                $query->where('color', $request->color);
            })
            ->when($request->has('wheel_position'), function (Builder $query, Request $request) {
                $query->where('wheel_position', $request->wheel_position);
            })
            ->when($request->has('condition'), function (Builder $query, Request $request) {
                $query->where('condition', $request->condition);
            })
            ->when($request->has('transmission'), function (Builder $query, Request $request) {
                $query->where('transmission', $request->transmission);
            })
            ->when($request->has('drive_type'), function (Builder $query, Request $request) {
                $query->where('drive_type', $request->drive_type);
            })
            ->when($request->has('body_type'), function (Builder $query, Request $request) {
                $query->where('body_type', $request->body_type);
            })
            ->when($request->has('mileage'), function (Builder $query, Request $request) {
                $query->whereBetween('mileage_amount', $request->mileage['min'], $request->mileage['max']);
                $query->where('mileage_unit', $request->mileage['unit']);
            })
            ->when($request->has('engine_capacity'), function (Builder $query, Request $request) {
                $query->whereBetween('engine_capacity', $request->engine_capacity['min'], $request->engine_capacity['max']);
            })
            ->when($request->has('fuel_type'), function (Builder $query, Request $request) {
                $query->where('fuel_type', $request->fuel_type);
            })
            ->when($request->has('power'), function (Builder $query, Request $request) {
                $query->whereBetween('power', $request->power['min'], $request->power['max']);
            })
        ->with('mainPhoto')
        ->get();

        return response()->json($posts);
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
            Post::withTrashed()->with(['photos', 'author', 'details'])->findOrFail($postId)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $postId): Response
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $data = $request->all();

        $post->details()->update($data['details']);

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

        $data['status'] = PostStatus::WAITING;
        $data['rejection_reason'] = null;
        $post->update($data);

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
