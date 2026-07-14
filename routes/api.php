<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\CarCatalogController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFavouritesController;
use App\Http\Middleware\Admin as AdminAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/vincode/{vin}', [UserController::class, 'checkVin']);
Route::get('/faq', [FaqController::class, 'index']);
Route::get('/car-catalog', [CarCatalogController::class, 'index']);
Route::get('/news/slug/{slug}', [NewsController::class, 'showPublishedBySlug']);
Route::get('/news/{news}', [NewsController::class, 'show']);
Route::get('/news', [NewsController::class, 'getPublished']);

Route::middleware('auth:sanctum')->group(function () {
    // Authenticated middleware routes
    Route::get('/user', [UserController::class, 'show']);

    Route::middleware('verified')->group(function () {
        // email verified middleware routes
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);
        Route::post('posts/{post}/archive', [PostController::class, 'archive']);
        Route::post('posts/{post}/restore', [PostController::class, 'restore']);

        Route::prefix('user')->group(function () {
            Route::get('/posts', [UserController::class, 'getPosts']);
            Route::apiResource('favourites', UserFavouritesController::class)->except(['update', 'show']);
            Route::get('archive', [UserController::class, 'getArchivedPosts']);
        });
    });

    // Admin routes
    Route::prefix('admin')
        ->middleware(AdminAuth::class)->group(function () {
            Route::put('/password', [AdminController::class, 'updatePassword']);

            Route::prefix('posts')->group(function () {
                Route::get('/status/{status}', [AdminController::class, 'getPosts']);
                Route::post('/{post}/approve', [AdminController::class, 'approvePost']);
                Route::post('/{post}/reject', [AdminController::class, 'rejectPost']);
                Route::post('/{post}/featured', [AdminController::class, 'toggleFeaturedPost']);
                Route::get('/{post}', [AdminController::class, 'getPost']);
            });

            Route::prefix('faq')->group(function () {
                Route::post('/archive/{faq}', [FaqController::class, 'archive']);
                Route::post('/restore/{faq}', [FaqController::class, 'restore']);
                Route::get('/archive', [FaqController::class, 'getArchive']);
                Route::apiResource('/', FaqController::class);
            });

            Route::prefix('news')->group(function () {
                Route::get('/', [NewsController::class, 'index']);
                Route::post('/', [NewsController::class, 'store']);
                Route::put('/{news}', [NewsController::class, 'update']);
                Route::delete('/{news}', [NewsController::class, 'destroy']);
                Route::post('/{news}/publish', [NewsController::class, 'publish']);
                Route::post('/{news}/unpublish', [NewsController::class, 'unpublish']);
            });

            Route::prefix('car-catalog')->group(function () {
                Route::get('/', [CarCatalogController::class, 'adminIndex']);
                Route::post('/makes', [CarCatalogController::class, 'storeMake']);
                Route::put('/makes/{make}', [CarCatalogController::class, 'updateMake']);
                Route::delete('/makes/{make}', [CarCatalogController::class, 'destroyMake']);
                Route::post('/makes/{make}/models', [CarCatalogController::class, 'storeModel']);
                Route::put('/models/{model}', [CarCatalogController::class, 'updateModel']);
                Route::delete('/models/{model}', [CarCatalogController::class, 'destroyModel']);
            });
        });
});

Route::post('/admin/login', [AdminController::class, 'login']);
