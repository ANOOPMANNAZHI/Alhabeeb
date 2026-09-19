<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:mobile_devices_manage'])->group(function () {
    Route::get('mobileDevices', 'MobileDeviceAdminController@index')->name('mobile-devices.index');
    Route::post('mobileDevices/{user}/clear', 'MobileDeviceAdminController@clear')->name('mobile-devices.clear');
    Route::post('mobileDevices/{user}/block', 'MobileDeviceAdminController@block')->name('mobile-devices.block');
    Route::post('mobileDevices/{user}/unblock', 'MobileDeviceAdminController@unblock')->name('mobile-devices.unblock');
});
