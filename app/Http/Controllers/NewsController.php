<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsRequest;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(News::query()->latest('updated_at')->get());
    }

    public function getPublished(): JsonResponse
    {
        return response()->json(
            News::query()
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->get()
        );
    }

    public function showPublishedBySlug(string $slug): JsonResponse
    {
        $news = News::query()
            ->whereNotNull('published_at')
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($news);
    }

    public function store(NewsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->makeUniqueSlug($request->title);
        $news = News::create($data);

        return response()->json($news, 201);
    }

    public function show(News $news): JsonResponse
    {
        return response()->json($news);
    }

    public function update(NewsRequest $request, News $news): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['title']) && $data['title'] !== $news->title) {
            $data['slug'] = $this->makeUniqueSlug($data['title'], $news->id);
        }

        $news->update($data);

        return response()->json($news->fresh());
    }

    public function destroy(News $news): JsonResponse
    {
        $news->delete();

        return response()->json(['message' => 'News deleted']);
    }

    public function publish(News $news): JsonResponse
    {
        $news->published_at = now();
        $news->save();

        return response()->json($news->fresh());
    }

    public function unpublish(News $news): JsonResponse
    {
        $news->published_at = null;
        $news->save();

        return response()->json($news->fresh());
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : 'news';
        $suffix = 2;

        while (
            News::query()
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = sprintf('%s-%d', $baseSlug !== '' ? $baseSlug : 'news', $suffix);
            $suffix++;
        }

        return $slug;
    }
}
