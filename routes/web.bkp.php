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
Route::group(['middleware' => 'prevent-back-history'],function(){
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::fallback('HomeController@notFound');

Route::get('/notificationTest', 'HomeController@notificationTest');


Route::get('/', 'HomeController@dashboard')->name('home');
Route::get('/dashboard/{role?}', 'HomeController@dashboard')->name('dashboard');
Route::get('/changePassword', 'HomeController@changePassword')->name('change_password');
Route::post('/updatePassword', 'HomeController@updatePassword')->name('updatePassword');
Route::get('/profile', 'HomeController@profileView')->name('profileView');
Route::resource('settings', 'SettingsController');
Route::resource('logs', 'LogsController');
Route::post('/ajaxDashboardCount', 'HomeController@dashboardAjaxCount')->name('ajaxDashboardCount');
 Route::get('/createRole', 'HomeController@createRole');
 Route::get('tenant-contract/download/{file}/{docType}', 'FileDownloadController@download')->name('tenantContractDownload');

 Route::get('/menuHomesearch', 'HomeController@menuHomesearch')->name('menuHomesearch');
 Route::get('/soapApiAction', 'SoapController@soapApiAction')->name('soapApiAction');
 //Are
Route::get('receivables/{days?}','DashboardController@receivables')->name('receivables');
   Route::get('averageReceivables','DashboardController@averageReceivables')->name('averageReceivables');
Route::get('averageReceivableUnit','DashboardController@averageReceivableUnit')->name('averageReceivableUnit');
   Route::get('totalReceivables','DashboardController@totalReceivables')->name('totalReceivables');
   Route::get('contractsExpiring','DashboardController@contractsExpiring')->name('contractsExpiring');
   Route::get('expiring/{expdays}','DashboardController@expiring')->name('expiring');
   Route::get('pendingApprovals','DashboardController@pendingApprovals')->name('pendingApprovals');

    //Maintanance engineer
   Route::get('openAssignedComplaint','DashboardController@openAssignedComplaint')->name('openAssignedComplaint');
   Route::get('openAssignedComplaintSupervisor','DashboardController@openAssignedComplaintSupervisor')->name('openAssignedComplaintSupervisor');
   Route::get('openAssignedComplaintSubcontractor','DashboardController@openAssignedComplaintSubcontractor')->name('openAssignedComplaintSubcontractor');
    Route::get('complaintReviewCount','DashboardController@complaintReviewCount')->name('complaintReviewCount');

   Route::post('sales_coordinator_dashboard','DashboardController@salesCoordinatorDashboard')->name('sales-cordinator-dashboard'); 
  
   Route::post('sales_person_dashboard','DashboardController@salesPersonDashboard')->name('sales_person_dashboard'); 
   Route::post('sales_head_dashboard','DashboardController@salesHeadDashboard')->name('sales-head-dashboard'); 
   Route::get('vacancy_loss_list/{status}','HomeController@vacancyLossList')->name('vacancy-loss-list');    

   Route::post('ceo_dashboard','DashboardController@ceoDashboard')->name('ceo_dashboard'); 
   
   Route::get('takenOverNotdone_facility_manager','DashboardController@takenOverNotdoneForFacilityManager')->name('takenOverNotdoneForFacilityManager');

   //backoffice manager

   Route::get('tenantVaccating','DashboardController@tenantVaccating')->name('tenantVaccating'); 
     #region MD Dashboard-starts
	Route::post('md_dashboard','DashboardController@mdDashboard')->name('md-dashboard'); 
    Route::get('buildingList','MD_DashboardController@buildingList')->name('buildingList');
    Route::get('vacatedUnitsMTDCount','MD_DashboardController@vacatedUnitsMTDCount')->name('vacatedUnitsMTDCount'); 
    Route::get('vacatedUnitsMTDList','MD_DashboardController@vacatedUnitsMTDList')->name('vacatedUnitsMTDList');
    Route::get('comprehensiveUnits','MD_DashboardController@comprehensiveUnits')->name('comprehensiveUnits');
    Route::get('normalUnits','MD_DashboardController@normalUnits')->name('normalUnits');
    Route::get('unassignedEnquiry','MD_DashboardController@unassignedEnquiry')->name('unassignedEnquiry');
    Route::get('getBouncedChequesMTD','MD_DashboardController@getBouncedChequesMTD')->name('getBouncedChequesMTD');
    Route::get('caseStatusAccordingToLawyer', 'MD_DashboardController@caseStatusAccordingToLawyer')->name('caseStatusAccordingToLawyer');
    Route::get('vacantUnitMovementSc/{type?}','DashboardController@getVacantUnitMovementSchedule')->name('vacantUnitMovementSc'); 
	
   #endregion MD Dashboard-ends


});
