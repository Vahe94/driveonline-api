<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VincodeChecker;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/cars/vincode', VincodeChecker::class);

Route::middleware('auth:sanctum')->group(function () {
    //Authenticated middleware routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('verified')->group(function () {
        //email verified middleware routes
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);;
    });
});