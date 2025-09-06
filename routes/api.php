<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth:sanctum')->group(function () {
    //email verified middleware routes
    Route::middleware('verified')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('posts', PostController::class)->except(['index', 'show']);;
    });

    //email verification routes
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.send');
});

Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Auth routes handled by Fortify under web middleware (removed from API)
