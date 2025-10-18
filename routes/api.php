<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VincodeChecker;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavouritesController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/cars/vincode/{vin}', VincodeChecker::class);

Route::middleware('auth:sanctum')->group(function () {
    //Authenticated middleware routes
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::get('/posts', [UserController::class, 'getPosts']);
        Route::apiResource('favourites', UserFavouritesController::class)->except(['update', 'show']);
    });

    Route::middleware('verified')->group(function () {
        //email verified middleware routes
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    });
});