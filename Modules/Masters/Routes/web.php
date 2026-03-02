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

/*Route::prefix('masters')->group(function() {
    Route::get('/', 'MastersController@index');

});*/
Route::group(['middleware' => 'prevent-back-history'],function(){
Route::resource('buildingType', 'BuildingTypeController');
Route::post('/buildingTypeStatus/{id}', 'BuildingTypeController@changeStatus')->name('buildingType.changeStatus');


Route::resource('unitType', 'UnitTypeController');
Route::post('/unitTypeStatus/{id}', 'UnitTypeController@changeStatus')->name('unitType.changeStatus');

Route::resource('tenantStatus', 'TenantStatusController');
Route::post('/tenantStatusStatus/{id}', 'TenantStatusController@changeStatus')->name('tenantStatus.changeStatus');

Route::resource('tenantType', 'TenantTypeController');
Route::post('/tenantTypeStatus/{id}', 'TenantTypeController@changeStatus')->name('tenantType.changeStatus');

Route::resource('amentityType', 'AmentityTypeController');
Route::post('/amentityTypeStatus/{id}', 'AmentityTypeController@changeStatus')->name('amentityType.changeStatus');

Route::resource('work', 'WorkController');
Route::post('/workStatus/{id}', 'WorkController@changeStatus')->name('work.changeStatus');

Route::resource('reason', 'ReasonController');
Route::post('/reasonStatus/{id}', 'ReasonController@changeStatus')->name('reason.changeStatus');

Route::resource('location', 'LocationController');
Route::post('/locationStatus/{id}', 'LocationController@changeStatus')->name('location.changeStatus');

Route::post('/regionStatus/{id}', 'RegionController@changeStatus')->name('region.changeStatus');

Route::resource('country', 'CountryController');
Route::post('/countryStatus/{id}', 'CountryController@changeStatus')->name('country.changeStatus');
Route::resource('jobCategory', 'JobCategoryController');
Route::post('/jobCategoryStatus/{id}', 'JobCategoryController@changeStatus')->name('jobCategory.changeStatus');

Route::resource('currency', 'CurrencyController');
Route::post('/currencyStatus/{id}', 'CurrencyController@changeStatus')->name('currency.changeStatus');

Route::resource('workLink', 'ContractorWorkLinkController');
Route::post('/workLinkStatus/{id}', 'ContractorWorkLinkController@changeStatus')->name('workLink.changeStatus');

Route::resource('bank', 'BankController');
Route::post('/bankStatus/{id}', 'BankController@changeStatus')->name('bank.changeStatus');

Route::resource('homeUtility', 'HomeUtilityController');
Route::post('/homeUtilityStatus/{id}', 'HomeUtilityController@changeStatus')->name('homeUtility.changeStatus');

Route::resource('managementType', 'ManagementTypeController');
Route::post('/managementTypeStatus/{id}', 'ManagementTypeController@changeStatus')->name('managementType.changeStatus');

Route::resource('invoiceType', 'InvoiceTypeController');
Route::post('/invoiceTypeStatus/{id}', 'InvoiceTypeController@changeStatus')->name('invoiceType.changeStatus');

Route::resource('paymentMethod', 'PaymentMethodController');
Route::post('/paymentMethodStatus/{id}', 'PaymentMethodController@changeStatus')->name('paymentMethod.changeStatus');

Route::resource('enquirySource', 'EnquirySourceController');
Route::post('/enquirySourceStatus/{id}', 'EnquirySourceController@changeStatus')->name('enquirySource.changeStatus');

Route::resource('vendorType', 'VendorTypeController');
Route::post('/vendorTypeStatus/{id}', 'VendorTypeController@changeStatus')->name('vendorType.changeStatus');
//vendor code exist
Route::post('vendors/checkVendorCodeExist','VendorController@checkVendorCodeExist')->name('checkVendorCodeExist');

Route::resource('inventory', 'InventoryController');

Route::resource('priceRange', 'PriceRangeController');
Route::post('/priceRangeStatus/{id}', 'PriceRangeController@changeStatus')->name('priceRange.changeStatus');


/* ---- Jackson -----------  */
Route::resource('complaintReason', 'ComplaintReasonController');
Route::post('/complaintReasonStatus/{id}', 'ComplaintReasonController@changeStatus')->name('complaintReason.changeStatus');

Route::resource('designation', 'DesignationController');
Route::post('/designationStatus/{id}', 'DesignationController@changeStatus')->name('designation.changeStatus');

Route::resource('employee', 'EmployeeController');
Route::post('/employeeStatus/{id}', 'EmployeeController@changeStatus')->name('employee.changeStatus');
Route::get('/employeeResetPass/{id}', 'EmployeeController@resetPassword')->name('employee.resetPassword');

 Route::post('deleteEmployeeImage','EmployeeController@deleteEmployeeImage')->name('deleteEmployeeImage');
/* ---- End -----------  */

//Location Autocomplete
Route::get('/locationAutocomplete', 'LocationController@locationAutocomplete')->name('location.locationAutocomplete');
//Location Autocomplete
Route::get('/locationsAutocomplete', 'LocationController@locationsAutocomplete')->name('locationsAutocomplete');
//Building Autocomplete
Route::get('/buildingAutocomplete', 'BuildingController@buildingAutocomplete')->name('buildingAutocomplete');
//Building with ajax-division
Route::post('/ajaxBuildingWithAxDivision', 'BuildingController@ajaxBuildingWithAxDivision')->name('ajaxBuildingWithAxDivision');

//Building Autocomplete
Route::get('/buildingAutocompleteByUnit', 'BuildingController@buildingAutocompleteByUnit')->name('buildingAutocompleteByUnit');
//Building Autocomplete to code
Route::get('/buildingAutocompleteCode', 'BuildingController@buildingAutocompleteCode')->name('buildingAutocompleteCode');
//Vendors
Route::post('/vendorStatus/{vendor}', 'VendorController@changeStatus')->name('vendors.changeStatus');
Route::resource('vendors', 'VendorController');


//Building
Route::post('/buildingStatus/{building}', 'BuildingController@changeStatus')->name('building.changeStatus');
Route::resource('building', 'BuildingController');
Route::post('/updateUnitTypeCount', 'BuildingController@updateUnitTypeCount')->name('building.updateUnitTypeCount'); 
//Unit
//qrCodeGenerator
Route::get('/qrCodeGenerator/{unit_id}', 'UnitController@qrCodeGenerator')->name('qrCodeGenerator');
//qrCodeScanner
Route::post('/qrCodeScanner', 'UnitController@qrCodeScanner')->name('qrCodeScanner');
Route::post('/unitStatus/{unit}', 'UnitController@changeStatus')->name('unit.changeStatus');
Route::post('/unit/ajaxFloorList','UnitController@ajaxFloorList')->name('unit.ajaxFloorList');
Route::resource('unit', 'UnitController');
//Existing Unit view
Route::post('viewExistingUnit', 'UnitController@viewExistingUnit')->name('viewExistingUnit');
Route::post('/unitUtilities', 'UnitController@utilitiesUpdate')->name('unit.utilitiesUpdate');

Route::resource('unit-utility', 'UnitUtilityController');
Route::post('building-amentity-unique', 'BuildingAmentityController@buildingAmentityUnique')->name('buildingAmentityUnique');
Route::resource('building-amentity', 'BuildingAmentityController');
Route::resource('building-insurance', 'BuildingInsuranceController');
Route::delete('destroyImage/{id}','BuildingController@destroyImage');
Route::delete('destroyEleReading/{id}','BuildingController@destroyEleReading');

Route::resource('subWork', 'SubWorkController');
/* Are Building assign starts*/
Route::resource('areBuildingAssign', 'AreBuildingAssignController');
//ARE autocomplete
Route::get('/areAutocompleteCode','AreBuildingAssignController@areAutocompleteCode')->name('areAutocompleteCode');
//search
Route::match(['get', 'post'],'areBuildingSearch/areBuildingAssign', 'AreBuildingAssignController@areBuildingSearch')->name('areBuildingSearch');

Route::get('areBuilding/enquiry_filter', 'AreBuildingAssignController@enquiryFilter')->name('areBuildingAssignFilter');
Route::match(['get', 'post'],'groupAreList', 'AreBuildingAssignController@groupAreList')->name('group_are_list');
Route::get('groupView/groupAreList/{id}', 'AreBuildingAssignController@groupView')->name('groupView');
Route::resource('tenancyDetails','TenancyDetailsController');

//search
Route::match(['get', 'post'],'TenancyDetailsSearch/tenancyDetails', 'TenancyDetailsController@TenancySearch')->name('TenancyDetailsSearch');

//search-tenancy details
Route::get('tenancy_details/enquiry_filter', 'TenancyDetailsController@enquiryFilter')->name('tenancyDetailsFilter');

/* Are Building assign ends*/
/* Legal starts*/
/*** approval List ****/
Route::get('legalCase/plmsApproval', 'LegalController@plmsApproval')->name('plmsApproval');
Route::get('legalCase/lawyerApproval', 'LegalController@lawyerApproval')->name('lawyerApproval');
Route::get('legalCase/activeCases', 'LegalController@activeCases')->name('activeCases');
Route::resource('legalCase','LegalController');
//search
Route::match(['get', 'post'],'legalCaseSearch/legalCase', 'LegalController@legalSearch')->name('legalCaseSearch');
//search-legal case
Route::get('legal_case/enquiry_filter', 'LegalController@enquiryFilter')->name('legalCaseFilter');

//legal are stage
Route::post('/legalCase/areStage', 'LegalController@legalAreStage')->name('legalAreStage');
Route::post('/legalCase/areStageStore', 'LegalController@areStageStore')->name('areStageStore');
/*****    closed legal cases  starts****/
Route::get('/closedLegalCases', 'LegalController@closedLegalCases')->name('closedLegalCases');
Route::get('closedLegalCases/{id}', 'LegalController@closedLegalCasesShow')->name('closedLegalCasesShow');

/*****    closed legal cases ends  ****/
/*****    referback legal cases  starts****/
Route::get('/referBackCases', 'LegalController@referBackCases')->name('referBackCases');
Route::get('referBackCases/{id}', 'LegalController@referBackCasesShow')->name('referBackCasesShow');
/*****    referback legal cases ends  ****/

/*Plms approval */
Route::get('plmsApproval/{id}', 'LegalController@plmsApprovalShow')->name('plmsApprovalShow');

Route::match(['get', 'post'],'plmsApprovalSearch/plmsApproval', 'LegalController@plmsSearch')->name('plmsApprovalSearch');

//plms approve 
Route::post('/plmsApproval/approveReferBack', 'LegalController@plmsApproveReferBack')->name('plmsApproveReferBack');
Route::post('/plmsApproval/approveReferBackStore', 'LegalController@plmsApproveReferBackStore')->name('plmsApproveReferBackStore');
//plms close
Route::post('/plmsApproval/legalCaseClose', 'LegalController@legalCaseClose')->name('legalCaseClose');


/* lawyer approval */
Route::match(['get', 'post'],'lawyerApprovalSearch/lawyerApproval', 'LegalController@lawyerSearch')->name('lawyerApprovalSearch');
/*lawyer approval view*/
Route::get('lawyerApproval/{id}', 'LegalController@lawyerApprovalShow')->name('lawyerApprovalShow');


/* Active cases*/
Route::get('activeCases/{id}', 'LegalController@activeCasesShow')->name('activeCasesShow');
/* lawyer approval */
Route::match(['get', 'post'],'activeCasesSearch/lawyerApproval', 'LegalController@casesSearch')->name('activeCasesSearch');
//active case legal note store
Route::post('/activeCases/legalNoteStore', 'LegalController@legalNoteStore')->name('legalNoteStore');
Route::post('/activeCases/legalDocumentStore', 'LegalController@legalDocumentStore')->name('legalDocumentStore');
/* Pdc Show*/
Route::get('legalPdcShow/{id}', 'LegalController@legalPdcShow')->name('legalPdcShow');
Route::get('legaltenantContractShow/{id}', 'LegalController@legaltenantContractShow')->name('legaltenantContractShow');
/* Legal ends*/


Route::post('unitFilter', 'UnitController@unitFilter')->name('unitFilter');
Route::post('buildingFilter', 'BuildingController@buildingFilter')->name('buildingFilter');


/* Report Starts*/
Route::get('showBuildingDetails', 'MasterReportController@showBuildingDetails')->name('showBuildingDetails');

Route::post('showBuildingDetailsReport', 'MasterReportController@showBuildingDetailsReport')->name('showBuildingDetailsReport');

Route::get('showBuildingUnitDetails', 'MasterReportController@showBuildingUnitDetails')->name('showBuildingUnitDetails');
Route::get('landlordReportAutocompleteCode', 'MasterReportController@landlordReportAutocompleteCode')->name('landlordReportAutocompleteCode');
Route::get('locationReportAutocompleteCode', 'MasterReportController@locationReportAutocompleteCode')->name('locationReportAutocompleteCode');


Route::post('showBuildingUnitDetailsReport', 'MasterReportController@showBuildingUnitDetailsReport')->name('showBuildingUnitDetailsReport');
Route::get('buildingReportAutocompleteCode', 'MasterReportController@buildingReportAutocompleteCode')->name('buildingReportAutocompleteCode');
Route::get('buildingCodeReportAutocompleteCode', 'MasterReportController@buildingCodeReportAutocompleteCode')->name('buildingCodeReportAutocompleteCode');

Route::get('showFurnishedUnitReport', 'MasterReportController@showFurnishedUnitReport')->name('showFurnishedUnitReport');
Route::post('furnishedUnitReportPdf', 'MasterReportController@furnishedUnitReportPdf')->name('furnishedUnitReportPdf');

Route::get('furnishedBuildingReportAutocompleteCode', 'MasterReportController@furnishedBuildingReportAutocompleteCode')->name('furnishedBuildingReportAutocompleteCode');

Route::get('showVacancyLossReport', 'MasterReportController@showVacancyLossReport')->name('showVacancyLossReport');

Route::post('vacancyLossReportPdf', 'MasterReportController@vacancyLossReportPdf')->name('vacancyLossReportPdf');

Route::get('showVacantUnitReport', 'MasterReportController@showVacantUnitReport')->name('showVacantUnitReport');

Route::post('vacantUnitReportPdf', 'MasterReportController@vacantUnitReportPdf')->name('vacantUnitReportPdf');

Route::get('showTenantDetailsReport', 'MasterReportController@showTenantDetailsReport')->name('showTenantDetailsReport');

Route::post('tenantDetailsReportPdf', 'MasterReportController@tenantDetailsReportPdf')->name('tenantDetailsReportPdf');

Route::get('buildingCodesReportAutocompleteCode', 'MasterReportController@buildingCodesReportAutocompleteCode')->name('buildingCodesReportAutocompleteCode');
/* Report Ends*/

});

