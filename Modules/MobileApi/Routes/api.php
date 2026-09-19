<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['pong' => true, 'time' => now()->toIso8601String()]))->name('api.ping');

Route::post('auth/login', 'AuthController@login')->name('api.auth.login');

Route::middleware(['auth:sanctum', 'mobile.device'])->group(function () {
    Route::post('auth/logout', 'AuthController@logout')->name('api.auth.logout');
    Route::get('auth/me', 'AuthController@me')->name('api.auth.me');
});
