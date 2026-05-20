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
Route::prefix('maintenance')->group(function() {
    Route::get('/', 'MaintenanceController@index');
});
//Search
Route::get('complaint/enquiry_filter', 'MaintenanceController@enquiryFilter')->name('maintenanceEnquiryFilter');
//ticket Modal edit
Route::post('complaint/ticketEdit', 'MaintenanceController@ticketEdit')->name('ticketEdit');
Route::post('complaint/buildingByUnitOccuiped','MaintenanceController@buildingByUnitOccuiped')->name('complaintBuildingByUnitOccuiped');
Route::post('complaint/getBuildingDetail','MaintenanceController@getBuildingDetail')->name('complaintGetBuildingDetail');
//Building Autocomplete
Route::get('/complaintBuildingAutocomplete', 'MaintenanceController@buildingAutocomplete')->name('complaintBuildingAutocomplete');
Route::match(['get', 'post'],'complaintSearch/complaint', 'MaintenanceController@complaintSearch')->name('complaintSearch');
Route::match(['get', 'post'],'complaintUnassignedSearch/complaintStage', 'ComplaintStageController@complaintUnassignedSearch')->name('complaintUnassignedSearch');
//Complaint enquiry Remainder
Route::get('complaintReminder/{enquiry}', 'MaintenanceController@enquiryReminder')->name('complaint.reminder');
//Complaints
Route::resource('complaint', 'MaintenanceController');
//Get Contract Details populate
Route::post('complaint/combinationSearchMaintenance','MaintenanceController@combinationSearchMaintenance')->name('combinationSearchMaintenance');


Route::post('complaint/contractDetails','MaintenanceController@contractSearchByMobile')->name('maintenanceContractDetails');
//Get tenant & contract details by building unit change
Route::post('complaint/contractDetailsByBuildingUnit','MaintenanceController@contractDetailsByBuildingUnit')->name('contractDetailsByBuildingUnit');
//tickets
Route::post('complaint/subComplaint','MaintenanceController@addSubComplaint')->name('addSubComplaint');
//tickets update
Route::post('complaint/updateSubComplaint','MaintenanceController@updateSubComplaint')->name('updateSubComplaint');
//Get occupant Details populate
Route::post('complaint/occupantDetails','MaintenanceController@contractSearchByOccupant')->name('contractSearchByOccupant');
//Checklist edit
Route::post('complaint/checklistEdit', 'MaintenanceController@checklistEdit')->name('checklistEdit');
//Checklist Update
Route::put('/complaint/checklistUpdate/{complaintChecklist}', 'MaintenanceController@checklistUpdate')->name('checklistUpdate');
Route::get('complaintStage/signature', 'ComplaintStageController@signature')->name('signature');
//Assigned list
Route::match(['get', 'post'],'complaintStage/AssignedList/', 'ComplaintStageController@assignedList')->name('complaintAssignedList');
// Un assigned View
Route::get('/complaintStage/{id}/UnAssignedList', 'ComplaintStageController@unAssignedView')->name('complaintUnassigned.view');
// Closed View
Route::get('/ClosedList/{id}', 'ComplaintStageController@closedView')->name('complaintClosed.view');
// Closed View
Route::get('/CompletelyClosedList/{id}', 'ComplaintStageController@completelyClosedView')->name('complaintCompletelyClosed.view');
// Closed View
Route::get('/ReviewList/{id}', 'ComplaintStageController@reviewView')->name('complaintReview.view');
//Complaint Assign Remainder
Route::get('complaintAssignReminder/{complaint}', 'ComplaintStageController@assignReminder')->name('complaint.assignreminder');
//..................... Jackson .....................................

// Sub Assigned List
Route::match(['get', 'post'],'complaintStage/SubAssignedList','ComplaintStageController@subAssignedList')->name('complaintSubAssignedList');
// Review List
Route::match(['get', 'post'],'complaintStage/ReviewList','ComplaintStageController@reviewList')->name('complaintReviewList');
// Closed List
Route::match(['get', 'post'],'complaintStage/ClosedList','ComplaintStageController@closedList')->name('complaintClosedList');
// Completely Closed List
Route::match(['get', 'post'],'complaintStage/CompletelyClosedList','ComplaintStageController@completelyClosedList')->name('complaintCompletelyClosedList');
//Re-assign modal
Route::match(['get', 'post'],'complaintStage/reAssignModal', 'ComplaintStageController@reAssignModal')->name('complaintReAssignModal');
//Close modal
Route::match(['get', 'post'],'complaintStage/closeComplaintModal', 'ComplaintStageController@closeComplaintModal')->name('closeComplaintModal');

//Close modal
Route::match(['get', 'post'],'complaintStage/ticketCloseModal', 'ComplaintStageController@closeModal')->name('ticketCloseModal');
//Close modal Action
Route::post('complaintStage/closeModalAction', 'ComplaintStageController@ticketCloseModalAction')->name('ticketCloseModalAction');

//Approve Reject modal
Route::match(['get', 'post'],'complaintStage/ServiceReportStatusLandlordModal', 'ComplaintStageController@ServiceReportStatusLandlordModal')->name('ServiceReportStatusLandlordModal');
//Approve Reject modal Action
Route::post('complaintStage/ServiceReportStatusLandlordModalAction', 'ComplaintStageController@ServiceReportStatusLandlordModalAction')->name('ServiceReportStatusLandlordModalAction');



//Close modal Action
Route::post('complaintStage/complaintCloseModalAction', 'ComplaintStageController@complaintCloseModalAction')->name('complaintCloseModalAction');
//service Report Image Upload Action
Route::post('reportImageUpload', 'ComplaintStageController@reportImageUpload')->name('reportImageUpload');

// End

//Complaint Stage
Route::resource('complaintStage', 'ComplaintStageController');
//group assign modal
Route::post('complaintStage/groupAssignModal', 'ComplaintStageController@groupAssignModal')->name('groupAssignModal');
// Group assign Store
Route::post('complaintStage/storeGroupAssign', 'ComplaintStageController@storeGroupAssign')->name('storeGroupAssign');
//Get User by Role
Route::post('complaintStage/getUserByRole', 'ComplaintStageController@getUserByRole')->name('getUserByRole');
// Get Subcontractor
Route::post('complaintStage/getSubContractor', 'ComplaintStageController@getSubContractor')->name('getSubContractor');


// Assigned View
Route::get('/AssignedList/{id}', 'ComplaintStageController@AssignedView')->name('complaintAssigned.view');
// Contractor Service Report
Route::get('AssignedList/{id}/serviceReport/', 'ComplaintStageController@contractorServiceReport')->name('contractorServiceReport');
//  Check in Sub Contractor service Report
Route::get('/check-InContractor/{id}/', 'ComplaintStageController@checkInContractor')->name('checkInContractor');
//Sub assign modal
Route::post('complaintStage/subAssignModal', 'ComplaintStageController@subAssignModal')->name('subAssignModal');
//Sub Re assigning modal in sub assigned list
Route::post('complaintStage/subReAssignModal', 'ComplaintStageController@subReAssignModal')->name('subReAssignModal');
// Group assign Store
Route::post('complaintStage/storeGroupSubAssign', 'ComplaintStageController@storeGroupSubAssign')->name('storeGroupSubAssign');
// service report inventoy item add
Route::post('complaintStage/storeServiceReportNote', 'ComplaintStageController@storeServiceReportNote')->name('storeServiceReportNote');

// get Modal service report status Update
Route::post('complaintStage/ServiceReportStatusUpdate', 'ComplaintStageController@ServiceReportStatusUpdate')->name('ServiceReportStatusUpdate');
//  service report To closed Ticket after signature
Route::post('complaintStage/generateServiceReport', 'ComplaintStageController@generateServiceReport')->name('generateServiceReport');


// Send landlord approval| Reject|Approve
Route::get('complaintStage/ServiceReportStatusLandlord/{id}/{status}/ReviewList', 'ComplaintStageController@ServiceReportStatusLandlord')->name('ServiceReportStatusLandlord');

// service report status Update
Route::put('complaintStage/storeServiceReportStatusUpdate/{id}', 'ComplaintStageController@storeServiceReportStatusUpdate')->name('storeServiceReportStatusUpdate');
// sub Assigned View
Route::get('/SubAssignedList/{id}/{sub_assign}', 'ComplaintStageController@SubAssignedview')->name('complaintSubAssigned.view');
// Technician Service Report
Route::get('SubAssignedList/{id}/{sub_assign}/serviceReportTechnician/', 'ComplaintStageController@technicianServiceReport')->name('technicianServiceReport');
// Technician Service Report After close can edit
Route::get('ClosedList/{id}/{sub_assign?}/serviceReportTechnician/', 'ComplaintStageController@technicianServiceReportClosed')->name('technicianServiceReportClosed');
// Technician Service Report View
Route::get('SubAssignedList/{id}/{sub_assign}/serviceReportView/', 'ComplaintStageController@serviceReportView')->name('technicianServiceReportView');
//  Check in Technician service Report
Route::get('/check-In/{id}/{sub_assign}/', 'ComplaintStageController@checkIn')->name('checkIn');
// Update  service report via Button
Route::get('serviceReportStatusButtonUpdate/{id}/{status}/', 'ComplaintStageController@serviceReportStatusButtonUpdate')->name('serviceReportStatusButtonUpdate');

// Closed ticket Detail view via Service Report
Route::get('complaintStage/{id}/closedDetailedView/', 'ComplaintStageController@closedDetailedView')->name('closedDetailedView');
//Service Report Image delete
Route::delete('serviceDestroyImage/{id}/', 'ComplaintStageController@destroyImage')->name('destroyImage');

//..................... Jackson .....................................

//..................... Reshma starts.....................................

//reshma
//Amc Contract
Route::resource('amcContract', 'AmcContractController');

//ajax for building amenity
Route::get('getBuildingAmenity','AmcContractController@getBuildingAmenity')->name('getBuildingAmenity');
//ajax for building amenity In contract
Route::get('buildingAmenityInContract','AmcContractController@buildingAmenityInContract')->name('buildingAmenityInContract');

//amc Contractor autocomplete
Route::get('/contractorAutocompleteCode', 'AmcContractController@contractorAutocompleteCode')->name('contractorAutocompleteCode');

//amcbuilding autocomplete
Route::get('/buildingAmcAutocompleteCode','AmcContractController@buildingAmcAutocompleteCode')->name('buildingAmcAutocompleteCode');

//add amc building amenity
Route::post('addAmcAmenity','AmcContractController@addAmcAmenity')->name('addAmcAmenity');
//Check Contract Exist building amenity
Route::post('checkContractExist','AmcContractController@checkContractExist')->name('checkContractExist');

//search
Route::match(['get', 'post'],'amcContractSearch/amcContract', 'AmcContractController@ContractSearch')->name('amcContractSearch');

//cancel contract
 Route::post('cancelContract','AmcContractController@cancelContract')->name('cancelContract');


//amc Schedule
Route::resource('amcSchedule', 'AmcScheduleController');
//amc contract no autocomplete
Route::get('/amcContractNoAutocompleteCode', 'AmcScheduleController@amcContractNoAutocompleteCode')->name('amcContractNoAutocompleteCode');


//ajax for contract details
Route::post('/amcDetailsByContractNo', 'AmcScheduleController@amcDetailsByContractNo')->name('amcDetailsByContractNo');

//unit by building
Route::post('unitByBuilding','AmcScheduleController@unitByBuilding')->name('unitByBuilding');

//amc building auto complete
Route::get('/amcBuildingAutocomplete', 'AmcScheduleController@amcBuildingAutocomplete')->name('amcBuildingAutocomplete');

//amenities by Contract NO
Route::post('amenitiesByContractNo','AmcScheduleController@amenitiesByContractNo')->name('amenitiesByContractNo');

//search
Route::match(['get', 'post'],'amcScheduleSearch/amcSchedule', 'AmcScheduleController@ScheduleSearch')->name('amcScheduleSearch');

//process amc building amenity
Route::post('processAmcAmenity','AmcScheduleController@processAmcAmenity')->name('processAmcAmenity');
//process amc building amenity
Route::post('addAmenitySubcontractorForm','AmcScheduleController@addAmenitySubcontractorForm')->name('addAmenitySubcontractorForm');

Route::post('addAmenityTechnicianForm','AmcScheduleController@addAmenityTechnicianForm')->name('addAmenityTechnicianForm');
//process amc building amenity
Route::post('processAmcAmenityTechnician','AmcScheduleController@processAmcAmenityTechnician')->name('processAmcAmenityTechnician');
//amenity Modal edit
Route::post('amenityEdit', 'AmcScheduleController@amenityEdit')->name('amenityEdit');


//amenity Modal  update
Route::post('updateScheduleAmenity','AmcScheduleController@updateScheduleAmenity')->name('updateScheduleAmenity');

//amenity Modal add
Route::post('addTask', 'AmcScheduleController@addTask')->name('addTask');
//AmcTask Reminder
Route::get('amcTaskReminder/{task}', 'AmcTaskController@taskReminder')->name('addTask.reminder');
//AmcTask Reminder
Route::get('amcTaskNotification/', 'AmcTaskController@amcTaskNotification')->name('amcTaskNotification');

//amenity Modal add-sub
Route::post('addTaskSubContractor', 'AmcScheduleController@addTaskSubContractor')->name('addTaskSubContractor');

//amenity Modal add-for schedule view
Route::post('addTaskAmenity', 'AmcScheduleController@addTaskAmenity')->name('addTaskAmenity');

//amenity Modal add-for schedule view
Route::post('editTaskAmenity', 'AmcScheduleController@editTaskAmenity')->name('editTaskAmenity');
//Unt Aleready Exist
Route::post('unitExist', 'AmcScheduleController@unitExist')->name('unitExist');
  //cancel schedule
 Route::post('cancelSchedule','AmcScheduleController@cancelSchedule')->name('cancelSchedule');
//Amc Task
Route::resource('amcTask', 'AmcTaskController');
//search
Route::match(['get', 'post'],'amcTaskSearch/amcTask', 'AmcTaskController@TaskSearch')->name('amcTaskSearch');

//cancel Schedule Task
// Route::get('cancelScheduleTask/{id}','AmcTaskController@cancelScheduleTask')->name('cancelScheduleTask');
 Route::post('closeTask','AmcTaskController@closeTask')->name('closeTask');
 Route::post('addReview','AmcTaskController@addReview')->name('addReview');
 Route::post('checkTask','AmcTaskController@checkTask')->name('checkTask');

 //search-add-contractor
Route::get('amc_contract/enquiry_filter', 'AmcContractController@enquiryFilter')->name('amcContractFilter');

//search-add-schedule
Route::get('amc_schedule/enquiry_filter', 'AmcScheduleController@enquiryFilter')->name('amcScheduleFilter');

//search-add-task
Route::get('amc_task/enquiry_filter', 'AmcTaskController@enquiryFilter')->name('amcTaskFilter');
//amenity Modal edit - subcontractor
Route::post('amenityEditSub', 'AmcScheduleController@amenityEditSub')->name('amenityEditSub');
//ajax for building unis
Route::get('getBuildingUnits','AmcScheduleController@getBuildingUnits')->name('getBuildingUnits');
//..................... End .....................................

Route::middleware('auth')->group(function () { 

//maintenanceInvoice
Route::resource('maintenanceInvoice', 'MaintenanceInvoiceController');
Route::get('buildingUnit/{building_id?}','MaintenanceInvoiceController@buildingUnit')->name('buildingUnit');
Route::match(['get', 'post'],'maintenanceInvoiceFilter','MaintenanceInvoiceController@maintenanceInvoiceFilter')->name('maintenanceInvoiceFilter');

Route::get('sendToApproveUnapprove/{maintenanceInvoice}','MaintenanceInvoiceController@sendToApproveUnapprove')->name('maintenanceInvoice.sendToApproveUnapprove');

Route::get('approveInvoice/{maintenanceInvoice}/{action}','MaintenanceInvoiceController@approveInvoice')->name('maintenanceInvoice.approve');
Route::get('maintenanceInvoiceApproval','MaintenanceInvoiceController@maintenanceInvoiceApproval')->name('maintenanceInvoiceApproval');

Route::get('maintenanceInvoiceApproval/{maintenanceInvoice}','MaintenanceInvoiceController@show')->name('maintenanceInvoiceApproval.show');
 
//EXPENSE Account code Autocomplete
Route::get('/expenseAccountCodeAutocomplete', 'MaintenanceInvoiceController@expenseAccountCodeAutocomplete')->name('expenseAccountCodeAutocomplete');
Route::match(['get', 'post'],'groupInvoiceGeneration', 'MaintenanceInvoiceController@groupInvoiceGeneration')->name('groupInvoiceGeneration');
Route::post('/maintenanceInvoiceGenerate', 'MaintenanceInvoiceController@maintenanceInvoiceGenerate')->name('maintenanceInvoiceGenerate');
});
Route::get('/buildingAutocompleteCodeForDim', 'MaintenanceInvoiceController@buildingAutocompleteCodeForDim')->name('buildingAutocompleteCodeForDim');




/*
*
*Report Starts
*
*/

Route::get('showMaintenanceReport', 'MaintenanceReportController@showMaintenanceReport')->name('showMaintenanceReport');

Route::post('maintenanceReportPdf', 'MaintenanceReportController@maintenanceReportPdf')->name('maintenanceReportPdf');

Route::get('showComplaintStatusReport', 'MaintenanceReportController@showComplaintStatusReport')->name('showComplaintStatusReport');

Route::post('complaintStatusReportPdf', 'MaintenanceReportController@complaintStatusReportPdf')->name('complaintStatusReportPdf');

Route::get('showComplaintTicketReport', 'MaintenanceReportController@showComplaintTicketReport')->name('showComplaintTicketReport');


Route::post('complaintTicketReportPdf', 'MaintenanceReportController@complaintTicketReportPdf')->name('complaintTicketReportPdf');

Route::get('unitReportAutocompleteCode', 'MaintenanceReportController@unitReportAutocompleteCode')->name('unitReportAutocompleteCode');

/*
*
*Report ends
*
*/
Route::get('showServiceReport', 'MaintenanceReportController@showServiceReport')->name('showServiceReport');
Route::post('serviceReportPdf', 'MaintenanceReportController@serviceReportPdf')->name('serviceReportPdf');
Route::get('servicecomplaintReportAutocompleteCode', 'MaintenanceReportController@servicecomplaintReportAutocompleteCode')->name('servicecomplaintReportAutocompleteCode');
Route::get('servicecomplaintReportNoAutocompleteCode', 'MaintenanceReportController@servicecomplaintReportNoAutocompleteCode')->name('servicecomplaintReportNoAutocompleteCode');
});
