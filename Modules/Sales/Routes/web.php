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
Route::prefix('sales')->group(function() {
    Route::get('/', 'SalesController@index');
});


Route::get('call-center', 'SalesEnquiryController@callCenter')->name('callCenter');

/*Sales Report Routes Start's */

Route::get('showSalesEnquiries', 'SalesReportController@showSalesEnquiries')->name('showSalesEnquiries');
Route::post('salesEnquiryReportPdf', 'SalesReportController@salesEnquiryReportPdf')->name('salesEnquiryReportPdf');


/*Sales Report Routes end's */

Route::get('enquiry_filter', 'SalesEnquiryController@enquiryFilter')->name('enquiryFilter');

Route::match(['get', 'post'],'enquiry/tenant', 'SalesEnquiryController@tenantEnquiry')->name('tenantEnquiries');
Route::match(['get', 'post'],'enquiry/landlord', 'SalesEnquiryController@landlordEnquiry')->name('landlordEnquiries');
Route::post('enquiry/tenantSearchByMobile', 'SalesEnquiryController@tenantSearchByMobile')->name('tenantSearchByMobile');
Route::post('enquiry/landlordSearchByMobile', 'SalesEnquiryController@landlordSearchByMobile')->name('landlordSearchByMobile');
Route::match(['get', 'post'],'enquiry/enquiryLandlordSearch', 'SalesEnquiryController@enquiryLandlordSearch')->name('enquiryLandlordSearch');
Route::match(['get', 'post'],'enquiry/enquiryTenantSearch', 'SalesEnquiryController@enquiryTenantSearch')->name('enquiryTenantSearch');

Route::get('enquiryReminder/{enquiry}', 'SalesEnquiryController@enquiryReminder')->name('enquiry.reminder');

Route::resource('enquiry', 'SalesEnquiryController');
Route::get('enquiryEditPopup/{id}', 'SalesEnquiryController@enquiryEditPopup')->name('enquiry.enquiryEditPopup');
Route::post('enquiryEditPopupAction', 'SalesEnquiryController@enquiryEditPopupAction')->name('enquiry.enquiryEditPopupAction');

/* Tenant Stages */
Route::match(['get', 'post'],'/leadAssign/inprogressList', 'TenantStageController@inprogressList')->name('inprogressList');
Route::match(['get', 'post'],'/leadAssign/documentationList', 'TenantStageController@documentationList')->name('documentationList');
Route::post('/leadAssign/documentation', 'TenantStageController@documentationStage')->name('documentationStage');
Route::match(['get', 'post'],'/leadAssign/preliminaryApprovalList', 'TenantStageController@preliminaryApprovalList')->name('preliminaryApprovalList');
Route::post('/leadAssign/preliminaryApprovalAccept', 'TenantStageController@approvalAccept')->name('approvalAccepts');
/*multiple document/ contract accept -reject*/
Route::post('/accept-reject', 'TenantStageController@acceptReject')->name('accept-reject');
// Jackson
Route::match(['get', 'post'],'/leadAssign/finalDocPendingApprovalList', 'TenantStageController@finalDocPendingApprovalList')->name('finalDocPendingApprovalList');


Route::match(['get', 'post'],'/leadAssign/finalDocumentationList', 'TenantStageController@finalDocumentationList')->name('finalDocumentationList');
Route::match(['get', 'post'],'/leadAssign/finalApproval', 'TenantStageController@finalApprovalList')->name('finalApprovalList');
Route::match(['get', 'post'],'/leadAssign/wonList', 'TenantStageController@wonList')->name('wonList');
Route::match(['get', 'post'],'/leadAssign/closedList', 'TenantStageController@closedList')->name('closedList');
Route::post('/leadSearch', 'TenantStageController@search')->name('leadAssign.search');
Route::match(['get', 'post'],'/leadAssignedList', 'TenantStageController@assignedList')->name('leadAssign.assignedList');
Route::match(['get', 'post'],'/leadAssign/AssignedList', 'TenantStageController@salesAssignedList')->name('salesAssignedList');
/*Route::post('/assignedsearch', 'TenantStageController@assignedsearch')->name('leadAssign.assignedsearch');*/
// Pending details info 
Route::get('/finalDocPendingApprovalList/{id}/{stage}/tenantPendingStageInfo', 'TenantStageController@tenantPendingStageInfo')->name('leadAssign.tenantPendingStageInfo');

Route::get('/tenantNextstage/{id}/{stage}', 'TenantStageController@nextStage')->name('leadAssign.nextStage');

Route::get('leadAssign/assignModal/{id}/{stage}/{page}', 'TenantStageController@assignModal')->name('leadAssign.assignModal');
Route::post('/storeSalesNote/', 'TenantStageController@storeSalesNote')->name('leadAssign.storeSalesNote');

Route::post('/leadAssign/moveNextStage', 'TenantStageController@moveNextStage')->name('moveNextStage');
Route::get('leadAssign/re-assign/{id}/{stage}/{page}', 'TenantStageController@reAssignModal')->name('reAssignModal');
Route::post('/leadAssign/reAssign', 'TenantStageController@reAssign')->name('reAssign');
Route::get('leadAssign/re-open/{id}/{stage}', 'TenantStageController@reOpenModal')->name('reOpenModal');
Route::post('/leadAssign/reOpen', 'TenantStageController@reOpen')->name('reOpen');
Route::post('/leadAssign/reAssignPrevious', 'TenantStageController@reAssignPrevious')->name('reAssignPrevious');
//Group Reassign 
Route::post('groupReAssignModal', 'TenantStageController@groupReAssignModal')->name('groupReAssignModal');
Route::post('leadAssign/storeGroupReAssign', 'TenantStageController@storeGroupReAssign')->name('storeGroupReAssign');
//end
// jackson
Route::post('leadAssign/groupAssignModal', 'TenantStageController@groupAssignModal')->name('leadAssign.groupAssignModal');
Route::post('leadAssign/storeGroupAssign', 'TenantStageController@storeGroupAssign')->name('leadAssign.storeGroupAssign');
// end
Route::match(['get', 'post'],'tenantUnassignedSearch', 'TenantStageController@tenantUnassignedSearch')->name('leadAssign.tenantUnassignedSearch');
Route::get('assignReminder/{salesEnquiry}', 'TenantStageController@assignReminder')->name('leadAssign.reminder');


Route::resource('tenants', 'TenantController');
Route::post('/tenantsStatus/{id}', 'TenantController@changeStatus')->name('tenants.changeStatus');
//Tenant Autocomplete
Route::get('/tenantsAutocomplete', 'TenantController@tenantsAutocomplete')->name('tenantsAutocomplete');
//Nationality Autocomplete
Route::get('/nationalityAutocomplete', 'TenantController@nationalityAutocomplete')->name('nationalityAutocomplete');
//mobile no exist
Route::post('tenants/checkMobileExist','TenantController@checkMobileExist')->name('checkMobileExist');

//Residence ID exist
Route::post('tenants/checkResidenceExist','TenantController@checkResidenceExist')->name('checkResidenceExist');

// Commercial exist
Route::post('tenants/checkCommercialExist','TenantController@checkCommercialExist')->name('checkCommercialExist');


/*Nationality Details*/
Route::post('/nationalityDetails/', 'TenantContractController@nationalityDetails')->name('nationalityDetails');
Route::resource('leadAssign', 'TenantStageController');

/* Tenant Contract*/
Route::get('/tenant/documentation/{id}', 'TenantContractController@contractDocumentation')->name('contractDocumentation');
Route::get('/tenantContract/contractCreation/{id}/{stage}', 'TenantContractController@contractCreation')->name('contractCreation');
Route::post('/tenantContract/contractStore/{tenantContract}', 'TenantContractController@contractStore')->name('contractStore');
Route::post('tenantContract/buildingByUnit','TenantContractController@buildingByUnit')->name('buildingByUnit');
Route::post('tenantContract/buildingByUnitWithoutCheckVaccant','TenantContractController@buildingByUnitWithoutCheckVaccant')->name('buildingByUnitWithoutCheckVaccant');
Route::post('tenantContract/buildingByUnitOccuiped','TenantContractController@buildingByUnitOccuiped')->name('buildingByUnitOccuiped');
Route::post('tenantContract/buildingDetail','TenantContractController@buildingDetail')->name('buildingDetail');
Route::post('tenantContract/unitDetail','TenantContractController@unitDetail')->name('unitDetail');
Route::post('tenantContract/getBuildingDetail','TenantContractController@getBuildingDetail')->name('getBuildingDetail');
Route::post('tenants/tenantDetail','TenantController@tenantDetails')->name('tenantDetail');

Route::resource('tenantContract', 'TenantContractController');



/* tenant Contract Delete*/
Route::delete('contractDestroy/{id}','TenantContractController@contractDestroy')->name('contractDestroy');
/* Flow Wise Users list based roles*/
Route::post('usersListByRole','TenantStageController@usersListByRole')->name('usersListByRole');

/* Flow Wise Users list based roles*/
Route::post('usersByRole','TenantStageController@usersByRole')->name('usersByRole');
/* Tenant Activity*/
Route::get('/salesActivities/activities/{id}', 'TenantSalesActivityController@salesActivity')->name('salesActivity');
Route::resource('salesActivities', 'TenantSalesActivityController');

/* Land loard Stages*/
Route::match(['get', 'post'],'landlordSearch', 'LandlordStageController@landlordSearch')->name('landlord.landlordSearch');
Route::resource('landlordLeadAssign', 'LandlordStageController');
Route::resource('landloardLeadAssign', 'LandlordStageController');

Route::get('/landlordPendingApproval', 'LandlordStageController@landlordPendingApproval')->name('landlordPendingApproval');

Route::post('/landlordLeadSearch', 'LandlordStageController@search')->name('landlordLeadAssign.search');
Route::post('/landlordClose/{id}', 'LandlordStageController@close')->name('landlordLeadAssign.close');

Route::get('/landlordNextstage/{id}/{stage}', 'LandlordStageController@nextStage')->name('landlordLeadAssign.nextStage');

Route::post('/landlordNextstageAction', 'LandlordStageController@nextStageAction')->name('nextStageAction');
Route::post('landlordClose', 'LandlordStageController@approvalAccept')->name('approvalAccept');

// Jackson
Route::post('landlordAjaxSearch', 'LandlordStageController@landlordAjaxSearch')->name('landlordAjaxSearch');
Route::match(['get', 'post'],'landlordContractSearch', 'LandlordContractController@landlordContractSearch')->name('landlord.landlordContractSearch');
Route::resource('landlordLead', 'LandlordStageController');
Route::resource('landlordContract', 'LandlordContractController');
Route::get('enquiry/landlordContract/{id}', 'LandlordContractController@landlordEnquiryView')->name('landlordEnquiryView');
Route::post('landlordDraftContract', 'LandlordContractController@draftContract')->name('draftContract');
Route::post('vendorNameAjax', 'LandlordContractController@vendorNameAjax')->name('vendorNameAjax');
Route::post('vendorNameAjaxCode', 'LandlordContractController@vendorNameAjaxCode')->name('vendorNameAjaxCode');
Route::post('buildingNameAjax', 'LandlordContractController@buildingNameAjax')->name('buildingNameAjax');
Route::post('buildingNameCodeAjax', 'LandlordContractController@buildingNameCodeAjax')->name('buildingNameCodeAjax');
Route::get('/contractGeneration/{id}', 'LandlordContractController@contractGeneration')->name('contractGeneration');
Route::post('landlordPopup', 'LandlordContractController@landlordPopup')->name('landlordPopup');
Route::post('landlordPopupAction', 'LandlordContractController@landlordPopupAction')->name('landlordPopupAction');
Route::post('buildingPopup', 'LandlordContractController@buildingPopup')->name('buildingPopup');
Route::post('buildingPopupAction', 'LandlordContractController@buildingPopupAction')->name('buildingPopupAction');
Route::post('landlordContractAction', 'LandlordContractController@landlordContractAction')->name('landlordContractAction');
Route::match(['get', 'post'],'landlordcontractApprovalList', 'LandlordContractController@contractApprovalList')->name('contractApprovalList');
Route::get('landlordcontractApprovalInfo/{id}/{stage}', 'LandlordContractController@contractApprovalListInfo')->name('contractApprovalListInfo');
Route::get('landlordPendingApproval/{id}/{stage}/contractApprovalListPendingInfo', 'LandlordContractController@contractApprovalListPendingInfo')->name('contractApprovalListPendingInfo');
Route::match(['get', 'post'],'landlordContractWon', 'LandlordContractController@contractApprovalWon')->name('contractApprovalWon');
Route::match(['get', 'post'],'landlordContractLoss', 'LandlordContractController@contractApprovalLoss')->name('contractApprovalLoss');
Route::get('landLordLeadAssign/re-assign/{id}/{stage}', 'LandlordStageController@reAssignLandlordModal')->name('reAssignLandlordModal');
Route::post('/leadAssign/reAssignLandlord', 'LandlordStageController@reAssignLandlord')->name('reAssignLandlord');

//Landlord Autocomplete landlordAutocompleteCode
Route::get('/landlordAutocomplete', 'LandlordContractController@landlordAutocomplete')->name('landlordAutocomplete');
//Landlord Autocomplete  code
Route::get('/landlordAutocompleteCode', 'LandlordContractController@landlordAutocompleteCode')->name('landlordAutocompleteCode');


/*Route::get('testNotification', 'TenantStageController@testNotification');*/
});

