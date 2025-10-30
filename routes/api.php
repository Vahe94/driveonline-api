<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VincodeChecker;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavouritesController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\Admin as AdminAuth;
use App\Http\Controllers\FaqController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/vincode/{vin}', VincodeChecker::class);
Route::get('/faq', [FaqController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    //Authenticated middleware routes
    Route::get('/user', [UserController::class, 'show']);

    Route::middleware('verified')->group(function () {
        //email verified middleware routes
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);

        Route::prefix('user')->group(function () {
            Route::get('/posts/{post}', [UserController::class, 'getPost']);
            Route::get('/posts', [UserController::class, 'getPosts']);
            Route::apiResource('favourites', UserFavouritesController::class)->except(['update', 'show']);
        });
    });

    Route::prefix('admin')
        ->middleware(AdminAuth::class)->group(function () {
            Route::prefix('posts')->group(function () {
                Route::get('/status/{status}', [AdminController::class, 'getPosts']);
                Route::post('/{post}/approve', [AdminController::class, 'approvePost']);
                Route::post('/{post}/reject', [AdminController::class, 'rejectPost']);
                Route::get('/{post}', [AdminController::class, 'getPost']);
            });

            Route::prefix('faq')->group(function () {
                Route::post('/archive/{faq}', [FaqController::class, 'archive']);
                Route::post('/restore/{faq}', [FaqController::class, 'restore']);
                Route::get('/archive', [FaqController::class, 'getArchive']);
                Route::apiResource('/', FaqController::class);
            });
        });
});

Route::post('/admin/login', [AdminController::class, 'login']);