<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Faq::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request): Response
    {
        Faq::create($request->validated());
        return response()->noContent(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq): JsonResponse
    {
        return response()->json($faq);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq): Response
    {
        $faq->update($request->validated());
        return response()->noContent(200);
    }

    public function getArchive(): JsonResponse
    {
        return response()->json(Faq::onlyTrashed()->get());
    }

    public function archive(Faq $faq): Response
    {
        $faq->delete();
        return response()->noContent(200);
    }

    public function restore(Faq $faq): Response
    {
        $faq->restore();
        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq): Response
    {
        $faq->forceDelete();
        return response()->noContent(200);
    }
}
