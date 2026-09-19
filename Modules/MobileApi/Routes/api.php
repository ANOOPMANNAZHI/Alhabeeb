<?php

use Illuminate\Support\Facades\Route;

Route::get('ping', fn () => response()->json(['pong' => true, 'time' => now()->toIso8601String()]))->name('api.ping');

Route::post('auth/login', 'AuthController@login')->name('api.auth.login');

// Signed, unauthenticated download (the app opens PDFs/images in a viewer that cannot send headers)
Route::get('files', 'FileController@show')->name('api.files.show');

Route::middleware(['auth:sanctum', 'mobile.device', 'mobile.log'])->group(function () {
    Route::post('auth/logout', 'AuthController@logout')->name('api.auth.logout');
    Route::get('auth/me', 'AuthController@me')->name('api.auth.me');

    Route::get('complaints', 'ComplaintController@index')->name('api.complaints.index');
    Route::get('complaints/{ticket}', 'ComplaintController@show')->name('api.complaints.show');
    Route::post('complaints/{ticket}/claim', 'ComplaintController@claim')->name('api.complaints.claim');
    Route::post('complaints/{ticket}/check-in', 'ComplaintController@checkIn')->name('api.complaints.check-in');
    Route::post('complaints/{ticket}/notes', 'ComplaintController@note')->name('api.complaints.notes');
    Route::post('complaints/{ticket}/photos', 'ComplaintController@photo')->name('api.complaints.photos');
    Route::post('complaints/{ticket}/items', 'ComplaintController@item')->name('api.complaints.items');
    Route::delete('complaints/{ticket}/items/{item}', 'ComplaintController@removeItem')->name('api.complaints.items.remove');
    Route::post('complaints/{ticket}/status', 'ComplaintController@status')->name('api.complaints.status');
    Route::post('complaints/{ticket}/signature', 'ComplaintController@signature')->name('api.complaints.signature');
    Route::get('complaints/{ticket}/service-report.pdf', 'ComplaintController@pdf')->name('api.complaints.pdf');

    Route::get('terminations', 'TerminationController@index')->name('api.terminations.index');
    Route::get('terminations/{termination}', 'TerminationController@show')->name('api.terminations.show');
    Route::put('terminations/{termination}/inspection', 'TerminationController@saveInspection')->name('api.terminations.inspection');
    Route::post('terminations/{termination}/photos', 'TerminationController@photo')->name('api.terminations.photos');
    Route::post('terminations/{termination}/submit', 'TerminationController@submit')->name('api.terminations.submit');
    Route::get('terminations/{termination}/refer-back', 'TerminationController@referBack')->name('api.terminations.refer-back');
    Route::get('terminations/{termination}/inspection.pdf', 'TerminationController@pdf')->name('api.terminations.pdf');

    Route::get('onboardings', 'OnboardingController@index')->name('api.onboardings.index');
    Route::get('onboardings/{onboarding}', 'OnboardingController@show')->name('api.onboardings.show');
    Route::put('onboardings/{onboarding}/entries', 'OnboardingController@entries')->name('api.onboardings.entries');
    Route::post('onboardings/{onboarding}/photos', 'OnboardingController@photo')->name('api.onboardings.photos');
    Route::post('onboardings/{onboarding}/complete', 'OnboardingController@complete')->name('api.onboardings.complete');
    Route::get('onboardings/{onboarding}/onboarding.pdf', 'OnboardingController@pdf')->name('api.onboardings.pdf');
});
