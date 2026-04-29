<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Enums\PostStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Builder;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 12), 48));

        $posts = Post::ofStatus(PostStatus::APPROVED)
            ->when($request->title, function (Builder $query, string $title) {
                $query->whereFullText('title', $title);
            })
            ->when($request->city, function (Builder $query, string $city) {
                $query->whereHas('author', function (Builder $authorQuery) use ($city) {
                    $authorQuery->where('city', trim($city));
                });
            })
            ->when($request->price, function (Builder $query, array $price) {
                $query->whereBetween('price', [$price['min'], $price['max']]);
            })
            ->whereHas('details', function (Builder $query) use ($request) {
                $query->when($request->year, function (Builder $query, array $year) {
                    $query->whereBetween('year', [$year['min'], $year['max']]);
                })
                ->when($request->color, function (Builder $query, string $color) {
                    $query->where('color', $color);
                })
                ->when($request->wheel_position, function (Builder $query, string $wheel_position) {
                    $query->where('wheel_position', $wheel_position);
                })
                ->when($request->condition, function (Builder $query, string $condition) {
                    $query->where('condition', $condition);
                })
                ->when($request->transmission, function (Builder $query, string $transmission) {
                    $query->where('transmission', $transmission);
                })
                ->when($request->drive_type, function (Builder $query, string $drive_type) {
                    $query->where('drive_type', $drive_type);
                })
                ->when($request->body_type, function (Builder $query, string $body_type) {
                    $query->where('body_type', $body_type);
                })
                ->when($request->mileage, function (Builder $query, array $mileage) {
                    $query->whereBetween('mileage_amount', [$mileage['min'], $mileage['max']]);
                    $query->where('mileage_unit', $mileage['unit']);
                })
                ->when($request->engine_capacity, function (Builder $query, array $engine_capacity) {
                    $query->whereBetween('engine_capacity', [$engine_capacity['min'], $engine_capacity['max']]);
                })
                ->when($request->fuel_type, function (Builder $query, string $fuel_type) {
                    $query->where('fuel_type', $fuel_type);
                })
                ->when($request->power, function (Builder $query, array $power) {
                    $query->whereBetween('power', [$power['min'], $power['max']]);
                });
            })
        ->with(['mainPhoto', 'author', 'details'])
        ->orderByDesc('created_at')
        ->paginate($perPage);

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $user = $request->user();

        $request = $request->all();

        $post = $user->posts()->create($request);

        $post->details()->create($request['details']);



        if (isset($request['photos'])) {
            $photos = [];

            foreach ($request['photos'] as $file) {
                $path = $user->id . '/photos/' . $post->id;
                $path = $file->store($path, 'public');
                $photos[] = ['url' => $path];
            }

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
    public function update(StorePostRequest $request, int $postId): Response
    {
        $post = Post::withTrashed()->findOrFail($postId);
        $data = $request->all();

        if (!$post->trashed() || !isset($data['payed'])) {
            $data['status'] = PostStatus::WAITING->value;
            $data['rejection_reason'] = null;
        }

        $post->update($data);

        if (isset($data['details'])) {
            $post->details()->update($data['details']);
        }

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

        $data['status'] = PostStatus::WAITING->value;
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
