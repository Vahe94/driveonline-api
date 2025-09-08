<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

//Authenticated middleware routes
Route::middleware('auth:sanctum')->group(function () {

    //email verified middleware routes
    Route::middleware('verified')->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('posts', PostController::class)->except(['index', 'show']);;
    });
});