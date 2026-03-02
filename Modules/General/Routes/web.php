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

/*Route::prefix('general')->group(function() {
    Route::get('/', 'GeneralController@index');
});*/
/* ---- Jackson -----------  */
Route::group(['middleware' => 'prevent-back-history'],function(){
Route::resource('workFlow', 'WorkFlowController');
Route::post('/workFlowStatus/{id}', 'WorkFlowController@changeStatus')->name('workFlow.changeStatus');

Route::resource('workFlowProcess', 'WorkFlowProcessController');
Route::post('/workFlowProcessStatus/{id}', 'WorkFlowProcessController@changeStatus')->name('workFlowProcess.changeStatus');

Route::resource('action', 'ActionController');
Route::post('/actionStatus/{id}', 'ActionController@changeStatus')->name('action.changeStatus');

Route::resource('processActionLink', 'ProcessActionLinkController');
Route::resource('processAssign', 'ProcessAssignController');

Route::post('processAssign/ajaxStages','ProcessAssignController@ajaxStages')->name('processAssign.ajaxStages');
Route::get('processAssign/processStages/{id}','ProcessAssignController@processStages')->name('processAssign.processStages');

Route::post('processAssign/usersByRoleId','ProcessAssignController@usersByRoleId')->name('processAssign.usersByRoleId');

/* ---- End -----------  */
Route::resource('workFlowCategory', 'WorkFlowCategoryController');
Route::post('/workFlowCategoryStatus/{id}', 'WorkFlowCategoryController@changeStatus')->name('workFlowCategory.changeStatus');
});