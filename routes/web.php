<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/media/{path}', function (string $path) {
    $path = preg_replace('#/+#', '/', ltrim($path, '/'));

    abort_if(str_contains($path, '..'), 404);

    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    return response()->file($disk->path($path), [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');
