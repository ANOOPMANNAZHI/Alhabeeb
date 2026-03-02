<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::prefix('menu')->group(function() {
//     Route::get('/', 'MenuController@index');
// });
Route::group(['middleware' => 'prevent-back-history'],function(){
 Route::get('menuOrder', 'MenuController@menuOrderUpdate')->name('menuOrder');
 Route::get('submenu/{menu}', 'MenuController@menuList')->name('menu.menus');

 //Menu 
 Route::resource('menu', 'MenuController');
 //Permission  Key      
 Route::resource('menu.permission', 'PermissionController'); 
 //Role 
 Route::resource('role', 'RoleController');  

  Route::get('setPermission/{role}', 'RoleController@setPermission')->name('setPermission');
  Route::post('storePermission/{role}', 'RoleController@storePermission')->name('storePermissions');    
  Route::post('menuPermissions/{menu}', 'RoleController@menuPermissions')->name('menuPermissions');    
    /* -------------- SIdebar user level setting Open or Close ........ */
  Route::post('sidebarSetting', 'MenuController@sidebarSetting');  
});
