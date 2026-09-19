<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['pong' => true, 'time' => now()->toIso8601String()]))->name('api.ping');
