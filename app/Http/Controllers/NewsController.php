<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsRequest;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class NewsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(News::all());
    }

    public function getPublished(): JsonResponse
    {
        return response()->json(News::whereNotNull('published_at')->get());
    }

    public function store(NewsRequest $request): Response
    {
        $news = new News();
        $news->title = $request->title;
        $news->content = $request->input('content');
        $news->save();
        return response()->noContent(200);
    }

    public function show(News $news): JsonResponse
    {
        return response()->json($news);
    }

    public function publish(News $news): Response
    {
        $news->published_at = now();
        $news->save();
        return response()->noContent(200);
    }

    public function unpublish(News $news): Response
    {
        $news->published_at = null;
        $news->save();
        return response()->noContent(200);
    }
}
