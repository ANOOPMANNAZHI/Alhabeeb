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
Route::prefix('backoffice')->group(function() {
    Route::get('/', 'BackOfficeController@index');
});
/*
*
*
* Tenant Renewal
*
*/

Route::get('testRenewal', 'TenantRenewalController@testRenewal')->name('testRenewal');

//Search
Route::get('tenant_filter', 'TenantRenewalController@tenantSearchFilter')->name('tenantFilter');
/*** Renewal Contract Add ****/
Route::get('contract/{id}/tenantRenewal/contract', 'TenantRenewalController@renewalNewContract')->name('tenantRenewalNewContract');
/*** Renewal Contract List ****/
Route::match(['get', 'post'],'tenantRenewal/contract', 'TenantRenewalController@renewalContract')->name('tenantRenewalContract');
// PDC Generation. due to Breadcrum issue
Route::get('{path}/{subpath}/pdc/{id}', 'TenantRenewalController@pdcGeneration')->name('pdcGeneration');
Route::get('contract/{id}/edit', 'TenantRenewalController@edit')->name('newContract.edit');
Route::get('{path}/{subpath}/invoice/{id}', 'TenantRenewalController@invoiceGeneration')->name('invoiceGeneration');
/*** View Previous Contract View ****/
Route::get('previousContractViewAndAction/{id}', 'TenantRenewalController@previousContractViewAndAction')->name('previousContractViewAndAction');
/*** Renewal Contract View ****/
Route::get('contract/{id}/{stage}/newContractShow/', 'TenantRenewalController@newContractShow')->name('newContractShow');
/*** Renewal Contract Approval stage List ****/
Route::match(['get', 'post'],'tenantRenewal/renewalContractApproval', 'TenantRenewalController@renewalContractApprovalStage')->name('renewalContractApproval');
/*** Renewal Contract View ****/
Route::get('renewalContractApproval/{id}/{stage}/newContractApprovalShow/', 'TenantRenewalController@newContractApprovalShow')->name('newContractApprovalShow');

/*** Renewed Contract List ****/
Route::match(['get', 'post'],'renewedContract', 'TenantRenewalController@renewedContract')->name('tenantRenewedContract');
/*** Renewed Contract View ****/
Route::get('renewedContract/{id}/', 'TenantRenewalController@renewedContractShow')->name('renewedContractShow');
Route::get('renewedContract/{tenant_contract}/edit', 'TenantRenewalController@renewedContractEdit')->name('renewedContract.edit');
Route::post('renewedContract/{tenant_contract}/save', 'TenantRenewalController@renewedContractSave')->name('renewedContract.save');
/*** Renewal Contract Cron ****/
Route::get('tenantRenewal/renewalCron', 'TenantRenewalController@cronRenewalContract')->name('cronRenewalContract');

/*** Renewal Stages ****/
Route::get('/tenantRenewal/stage/{contractId}/{stage}/{status}/{oldId}', 'TenantRenewalController@tenantRenewalStage')->name('tenantRenewalStage');

Route::post('/tenantRenewalStageDue/stage/{contractId}/{stage}/{status}/{oldId}', 'TenantRenewalController@tenantRenewalStageDue')->name('tenantRenewalStageDue');
/***  Sent for approval in renewal stage ****/
Route::get('/sendApprovalFromRenewal/{contractId}/{stage}/{status}/{oldId}', 'TenantRenewalController@sendApprovalFromRenewal')->name('sendApprovalFromRenewal');
//List Grid
Route::match(['get', 'post'],'tenantRenewalRequestSearch/tenantRenewal', 'TenantRenewalController@tenantRenewalRequestSearch')->name('tenantRenewal.tenantRenewalRequestSearch');
//generate pdf and send mail
Route::get('generate-pdf-email/{contract_id}','TenantRenewalController@generatePDF')->name('generatePdfEmail');


Route::post('tenantRenewalTemplate','TenantRenewalController@renewalTemplatePdf')->name('renewalTemplatePdf');
Route::post('tenantRenewalTemplateStore/{tenant_contract}','TenantRenewalController@tenantRenewalTemplateStore')->name('tenantRenewalTemplateStore');


//  Contract Under Renewal 
Route::get('tenantContractUnderRenewal','TenantRenewalController@contractUnderRenewal')->name('tenantContract.underRenewal');
Route::get('tenantContractUnderRenewal/{tenant_contract}','TenantRenewalController@show')->name('tenantContract.underRenewalView');
Route::post('tenantContractRenewalType/{tenant_contract}','TenantRenewalController@tenantContractRenewalType')->name('tenantContractRenewalType');

Route::post('tenantContractRenewalTypeStore/{tenant_contract}','TenantRenewalController@tenantContractRenewalTypeStore')->name('contractUnderRenewalTypeStore');
   



/*** Renewal Request List ****/
Route::resource('tenantRenewal', 'TenantRenewalController');


/*** Renewal Stages From approval accept Or Reject ****/
Route::post('/tenantRenewal/renewalApproveReject', 'TenantRenewalController@renewalApproveReject')->name('renewalApproveReject');
/*** Renewal Stages From approval accept Or Reject Store ****/
Route::post('/tenantRenewal/renewalApproveRejectStore', 'TenantRenewalController@renewalApproveRejectStore')->name('renewalApproveRejectStore');
/*** Renewal Note Store ****/
Route::post('/tenantRenewal/storeRenewalNote', 'TenantRenewalController@storeRenewalNote')->name('storeRenewalNote');


/*
*
*
* Landlord Renewal
*
*/
/*** Renewal Contract List ****/
Route::get('landlordRenewal/renewalContract', 'LandlordRenewalController@renewalContract')->name('renewalContract');
/*** Renewal Contract Approval stage List ****/
Route::get('landlordRenewal/contractApproval', 'LandlordRenewalController@landlordContractApproval')->name('landlordContractApproval');

/*** Renewal Contract Edit ****/
Route::get('landlordRenewal/newContractEdit/{id}/{status}', 'LandlordRenewalController@renewalContractEdit')->name('landlordRenewalContractEdit');



//-------------------------------Reshma Starts--------------------------------------------//
Route::get('landlordRenewal/renewalDue', 'LandlordRenewalController@renewalDue')->name('renewalDue');
//search-add-Renewal Due
Route::get('landlordRenewal/renewalDueFilter', 'LandlordRenewalController@renewalDueFilter')->name('renewalDueFilter');

//search-Landlord Renewal Due
Route::match(['get', 'post'],'landlordDueSearch/landlordRenewal', 'LandlordRenewalController@dueSearch')->name('landlordDueSearch');
//search-Landlord Renewal Contract
Route::match(['get', 'post'],'landlordContractSearch/landlordRenewal', 'LandlordRenewalController@renewalContractSearch')->name('landlordContractSearch');

//search-Landlord Renewal Contract
Route::get('landlordRenewal/renewalContractFilter', 'LandlordRenewalController@renewalContractFilter')->name('renewalContractFilter');

 Route::get('renewalContract/{id}/', 'LandlordRenewalController@getLandlordRenewalContract')->name('landlordRenewalContract');
 //Landlord Contract Renewal Creation
 Route::get('renewalContract/{id}/{status}/newContractCreate', 'LandlordRenewalController@renewalContractCreate')->name('landlordRenewalContractCreate');
 //Landlord Contract Renewal Creation-Add
 Route::post('landlordRenewal/newContractCreateAdd', 'LandlordRenewalController@renewalContractAdd')->name('landlordRenewalContractAdd');
 //Landlord new contract edit
 Route::get('renewalContract/{id}/landlordRenewal', 'LandlordRenewalController@renewalNewContractEdit')->name('landlordRenewalNewContractEdit');
 //landlord new contract update
 Route::post('landlordRenewal/newContractUpdate', 'LandlordRenewalController@renewalNewContractUpdate')->name('landlordRenewalNewContractUpdate');
  //landlord contract sent for approval
 Route::get('landlordRenewal/sentApproval/{oid}/{nid}', 'LandlordRenewalController@landlordSentForApproval')->name('landlordSentForApproval');
 //landlord contract approval
 Route::get('contractApproval/{id}/landlordRenewal', 'LandlordRenewalController@getLandlordApprovalContract')->name('landlordApprovalContract');
 //search-Landlord Renewal Approval
Route::match(['get', 'post'],'landlordApprovalSearch/contractApproval', 'LandlordRenewalController@approvalSearch')->name('landlordApprovalSearch');

//search-approval advance
Route::get('landlordRenewal/renewalApprovalFilter', 'LandlordRenewalController@renewalApprovalFilter')->name('renewalApprovalFilter');

/*** Renewal Contract Approval stage List ****/
Route::get('landlordRenewal/contractRenewed', 'LandlordRenewalController@landlordContractRenewed')->name('landlordContractRenewed');

//landlord renewed contract grid view
Route::get('contractRenewed/{id}/landlordRenewal/', 'LandlordRenewalController@getLandlordRenewedContract')->name('landlordRenewedContract');
//landlord approve reject from approve list
Route::post('/landlordRenewal/approveReject', 'LandlordRenewalController@landlordRenewalApproveReject')->name('landlordRenewalApproveReject');
//landlordRenewal approve reject store
Route::post('/landlordRenewal/approveRejectStore', 'LandlordRenewalController@landlordRenewalApproveRejectStore')->name('landlordRenewalApproveRejectStore');
//search-renewed advance
Route::get('landlordRenewal/renewedContractFilter', 'LandlordRenewalController@renewedContractFilter')->name('renewedContractFilter');

 //search-Landlord Renewed contract
Route::match(['get', 'post'],'landlordRenewedSearch/renewedContract', 'LandlordRenewalController@renewedContractSearch')->name('renewedContractSearch');



//-------------------------------Reshma Ends--------------------------------------------//

/*** Renewal Request List ****/
Route::resource('landlordRenewal', 'LandlordRenewalController');
/*** Renewal Stages ****/
Route::post('/landlordRenewal/stage', 'LandlordRenewalController@landlordRenewalStage')->name('landlordRenewalStage');
/*** Renewal Contract Update ****/
Route::post('/landlordRenewal/renewalContractUpdate/{id}', 'LandlordRenewalController@renewalContractUpdate')->name('landlordRenewalContractUpdate');  
//Landlord Renewal Note Store
Route::post('landlordRenewal/renewalNote', 'LandlordRenewalController@landlordRenewalNoteStore')->name('landlordRenewalNoteStore'); 
/*** Renewal Contract View ****/
Route::get('landlordRenewal/landlordNewContractShow/{id}/{req_id}/{status}', 'LandlordRenewalController@landlordNewContractShow')->name('landlordNewContractShow');
/*******************************RENEWAL ENDS*************************************/

// Jackson 
/*** Tenant edit ****/ 
Route::post('tenantPopup', 'TenantContractEditController@tenantPopup')->name('tenantPopup');
Route::post('tenantPopupAction', 'TenantContractEditController@tenantPopupAction')->name('tenantPopupAction');
Route::post('tenantNameAjaxCode', 'TenantContractEditController@tenantNameAjaxCode')->name('tenantNameAjaxCode');
Route::resource('tenant-contract', 'TenantContractEditController');


Route::post('discussionForum', 'TenantContractEditController@discussionForum')->name('discussionForum');
Route::post('discussionForum/{tenant_contract}', 'TenantContractEditController@discussionForumStore')->name('discussion.store');

Route::get('tenantContractPendingRevoke', 'TenantContractEditController@tenantContractPendingRevoke')->name('tenantContractPendingRevoke');
Route::get('tenantContractApprovedRevoke', 'TenantContractEditController@tenantContractApprovedRevoke')->name('tenantContractApprovedRevoke');
//Tennat Direct Contract Send For Approval
Route::get('tenant-contract/{id}/{page}/SendForApproval/', 'TenantContractEditController@tenantSendForApproval')->name('tenantSendForApproval');
//Landlord Direct Contract Send For Approval
Route::get('landlord-contract/{id}/{page}/SendForApproval/', 'LandlordContractDirectController@landlordSendForApproval')->name('landlordSendForApproval');
/*** Tennat Rewoke ****/

Route::get('tenant-contract/{id}/tenantRevoke/', 'TenantContractEditController@tenantContractRevoke')->name('tenantRevoke');
Route::post('tenant-contract/storeTenantRevoke/{tenantContract}', 'TenantContractEditController@storeTenantContractRevoke')->name('storeTenantRevoke');
Route::get('{view}/{id}/{stage}/tenantRevokeProcess/', 'TenantContractEditController@revokeProcess')->name('leadAssign.revokeProcess');
Route::post('tenant-contract/noteDirectModal/', 'TenantContractEditController@noteDirectModal')->name('noteDirectModal');
Route::post('tenant-contract/noteModal/', 'TenantContractEditController@noteModal')->name('noteModal');
Route::post('tenant-contract/directContractApprovalAcceptReject', 'TenantContractEditController@directContractApprovalAcceptReject')->name('directContractApprovalAcceptReject');
Route::post('tenant-contract/approvalAcceptReject/', 'TenantContractEditController@approvalAcceptReject')->name('approvalAcceptReject');
/*** Get UnitType From Unit****/
Route::post('/tenant-contract/getUnitTypeByUnit', 'TenantContractEditController@getUnitTypeByUnit')->name('getUnitTypeByUnit');
/*** Get Occuoant****/
Route::post('/tenant-contract/getOccupant', 'TenantContractEditController@getOccupant')->name('getOccupant');

Route::get('tenant-contract/tenant-pdc/{id}', 'TenantContractEditController@tenantPdcView')->name('tenantPdcView');

Route::get('pdc/printPreview/{id}', 'PdcController@printPreview')->name('PdcprintPreview');

Route::get('pdc/printPreview/{id}/{stage}', 'PdcController@printPreviewstage')->name('PdcprintPreviewstage');

Route::get('tenant-contract/add-pdc/{id}', 'TenantContractEditController@addTenantPdf')->name('addTenantPdf');

//------------------- End ---------------------------
/* Landlord Direct Contract*/
Route::resource('landlord-contract', 'LandlordContractDirectController');

Route::post('/landlord-contract/closeLandlordContract', 'LandlordContractDirectController@closeLandlordContract')->name('closeLandlordContract');

Route::post('/contract-details/oldcontractmovetoLeagal', 'TenantContractEditController@oldcontractmovetoLeagal')->name('oldcontractmovetoLeagal');

//tenant contract change status
Route::post('/tenant-contract/changeStatus', 'TenantContractEditController@tenantContractChangeStatus')->name('tenantContractChangeStatus');
//tenant contract change status store
Route::post('/tenant-contract/changeStatusStore', 'TenantContractEditController@tenantContractChangeStatusStore')->name('tenantContractChangeStatusStore');

//tenant contract Add Municipality
Route::post('/tenant-contract/addMunicipality', 'TenantContractEditController@tenantContractAddMunicipality')->name('tenantContractAddMunicipality');

Route::post('/tenant-contract/addMunicipalityStore', 'TenantContractEditController@tenantContractAddMunicipalityStore')->name('tenantContractAddMunicipalityStore');
Route::post('/tenant-contract/addMunicipalityview', 'TenantContractEditController@tenantContractAddMunicipalityview')->name('tenantContractAddMunicipalityview');
/*
*
*
* Tenant Termination
*
*/
Route::match(['get', 'post'],'tenantTerminationRequestSearch/tenantTermination', 'TenantTerminationController@tenantTerminationRequestSearch')->name('tenantTerminationRequestSearch');

/*** Termination Stages ****/
Route::get('/tenantTerminationStage/{termination}/{contractId}/{stage}/{action_key}', 'TenantTerminationController@tenantTerminationStage')->name('tenantTerminationStage');

/*** Post Termination Stages with alert box****/
Route::post('/terminateResubmitOrTerminateModal', 'TenantTerminationController@terminateResubmitOrTerminateModal')->name('terminateResubmitOrTerminateModal');

Route::post('/terminateResubmitOrTerminateModalAction', 'TenantTerminationController@terminateResubmitOrTerminateModalAction')->name('terminateResubmitOrTerminateModalAction');

/*** Post OPen termination Approve or Reject Note****/
Route::post('/terminateApproveOrRejectModal', 'TenantTerminationController@terminateApproveOrRejectModal')->name('terminateApproveOrRejectModal');

Route::post('/terminateApproveOrRejectModalAction', 'TenantTerminationController@terminateApproveOrRejectModalAction')->name('terminateApproveOrRejectModalAction');
/*** Termination Stages ****/
Route::get('/tenantTerminationReview/{termination}/', 'TenantTerminationController@tenantTerminationReview')->name('tenantTerminationReview');
//Agreement Autocomplete
Route::get('/agreementAutocomplete', 'TenantTerminationController@agreementAutocomplete')->name('agreementAutocomplete');
//Building Autocomplete
Route::get('/allBuildingAutocomplete', 'TenantTerminationController@allBuildingAutocomplete')->name('allBuildingAutocomplete');

//Unit Autocomplete
Route::get('/unitAutocomplete', 'TenantTerminationController@unitAutocomplete')->name('unitAutocomplete');

//Tenant Autocomplete
Route::get('/tenantAutocomplete', 'TenantTerminationController@tenantAutocomplete')->name('tenantAutocomplete');

/* Termination Approval*/
Route::match(['get', 'post'],'tenantTerminationApproval', 'TenantTerminationController@tenantTerminationApproval')->name('tenantTerminationApproval');
/*** Termination handoverUnassigned View ****/
Route::get('tenantTerminationApproval/{termination}/tenantTerminationApprovalView/', 'TenantTerminationController@tenantTerminationApprovalView')->name('tenantTerminationApprovalView');

/*** Termination handoverUnassigned View ****/
Route::get('handoverUnassigned/{id}/handoverUnassignedView/', 'TenantTerminationController@handoverUnassignedView')->name('handoverUnassignedView');
/* Termination handoverUnassigned*/
Route::match(['get', 'post'],'handoverUnassigned', 'TenantTerminationController@handoverUnassigned')->name('handoverUnassigned');
/*** Termination handoverUnassigned View ****/
Route::get('handoverAssigned/{termination}/handoverAssignedView/', 'TenantTerminationController@handoverAssignedView')->name('handoverAssignedView');

/*** Termination handoverUnassigned Inspection Create ****/
Route::get('handoverAssigned/{id}/handoverAssignedInspection/', 'TenantTerminationController@handoverAssignedInspection')->name('handoverAssignedInspection');
/*** Termination handoverUnassigned View ****/
Route::get('handoverAssigned/{termination}/handoverAssignedInspectionEdit/', 'TenantTerminationController@handoverAssignedInspectionEdit')->name('handoverAssignedInspectionEdit');
//Tenant termination handover assigned Referback
Route::post('/terminationReferBack', 'TenantTerminationController@terminationReferBack')->name('terminationReferBack');

Route::post('/terminationReferBackStore', 'TenantTerminationController@terminationReferBackStore')->name('terminationReferBackStore');

/* Termination Approval*/
Route::match(['get', 'post'],'handoverAssigned', 'TenantTerminationController@handoverAssigned')->name('handoverAssigned');

Route::match(['get', 'post'],'handoverAssignedView', 'TenantTerminationController@handoverAssignedSalesPersonView')->name('handoverAssignedSalesPersonView');

Route::get('handoverAssignedView/{termination}/handoverAssignedView/', 'TenantTerminationController@handoverAssignedViewSalesPerson')->name('handoverAssignedViewSalesPerson');

/*** Open Termination Status Changes Store ****/
Route::get('/tenantTerminationOpenStatus/{terminationId}/{status}', 'TenantTerminationController@tenantTerminationOpenStatus')->name('tenantTerminationOpenStatus');
/* Take Over For  Termination */
Route::match(['get', 'post'],'takeoverForTermination', 'TenantTerminationController@takeoverForTermination')->name('takeoverForTermination');
/*** Take Over For  Termination View ****/
Route::get('takeoverForTermination/{id}/takeoverForTerminationView/', 'TenantTerminationController@takeoverForTerminationView')->name('takeoverForTerminationView');
Route::get('terminationStage/signature', 'TenantTerminationController@signature')->name('tenantTerminationSignature');
/* tenantTerminatedContract */
Route::match(['get', 'post'],'tenantTerminatedContract', 'TenantTerminationController@tenantTerminatedContract')->name('tenantTerminatedContract');
/*** Termination tenantTerminatedContract View ****/
Route::get('tenantTerminatedContract/{id}/tenantTerminatedContractView/', 'TenantTerminationController@tenantTerminatedContractView')->name('tenantTerminatedContractView');
//Termination Inspection Remainder
Route::get('terminationInspectionReminder/{termination}', 'TenantTerminationController@inspectionReminder')->name('InspectionReminder');
//generate pdf and send mail
Route::get('terminationEmail/{termination_id}','TenantTerminationController@terminationEmail')->name('terminationEmail');

/*** Agreement Detail ****/
Route::post('/agreementDetail', 'TenantTerminationController@agreementDetail')->name('agreementDetail');
/*** Unit Detail ****/
Route::post('/unitDetail', 'TenantTerminationController@occupiedUnitDetail')->name('occupiedUnitDetail');
//Agreement by tenant id
Route::post('/getTenantContractByTenantId', 'TenantTerminationController@getTenantContractByTenantId')->name('getTenantContractByTenantId');
/*** Agreement Detail  Against buildingunit etc ****/
Route::post('/agreementDetailAgainstBulUnit', 'TenantTerminationController@agreementDetailAgainstBulUnit')->name('agreementDetailAgainstBulUnit');

/*** Building Detail  Against Tenant ****/
Route::post('/getBuildingByTenantId', 'TenantTerminationController@getBuildingByTenantId')->name('getBuildingByTenantId');
/*** group Assign Modal ****/
Route::post('/terminationGroupAssignModal', 'TenantTerminationController@groupAssignModal')->name('terminationGroupAssignModal');


Route::post('/cancelTermination', 'TenantTerminationController@cancelTermination')->name('cancelTermination');
Route::post('/cancelTerminationHandover', 'TenantTerminationController@cancelTerminationHandover')->name('cancelTerminationHandover');

/*** group Assign Modal ****/
Route::post('/storeGroupAssign', 'TenantTerminationController@storeGroupAssign')->name('terminationStoreGroupAssign');
/*** ImageUpload ****/
Route::post('/ImageUpload', 'TenantTerminationController@imageUpload')->name('imageUpload');
/*** Document Upload ****/
Route::post('/terminationDocumentStore', 'TenantTerminationController@terminationDocumentStore')->name('terminationDocumentStore');
/*** Inspection Store ****/
Route::post('/terminationInspectionStore', 'TenantTerminationController@terminationInspectionStore')->name('terminationInspectionStore');
/*** Inspection Update Store ****/
Route::put('/terminationInspectionUpdate/{id}', 'TenantTerminationController@terminationInspectionUpdate')->name('terminationInspectionUpdate');
/*** Update TakenOver date &Termination Date  ****/
Route::post('/terminationUpdateExtraFields', 'TenantTerminationController@terminationUpdateExtraFields')->name('terminationUpdateExtraFields');
/*** terminationSignatureStore ****/
Route::post('/terminationSignatureStore', 'TenantTerminationController@terminationSignatureStore')->name('terminationSignatureStore');
/*** termination Penalty  ****/
Route::post('tenantTerminationPenalty', 'TenantTerminationController@tenantTerminationPenalty')->name('tenantTerminationPenalty');
/***  termination Penalty Store ****/
Route::post('/tenantTerminationPenaltyStore', 'TenantTerminationController@tenantTerminationPenaltyStore')->name('tenantTerminationPenaltyStore');

Route::post('supervisorNoteStore', 'TenantTerminationController@supervisorNoteStore')->name('supervisorNoteStore');
Route::get('inspectionSendMail/{termination_id}', 'TenantTerminationController@inspectionSendMail')->name('inspectionSendMail');

/*** Termination Request List ****/ 
Route::resource('tenantTermination', 'TenantTerminationController');

/*
*
*
* Landlord Termination
*
*/
Route::match(['get', 'post'],'terminationRequestSearch/landlordTermination', 'LandlordTerminationController@terminationRequestSearch')->name('terminationRequestSearch');
//Agreement Autocomplete
Route::get('/landlordAgreementAutocomplete', 'LandlordTerminationController@agreementAutocomplete')->name('landlordAgreementAutocomplete');
//Building Autocomplete
Route::get('/landlordBuildingAutocomplete', 'LandlordTerminationController@buildingAutocomplete')->name('landlordBuildingAutocomplete');
//Landlord Autocomplete
Route::get('/vendorAutocomplete', 'LandlordTerminationController@landlordVendorAutocomplete')->name('landlordVendorAutocomplete');
/*** Agreement Detail ****/
Route::post('/landlordagreementDetail', 'LandlordTerminationController@agreementDetail')->name('landlordAgreementDetail');
/***  TerminatedContractLandlordView  View ****/
Route::get('LCTerminationVerify/{contract}/{termination}/{flag}/tenantContractByLandlord/', 'LandlordTerminationController@tenantContractByLandlord')->name('tenantContractByLandlord');
/***  Landlord PDC View  ****/
Route::get('LCTerminationVerify/{contract}/{termination}/{flag}/LandlordByTenantPdcView/', 'LandlordTerminationController@LandlordByTenantPdcView')->name('LandlordByTenantPdcView');
/***  TerminatedContractLandlordView  View ****/
Route::get('LCTerminationVerify/{contract}/{termination}/{flag}/LandlordByTenantInvoiceView/', 'LandlordTerminationController@LandlordByTenantInvoiceView')->name('LandlordByTenantInvoiceView');

/***  TerminatedContractLandlordView  View ****/
Route::get('LCTerminationApproval/{contract}/{termination}/{flag}/tenantContractByLandlord/', 'LandlordTerminationController@tenantContractByLandlord')->name('tenantContractByLandlordApproval');
/***  Landlord PDC View  ****/
Route::get('LCTerminationApproval/{contract}/{termination}/{flag}/LandlordByTenantPdcView/', 'LandlordTerminationController@LandlordByTenantPdcView')->name('LandlordApprovalByTenantPdcView');
/***  TerminatedContractLandlordView  View ****/
Route::get('LCTerminationApproval/{contract}/{termination}/{flag}/LandlordByTenantInvoiceView/', 'LandlordTerminationController@LandlordByTenantInvoiceView')->name('LandlordApprovalByTenantInvoiceView');


/*** Termination Stages ****/
Route::get('/landlordTerminationStage/{termination}/{contractId}/{stage}/{action_key}', 'LandlordTerminationController@landlordTerminationStage')->name('landlordTerminationStage');
/* LCTerminationVarify */
Route::match(['get', 'post'],'LCTerminationVerify', 'LandlordTerminationController@LCTerminationVerify')->name('LCTerminationVerify');
/*** Termination LCTerminationVerify View ****/
Route::get('LCTerminationVerify/{termination}/LCTerminationVerifyView/', 'LandlordTerminationController@LCTerminationVerifyView')->name('LCTerminationVerifyView');
/* LCTerminationApproval */
Route::match(['get', 'post'],'LCTerminationApproval', 'LandlordTerminationController@LCTerminationApproval')->name('LCTerminationApproval');
/***  LCTerminationApproval  View ****/
Route::get('LCTerminationApproval/{termination}/LCTerminationApprovalView/', 'LandlordTerminationController@LCTerminationApprovalView')->name('LCTerminationApprovalView');
/* TerminatedContractLandlord */
Route::match(['get', 'post'],'TerminatedContractLandlord', 'LandlordTerminationController@TerminatedContractLandlord')->name('TerminatedContractLandlord');
/***  TerminatedContractLandlordView  View ****/
Route::get('TerminatedContractLandlord/{termination}/TerminatedContractLandlordView/', 'LandlordTerminationController@TerminatedContractLandlordView')->name('TerminatedContractLandlordView');
/*** Open Termination Status Changes Store ****/
Route::get('/landlordTerminationChangeStatus/{terminationId}/{status}', 'LandlordTerminationController@landlordTerminationChangeStatus')->name('landlordTerminationChangeStatus');
/*** Check Outstanding ****/
Route::get('/checkOutstanding/{landlordContract}', 'LandlordTerminationController@chekOutstanding')->name('chekOutstanding');
/*** Termination Request List ****/ 
Route::resource('landlordTermination', 'LandlordTerminationController');


// Tenant Pdc -Jackson
// Tenant Invoice
Route::resource('invoice', 'InvoiceController');
Route::get('invoice/tenant-rent-invoice-details/{id}', 'InvoiceController@tenantRentInvoiceDetails')->name('tenantRentInvoiceDetails');
// Tenant Pdc

Route::match(['get', 'post'],'tenant-pdc-view/tenant-contract/{id}', 'PdcController@pdcView')->name('pdcView');
Route::post('pdc/tenant-pdc-edit', 'PdcController@pdcEdit')->name('pdcEdit');
Route::match(['get', 'post'],'pdc/tenantPdcBounce', 'PdcController@tenantPdcBounce')->name('tenantPdcBounce');
Route::post('pdc/tenant-ajax-pdc-bounce', 'PdcController@tenantAjaxPdcBounceSearch')->name('tenantAjaxPdcBounceSearch');
Route::post('pdc/tenant-ajax-pdc-exchange', 'PdcController@tenantAjaxPdcExchange')->name('tenantAjaxPdcExchange');
Route::post('pdc/getPdcExchangeDetails', 'PdcController@getPdcExchangeDetails')->name('getPdcExchangeDetails');
Route::get('pdc/getBouncedCheques', 'PdcController@getBouncedCheques')->name('getBouncedCheques');
Route::resource('pdc', 'PdcController');
//Pdc Excel 
Route::get('export', 'TenantPdcExcelController@export')->name('export');
Route::post('import', 'TenantPdcExcelController@import')->name('import');



/**** key Module  Jainy***/
//Tenant Active Autocomplete

Route::get('/tenantActiveAutocomplete', 'KeyController@tenantActiveAutocomplete')->name('tenantActiveAutocomplete');
Route::get('key_filter', 'KeyController@keySearchFilter')->name('keyFilter');
Route::match(['get', 'post'],'keyRequestSearch/keyManagement', 'KeyController@keyRequestSearch')->name('keyRequestSearch');
Route::match(['get', 'post'],'keyManagement/keyAccept', 'KeyController@keyAccept')->name('keyAccept');
Route::post('/keyAcceptAginstLandlordTenant', 'KeyController@keyAcceptAginstLandlordTenant')->name('keyAcceptAginstLandlordTenant');
Route::post('/keyAcceptAginstLandlordTenantStore', 'KeyController@keyAcceptAginstLandlordTenantStore')->name('keyAcceptAginstLandlordTenantStore');
Route::post('/keyScanForPaymentReceipt', 'KeyController@keyScanForPaymentReceipt')->name('keyScanForPaymentReceipt');
Route::resource('keyManagement','KeyController');
/**** key Module  Jainy END ***/

/** Routines starts**/
//Route::get('pdcPosting/rentalIncomePosting', 'RoutinesController@rentalIncomePosting')->name('rentalIncomePosting');
//Route::get('pdcPosting/tenantReceiptPosting', 'RoutinesController@tenantReceiptPosting')->name('tenantReceiptPosting');
//Route::get('pdcPosting/costRecognition', 'RoutinesController@costRecognition')->name('costRecognition');

//Route::resource('pdcPosting','RoutinesController');
Route::match(['get', 'post'],'pdcPosting', 'RoutinesController@pdcBulkPosting')->name('pdcPosting');
Route::match(['get', 'post'],'pdcPosting/rentalIncomePosting', 'RoutinesController@rentalIncomePostingList')->name('rentalIncomePosting');
Route::match(['get', 'post'],'pdcPosting/tenantReceiptPosting', 'RoutinesController@tenantReceiptPostingList')->name('tenantReceiptPosting');
Route::match(['get', 'post'],'pdcPosting/costRecognition', 'RoutinesController@costRecognitionList')->name('costRecognition');
//pdc Post
Route::post('/pdcPosting/pdcPost', 'RoutinesController@pdcPost')->name('pdcPost');
Route::post('/rentalIncomePosting/rentalPost', 'RoutinesController@rentalPost')->name('rentalPost');
Route::post('/tenantReceiptPostingList/receiptPost', 'RoutinesController@receiptPost')->name('receiptPost');
Route::post('/costRecognitionList/costPost', 'RoutinesController@costPost')->name('costPost');

Route::get('/tenantInvoicePosting/{invoice_id}','InvoiceController@tenantInvoicePosting')->name('tenantInvoicePosting');
Route::get('rentalBuildingAutocomplete', 'RoutinesController@rentalBuildingAutocomplete')->name('rentalBuildingAutocomplete');
/** Routines ends **/

/** Maintenance Payment starts **/
Route::get('maintenancePayment/maintenancePaymentApproval', 'MaintenancePaymentController@maintenancePaymentApproval')->name('maintenancePaymentApproval');
Route::get('maintenancePayment/enquiryFilter', 'MaintenancePaymentController@enquiryFilter')->name('maintenancePaymentFilter');

Route::resource('maintenancePayment','MaintenancePaymentController');
Route::get('/contractorTypeAutocompleteCode', 'MaintenancePaymentController@contractorTypeAutocompleteCode')->name('contractorTypeAutocompleteCode');



Route::get('maintenancePaymentApproval/{id}', 'MaintenancePaymentController@maintenancePaymentApprovalShow')->name('maintenancePaymentApprovalShow');
//search
Route::match(['get', 'post'],'paymentSearch/maintenancePayment', 'MaintenancePaymentController@paymentSearch')->name('paymentSearch');

Route::post('/maintenancePayment/cancel', 'MaintenancePaymentController@cancelMaintenancePayment')->name('cancelMaintenancePayment');
Route::post('/maintenancePayment/cancelStore', 'MaintenancePaymentController@cancelMaintenancePaymentStore')->name('cancelMaintenancePaymentStore');

Route::get('/maintenancePaymentAction/{maintenance_id}/{status}/{stage}','MaintenancePaymentController@maintenancePaymentAction')->name('maintenancePaymentAction');

Route::get('/maintenancePaymentPost/{maintenance_id}/{status}','MaintenancePaymentController@maintenancePaymentPost')->name('maintenancePaymentPost');
Route::match(['get', 'post'],'paymentApprovalSearch/maintenancePayment', 'MaintenancePaymentController@paymentApprovalSearch')->name('paymentApprovalSearch');
/** Maintenance Payment ends **/

/** Landlord Payment starts **/
Route::get('landlordPayment/landlordPaymentApproval', 'LandlordPaymentController@landlordPaymentApproval')->name('landlordPaymentApproval');
Route::get('landlordPayment/enquiryFilter', 'LandlordPaymentController@enquiryFilter')->name('landlordPaymentFilter');
Route::resource('landlordPayment','LandlordPaymentController');
Route::get('/landlordTypeAutocompleteCode', 'LandlordPaymentController@landlordTypeAutocompleteCode')->name('landlordTypeAutocompleteCode');
Route::get('/agreementAutocompleteCode', 'LandlordPaymentController@agreementAutocompleteCode')->name('agreementAutocompleteCode');
Route::match(['get', 'post'],'paymentSearch/landlordPayment', 'LandlordPaymentController@paymentSearch')->name('landlordPaymentSearch');
Route::post('/landlordPayment/cancel', 'LandlordPaymentController@cancelLandlordPayment')->name('cancelLandlordPayment');
Route::post('/landlordPayment/cancelStore', 'LandlordPaymentController@cancelLandlordPaymentStore')->name('cancelLandlordPaymentStore');
//ajax for contract details
Route::post('/invoiceDetailsByContractNo', 'LandlordPaymentController@invoiceDetailsByContractNo')->name('invoiceDetailsByContractNo');
Route::get('/landlordPaymentAction/{landlord_id}/{status}/{stage}','LandlordPaymentController@landlordPaymentAction')->name('landlordPaymentAction');
Route::get('landlordPaymentApproval/{id}', 'LandlordPaymentController@landlordPaymentApprovalShow')->name('landlordPaymentApprovalShow');
Route::match(['get', 'post'],'landlordPaymentApprovalSearch/landlordPayment', 'LandlordPaymentController@landlordPaymentApprovalSearch')->name('landlordPaymentApprovalSearch');
Route::get('/landlordPaymentPost/{landlord_id}/{status}','LandlordPaymentController@landlordPaymentPost')->name('landlordPaymentPost');
Route::post('/invoiceBalanceAmount', 'LandlordPaymentController@invoiceBalanceAmount')->name('invoiceBalanceAmount');

/** Landlord Payment ends **/

/** Deposit Refund starts **/
Route::get('depositRefund/enquiryFilter', 'DepositRefundController@enquiryFilter')->name('depositRefundFilter');
Route::match(['get', 'post'],'depositSearch/depositRefund', 'DepositRefundController@depositSearch')->name('depositRefundSearch');
Route::get('depositRefund/depositRefundApproval', 'DepositRefundController@depositRefundApproval')->name('depositRefundApproval');
Route::resource('depositRefund','DepositRefundController');
Route::get('/buildingDepositRefundAutocompleteCode', 'DepositRefundController@buildingDepositRefundAutocompleteCode')->name('buildingDepositRefundAutocompleteCode');

Route::get('/getUnitAutocompleteCode', 'DepositRefundController@getUnitAutocompleteCode')->name('getUnitAutocompleteCode');
Route::get('/tenantDepositRefundAutocompleteCode', 'DepositRefundController@tenantDepositRefundAutocompleteCode')->name('tenantDepositRefundAutocompleteCode');
Route::post('/unitDetailsByBuildingId', 'DepositRefundController@unitDetailsByBuildingId')->name('unitDetailsByBuildingId');

Route::post('/buildingDetailsByUnitId', 'DepositRefundController@buildingDetailsByUnitId')->name('buildingDetailsByUnitId');
Route::post('/getTenantDetailsByBuildingUnit', 'DepositRefundController@getTenantDetailsByBuildingUnit')->name('getTenantDetailsByBuildingUnit');

Route::post('/getTenantDetailsByUnitId', 'DepositRefundController@getTenantDetailsByUnitId')->name('getTenantDetailsByUnitId');

Route::post('/getUnitDetailsByTenantId', 'DepositRefundController@getUnitDetailsByTenantId')->name('getUnitDetailsByTenantId');
Route::post('/ajaxBankInfo', 'DepositRefundController@ajaxBankInfo')->name('ajaxBankInfo');
Route::post('/getReceiptDetailsByBuildingUnitTenant', 'DepositRefundController@getReceiptDetailsByBuildingUnitTenant')->name('getReceiptDetailsByBuildingUnitTenant');
Route::post('/getReceiptDetailsByUnitTenant', 'DepositRefundController@getReceiptDetailsByUnitTenant')->name('getReceiptDetailsByUnitTenant');
Route::post('/getReceiptDetails', 'DepositRefundController@getReceiptDetails')->name('getReceiptDetails');
Route::post('/buildingDetailsByTenantId', 'DepositRefundController@buildingDetailsByTenantId')->name('buildingDetailsByTenantId');
Route::post('/depositRefund/cancel', 'DepositRefundController@cancelDepositRefund')->name('cancelDepositRefund');
Route::post('/depositRefund/cancelStore', 'DepositRefundController@cancelDepositRefundStore')->name('cancelDepositRefundStore');
Route::get('/depositRefundAction/{deposit_id}/{status}/{stage}','DepositRefundController@depositRefundAction')->name('depositRefundAction');
Route::get('depositRefundApproval/{id}', 'DepositRefundController@depositRefundApprovalShow')->name('depositRefundApprovalShow');
Route::match(['get', 'post'],'depositRefundApprovalSearch/depositRefund', 'DepositRefundController@depositRefundApprovalSearch')->name('depositRefundApprovalSearch');
Route::get('/depositRefundPost/{deposit_id}/{status}','DepositRefundController@depositRefundPost')->name('depositRefundPost');
Route::post('depositRefund/tenantDetailsByBuildingUnit','DepositRefundController@tenantDetailsByBuildingUnit')->name('tenantDetailsByBuildingUnit');
Route::get('/depositReceiptAutocompleteCode', 'DepositRefundController@depositReceiptAutocompleteCode')->name('depositReceiptAutocompleteCode');
Route::post('depositRefund/receiptDetailsByTenantContract','DepositRefundController@receiptDetailsByTenantContract')->name('receiptDetailsByTenantContract');
Route::get('/depositReceiptBuildingAutocompleteCode', 'DepositRefundController@depositReceiptBuildingAutocompleteCode')->name('depositReceiptBuildingAutocompleteCode');
Route::get('/depositReceiptTenantAutocompleteCode', 'DepositRefundController@depositReceiptTenantAutocompleteCode')->name('depositReceiptTenantAutocompleteCode');
Route::get('/depositReceiptUnitAutocompleteCode', 'DepositRefundController@depositReceiptUnitAutocompleteCode')->name('depositReceiptUnitAutocompleteCode');
Route::get('/receiptBuildingAutocomplete', 'RentReceiptGenerationController@receiptBuildingAutocomplete')->name('receiptBuildingAutocomplete');
/** Deposit Refund Ends **/


//Get Contract Details populate
Route::post('rentReceiptGeneration/receiptContractDetails','RentReceiptGenerationController@receiptContractDetails')->name('receiptContractDetails');

Route::post('rentReceiptGeneration/receiptContractDetailsByBuildingUnit','RentReceiptGenerationController@receiptContractDetailsByBuildingUnit')->name('receiptContractDetailsByBuildingUnit');
Route::match(['get', 'post'],'receiptsTabViewList/rentReceiptGeneration/{tab?}/{data?}','RentReceiptGenerationController@receiptsTabViewList')->name('receiptsTabViewList');
/*******Print Preview**********/
Route::get('rentReceiptGeneration/printPreview/{id}', 'RentReceiptGenerationController@printPreview')->name('printPreview');
/******* End Print Preview**********/
 Route::match(['get', 'post'],'receiptsTabViewList/rentReceiptGeneration/{tab}','RentReceiptGenerationController@receiptsTabViewList')->name('receiptsTabViewListtab');
Route::match(['get', 'post'],'receiptsAgreementViewList/rentReceiptGeneration/{id}/{tab}','RentReceiptGenerationController@receiptsAgreementViewList')->name('receiptsAgreementViewList');
/* --------------------General -------------------- */
Route::match(['get', 'post'],'rentReceiptGeneration/generalReceiptEditAction/{id}', 'RentReceiptGenerationController@updateGeneralReceiptAction')->name('updateGeneralReceiptAction');

Route::match(['get', 'post'],'rentReceiptGeneration/generalReceiptEdit/{id}', 'RentReceiptGenerationController@updateGeneralReceipt')->name('updateGeneralReceipt');

Route::post('rentReceiptGeneration/generalReceiptCreationAction', 'RentReceiptGenerationController@addGeneralReceiptAction')->name('addGeneralReceiptAction');

Route::match(['get', 'post'],'rentReceiptGeneration/generalReceiptCreation', 'RentReceiptGenerationController@addGeneralReceipt')->name('addGeneralReceipt');

/*---------------------End ----------------- */
/* --------------- Deposit -------------------- */

Route::match(['get', 'post'],'rentReceiptGeneration/depositReceiptEditAction/{id}', 'RentReceiptGenerationController@updateDepositReceiptAction')->name('updateDepositReceiptAction');

Route::match(['get', 'post'],'rentReceiptGeneration/depositReceiptEdit/{id}', 'RentReceiptGenerationController@updateDepositReceipt')->name('updateDepositReceipt');

Route::post('rentReceiptGeneration/depositReceiptCreationAction', 'RentReceiptGenerationController@addDepositReceiptAction')->name('addDepositReceiptAction');

Route::match(['get','post'],'rentReceiptGeneration/depositReceiptCreation', 'RentReceiptGenerationController@addDepositReceipt')->name('addDepositReceipt');



/*---------------------End ----------------- */
//qrCodeScanner
Route::post('/rentReceiptGeneration/qrCodeScanner', 'RentReceiptGenerationController@qrCodeScanner')->name('qrCodeScanner');
// REquest for receipt Approval

Route::match(['get', 'post'],'receiptsRequestApproval/{tab?}','RentReceiptGenerationController@receiptsRequestForApprovalTabViewList')->name('receiptsRequestForApprovalTabViewList');

// Route::match(['get', 'post'],'receiptsRequestApproval/{tab}','RentReceiptGenerationController@receiptsRequestForApprovalTabViewList')->name('receiptsRequestForApprovalTabViewListtab');
Route::post('/pdcNoListWithContractNo', 'RentReceiptGenerationController@pdcNoListWithContractNo')->name('pdcNoListWithContractNo');
Route::post('/pdcListWithoutReceiptByContract', 'RentReceiptGenerationController@pdcListWithoutReceiptByContract')->name('pdcListWithoutReceiptByContract');
Route::post('/rentReceiptGeneration/changeStatus', 'RentReceiptGenerationController@receiptApprovalStatus')->name('receiptApprovalStatus');
Route::post('/rentReceiptGeneration/changeStatusAsPosted', 'RentReceiptGenerationController@receiptAsPosted')->name('receiptAsPosted');
Route::get('receipt_filter', 'RentReceiptGenerationController@receiptSearchFilter')->name('receiptFilter');
// -----------End ----------
Route::resource('rentReceiptGeneration', 'RentReceiptGenerationController');
Route::post('/keyScanForPaymentReceipt', 'KeyController@keyScanForPaymentReceipt')->name('keyScanForPaymentReceipt');



Route::middleware('auth')->group(function () { 	
	//Route::resource('landlordInvoice','LandlordInvoiceController');	 
	Route::get('landlordInvoiceApproval','LandlordInvoiceController@index')->name('landlordInvoiceApproval');

	Route::match(['get', 'post'],'landlordInvoiceFilter','LandlordInvoiceController@landlordInvoiceFilter')->name('landlordInvoiceFilter');
	
	
	Route::get('landlord-contract/{landlordContract}/invoiceGenerate','LandlordInvoiceController@create')->name('landlordInvoiceGenerate');
	Route::get('landlord-contract/{landlordContract}/invoice','LandlordInvoiceController@show')->name('landlordInvoiceShow');
	Route::get('landlord-contract/{landlordContract}/invoiceApproval','LandlordInvoiceController@show')->name('landlordInvoiceApprovalShow');
	//Route::get('landlord-contract/{landlordContract}/invoiceCreate','LandlordInvoiceController@create')->name('landlordInvoice.create');
	Route::get('landlord-contract/{landlordContract}/invoiceEdit','LandlordInvoiceController@edit')->name('landlordInvoice.edit');
    Route::post('landlord-contract/{landlordContract}/invoiceStore','LandlordInvoiceController@store')->name('landlordInvoice.store');
    Route::put('landlord-contract/{landlordContract}/invoiceUpdate','LandlordInvoiceController@update')->name('landlordInvoice.update');
    
    Route::get('landlord-contract/{landlordContract}/invoiceDelete','LandlordInvoiceController@destroy')->name('landlordInvoice.delete');
 
    Route::get('landlord-contract/{landlordContract}/{action}','LandlordInvoiceController@action')->name('landlordInvoice.action');
    
	Route::resource('generalLedger','GeneralLedgerController');	 
	Route::get('generalLedgerApproval','GeneralLedgerController@generalLedgerApproval')->name('generalLedgerApproval');
	
	Route::get('generalLedger/{generalLedger}/{action}','GeneralLedgerController@action')->name('generalLedger.action');
	
	Route::get('generalLedgerApproval/{generalLedger}','GeneralLedgerController@show')->name('generalLedgerApproval.show');
	Route::post('generalLedgerFilter','GeneralLedgerController@generalLedgerFilter')->name('generalLedgerFilter');
	Route::get('buildingAutocompleteInGl', 'GeneralLedgerController@buildingAutocompleteInGl')->name('buildingAutocompleteInGl');
	Route::post('buildingByUnitGL', 'GeneralLedgerController@buildingByUnitGL')->name('buildingByUnitGL');
	Route::post('generalLedgerDistribution','GeneralLedgerController@generalLedgerDistribution')->name('generalLedgerDistribution');
		
	});
	
		//Account code Autocomplete without Description
	Route::get('/accountCodeAutocompleteWithoutDesc', 'GeneralLedgerController@accountCodeAutocompleteWithoutDesc')->name('accountCodeAutocompleteWithoutDesc');
	//Account code info with params
	Route::get('/accountCodeWithParamInfo', 'GeneralLedgerController@accountCodeWithParamInfo')->name('accountCodeWithParamInfo');

	Route::get('/getReceiptDetailsjson', 'RentReceiptGenerationController@getReceiptDetailsjson')->name('getReceiptDetailsjson');

	//Account code Autocomplete
	Route::get('/accountCodeAutocomplete', 'GeneralLedgerController@accountCodeAutocomplete')->name('accountCodeAutocomplete');
	//Landlord Agreement List Autocomplete
	Route::get('/landlordAgreementAutocomplete', 'GeneralLedgerController@landlordAgreementAutocomplete')->name('landlordAgreementAutocomplete');
	  Route::get('receiptsAgreementViewList/rentReceiptGeneration/{tenantContract}','RentReceiptGenerationController@receiptsAgreementList')->name('rentReceiptFromTermination');

	  /***************** Mass Mail **************/

	Route::resource('massMail', 'MassMailController');
	Route::get('emailAutocomplete', 'MassMailController@emailAutocomplete')->name('emailAutocomplete');
	Route::get('/massEmailAutocomplete', 'MassMailController@massEmailAutocomplete')->name('massEmailAutocomplete');
	Route::get('emailCcAutocomplete', 'MassMailController@emailCcAutocomplete')->name('emailCcAutocomplete');
	Route::match(['get', 'post'],'composeMail/massMailList', 'MassMailController@massMailList')->name('massMailList');


/***************Report Starts******************/
	Route::get('showTenantContractReport', 'BackOfficeReportController@showTenantContractReport')->name('showTenantContractReport');

	Route::post('tenantContractReportPdf', 'BackOfficeReportController@tenantContractReportPdf')->name('tenantContractReportPdf');

	Route::get('showCorporateTenantReport', 'BackOfficeReportController@showCorporateTenantReport')->name('showCorporateTenantReport');

	Route::get('tenantReportAutocompleteCode', 'BackOfficeReportController@tenantReportAutocompleteCode')->name('tenantReportAutocompleteCode');

	Route::get('tenantCodeReportAutocompleteCode', 'BackOfficeReportController@tenantCodeReportAutocompleteCode')->name('tenantCodeReportAutocompleteCode');

	Route::post('corporateTenantReportPdf', 'BackOfficeReportController@corporateTenantReportPdf')->name('corporateTenantReportPdf');
	
	Route::get('tenantCompanyAutocompleteCode', 'BackOfficeReportController@tenantCompanyAutocompleteCode')->name('tenantCompanyAutocompleteCode');

	Route::get('tenantCodeCompanyAutocompleteCode', 'BackOfficeReportController@tenantCodeCompanyAutocompleteCode')->name('tenantCodeCompanyAutocompleteCode');

	Route::get('showTenantContractExpiryReport', 'BackOfficeReportController@showTenantContractExpiryReport')->name('showTenantContractExpiryReport');

	Route::post('tenantContractExpiryReportPdf', 'BackOfficeReportController@tenantContractExpiryReportPdf')->name('tenantContractExpiryReportPdf');

	Route::get('buildingNameReportAutocompleteCode', 'BackOfficeReportController@buildingNameReportAutocompleteCode')->name('buildingNameReportAutocompleteCode');

    Route::get('buildingNoReportAutocompleteCode', 'BackOfficeReportController@buildingNoReportAutocompleteCode')->name('buildingNoReportAutocompleteCode');
	Route::get('areReportAutocompleteCode', 'BackOfficeReportController@areReportAutocompleteCode')->name('areReportAutocompleteCode');

	Route::get('showTenantContractRenewalReport', 'BackOfficeReportController@showTenantContractRenewalReport')->name('showTenantContractRenewalReport');

	Route::post('tenantContractRenewalReportPdf', 'BackOfficeReportController@tenantContractRenewalReportPdf')->name('tenantContractRenewalReportPdf');

	Route::get('showEarlyTerminationReport', 'BackOfficeReportController@showEarlyTerminationReport')->name('showEarlyTerminationReport');

	Route::post('tenantEarlyTerminationReportPdf', 'BackOfficeReportController@tenantEarlyTerminationReportPdf')->name('tenantEarlyTerminationReportPdf');

	Route::get('showTerminationReport', 'BackOfficeReportController@showTerminationReport')->name('showTerminationReport');

	Route::post('tenantTerminationReportPdf', 'BackOfficeReportController@tenantTerminationReportPdf')->name('tenantTerminationReportPdf');

	Route::get('showEmployeeTenantContractReport', 'BackOfficeReportController@showEmployeeTenantContractReport')->name('showEmployeeTenantContractReport');

	Route::post('employeeTenantContractReportPdf', 'BackOfficeReportController@employeeTenantContractReportPdf')->name('employeeTenantContractReportPdf');

	Route::get('showDepositRentReport', 'BackOfficeReportController@showDepositRentReport')->name('showDepositRentReport');

	Route::post('depositRentReportPdf', 'BackOfficeReportController@depositRentReportPdf')->name('depositRentReportPdf');

	Route::get('buildingsCodeReportAutocompleteCode', 'BackOfficeReportController@buildingsCodeReportAutocompleteCode')->name('buildingsCodeReportAutocompleteCode');

	Route::get('showRentReceiptReport', 'BackOfficeReportController@showRentReceiptReport')->name('showRentReceiptReport');

	Route::post('rentReceiptReportPdf', 'BackOfficeReportController@rentReceiptReportPdf')->name('rentReceiptReportPdf');

	Route::get('rentBuildingAutocompleteCode', 'BackOfficeReportController@rentBuildingAutocompleteCode')->name('rentBuildingAutocompleteCode');

	Route::get('rentBuildingCodeAutocompleteCode', 'BackOfficeReportController@rentBuildingCodeAutocompleteCode')->name('rentBuildingCodeAutocompleteCode');


	Route::get('showGeneralReceiptReport', 'BackOfficeReportController@showGeneralReceiptReport')->name('showGeneralReceiptReport');

	Route::post('generalReceiptReportPdf', 'BackOfficeReportController@generalReceiptReportPdf')->name('generalReceiptReportPdf');

	Route::get('generalBuildingAutocompleteCode', 'BackOfficeReportController@generalBuildingAutocompleteCode')->name('generalBuildingAutocompleteCode');

	Route::get('generalBuildingCodeAutocompleteCode', 'BackOfficeReportController@generalBuildingCodeAutocompleteCode')->name('generalBuildingCodeAutocompleteCode');

	Route::get('showPaymentHistoryReport', 'BackOfficeReportController@showPaymentHistoryReport')->name('showPaymentHistoryReport');

	Route::post('paymentHistoryReportPdf', 'BackOfficeReportController@paymentHistoryReportPdf')->name('paymentHistoryReportPdf');

	Route::get('showLegalRentAmountReport', 'BackOfficeReportController@showLegalRentAmountReport')->name('showLegalRentAmountReport');

	Route::post('legalRentAmountReportPdf', 'BackOfficeReportController@legalRentAmountReportPdf')->name('legalRentAmountReportPdf');

	Route::get('showtenancyDetailsReport', 'BackOfficeReportController@showtenancyDetailsReport')->name('showtenancyDetailsReport');

	Route::post('tenancyDetailsReportPdf', 'BackOfficeReportController@tenancyDetailsReportPdf')->name('tenancyDetailsReportPdf');

	Route::get('tenancyBuildingCodeAutocompleteCode', 'BackOfficeReportController@tenancyBuildingCodeAutocompleteCode')->name('tenancyBuildingCodeAutocompleteCode');
	Route::get('tenancyBuildingReportAutocompleteCode', 'BackOfficeReportController@tenancyBuildingReportAutocompleteCode')->name('tenancyBuildingReportAutocompleteCode');
	Route::get('buildingTypeAutocompleteCode', 'BackOfficeReportController@buildingTypeAutocompleteCode')->name('buildingTypeAutocompleteCode');

	Route::get('tenantNameReportAutocompleteCode', 'BackOfficeReportController@tenantNameReportAutocompleteCode')->name('tenantNameReportAutocompleteCode');

	Route::get('showtenantReceivablesReport', 'BackOfficeReportController@showtenantReceivablesReport')->name('showtenantReceivablesReport');

	Route::post('tenantReceivablesReportPdf', 'BackOfficeReportController@tenantReceivablesReportPdf')->name('tenantReceivablesReportPdf');

	Route::get('showtenantReceivablesReportV2', 'BackOfficeReportController@showtenantReceivablesReportV2')->name('showtenantReceivablesReportV2');

	Route::post('tenantReceivablesReportPdfV2', 'BackOfficeReportController@tenantReceivablesReportPdfV2')->name('tenantReceivablesReportPdfV2');

	Route::get('showLegalReceivablesReportV2', 'BackOfficeReportController@showLegalReceivablesReportV2')->name('showLegalReceivablesReportV2');

	Route::post('legalReceivablesReportPdfV2', 'BackOfficeReportController@legalReceivablesReportPdfV2')->name('legalReceivablesReportPdfV2');

	Route::get('showNormalManagementReportV2', 'BackOfficeReportController@showNormalManagementReportV2')->name('showNormalManagementReportV2');

	Route::post('normalManagementReportV2Generate', 'BackOfficeReportController@normalManagementReportV2Generate')->name('normalManagementReportV2Generate');
	Route::get('normalManagementReportV2Stream', 'BackOfficeReportController@normalManagementReportV2Stream')->name('normalManagementReportV2Stream');
	Route::get('normalManagementReportV2Download/{token}', 'BackOfficeReportController@normalManagementReportV2Download')->name('normalManagementReportV2Download');

	Route::get('showLandlordTaxInvoiceReport', 'BackOfficeReportController@showLandlordTaxInvoiceReport')->name('showLandlordTaxInvoiceReport');
	Route::get('landlordTaxInvoiceReportStream', 'BackOfficeReportController@landlordTaxInvoiceReportStream')->name('landlordTaxInvoiceReportStream');
	Route::get('landlordTaxInvoiceReportDownload/{token}', 'BackOfficeReportController@landlordTaxInvoiceReportDownload')->name('landlordTaxInvoiceReportDownload');

	Route::resource('landlord-invoice-v2', 'LandlordInvoiceV2Controller')->except(['show']);
	Route::get('landlord-invoice-v2-contracts-by-vendor', 'LandlordInvoiceV2Controller@contractsByVendor')->name('landlordInvoiceV2ContractsByVendor');
	Route::get('landlord-invoice-v2-contract-details/{landlordContract}', 'LandlordInvoiceV2Controller@contractDetails')->name('landlordInvoiceV2ContractDetails');
	Route::get('landlord-invoice-v2-calculation-preview', 'LandlordInvoiceV2Controller@calculationPreview')->name('landlordInvoiceV2CalculationPreview');
Route::get('landlord-invoice-v2-overview-preview', 'LandlordInvoiceV2Controller@overviewPreview')->name('landlordInvoiceV2OverviewPreview');
	Route::get('landlord-invoice-v2/{landlordInvoiceV2}/print', 'LandlordInvoiceV2Controller@print')->name('landlordInvoiceV2Print');
	Route::post('landlord-invoice-v2/{landlordInvoiceV2}/post', 'LandlordInvoiceV2Controller@post')->name('landlordInvoiceV2Post');
	Route::get('showRentCollectionSummary', 'RentCollectionSummaryController@index')->name('showRentCollectionSummary');

	Route::get('showchequeReturnReport', 'BackOfficeReportController@showchequeReturnReport')->name('showchequeReturnReport');

	Route::post('chequeReturnReportPdf', 'BackOfficeReportController@chequeReturnReportPdf')->name('chequeReturnReportPdf');

	Route::get('showUnitTakeoverReport', 'BackOfficeReportController@showUnitTakeoverReport')->name('showUnitTakeoverReport');

	Route::post('unitTakeoverReportPdf', 'BackOfficeReportController@unitTakeoverReportPdf')->name('unitTakeoverReportPdf');

	Route::get('showLegalCaseReport', 'BackOfficeReportController@showLegalCaseReport')->name('showLegalCaseReport');

	Route::post('legalCaseReportPdf', 'BackOfficeReportController@legalCaseReportPdf')->name('legalCaseReportPdf');
	Route::get('accountCodeReceiptAutocompleteCode', 'BackOfficeReportController@accountCodeReceiptAutocompleteCode')->name('accountCodeReceiptAutocompleteCode');
	Route::get('showMonthlyTenancyReport', 'BackOfficeReportController@showMonthlyTenancyReport')->name('showMonthlyTenancyReport');

	Route::post('monthlyTenancyReportPdf', 'BackOfficeReportController@monthlyTenancyReportPdf')->name('monthlyTenancyReportPdf');

	Route::get('showMeraRentReceiptReport', 'BackOfficeReportController@showMeraRentReceiptReport')->name('showMeraRentReceiptReport');

	Route::post('meraRentReceiptReportDownload', 'BackOfficeReportController@meraRentReceiptReportDownload')->name('meraRentReceiptReportDownload');

	Route::get('showtenancyDetailsMeraReport', 'BackOfficeReportController@showtenancyDetailsMeraReport')->name('showtenancyDetailsMeraReport');

	Route::post('tenancyDetailsMeraReportDownload', 'BackOfficeReportController@tenancyDetailsMeraReportDownload')->name('tenancyDetailsMeraReportDownload');

	Route::get('showLeasingConsultantPerformance', 'BackOfficeReportController@showLeasingConsultantPerformance')->name('showLeasingConsultantPerformance');
	Route::get('leasingConsultantPerformanceData', 'BackOfficeReportController@leasingConsultantPerformanceData')->name('leasingConsultantPerformanceData');

	/***************Report Ends******************/
	/*******Contract Details Starts**********/

Route::resource('contract-details', 'TenantContractDetailsController');
//Building Autocomplete
Route::get('/allBuildingsAutocomplete', 'TenantContractDetailsController@allBuildingsAutocomplete')->name('allBuildingsAutocomplete');
//summaryRemark
Route::post('/GetSummaryRemark', 'TenantContractDetailsController@GetSummaryRemark')->name('GetSummaryRemark');

/*** Unit Detail ****/
Route::post('/allUnitsDetail', 'TenantContractDetailsController@allUnitsDetail')->name('allUnitsDetail');

/*** Tenant Detail ****/
Route::post('/allTenantsDetail', 'TenantContractDetailsController@allTenantsDetail')->name('allTenantsDetail');

/*** Agreement Detail ****/
Route::post('/contractDetails', 'TenantContractDetailsController@contractDetails')->name('contractDetails');

//Unit Autocomplete
Route::get('/allUnitsAutocomplete', 'TenantContractDetailsController@allUnitsAutocomplete')->name('allUnitsAutocomplete');
Route::post('/getBuildingDetails', 'TenantContractDetailsController@getBuildingDetails')->name('getBuildingDetails');
Route::get('/allTenantsAutocomplete', 'TenantContractDetailsController@allTenantsAutocomplete')->name('allTenantsAutocomplete');
Route::post('/getUnitDetails', 'TenantContractDetailsController@getUnitDetails')->name('getUnitDetails');
Route::post('/getBuildingCompleteDetails', 'TenantContractDetailsController@getBuildingCompleteDetails')->name('getBuildingCompleteDetails');

/*******Contract Details Ends**********/
//newly added starts
	Route::get('showRentalIncomeReport', 'BackOfficeReportController@showRentalIncomeReport')->name('showRentalIncomeReport');
	Route::post('rentalIncomeReportPdf', 'BackOfficeReportController@rentalIncomeReportPdf')->name('rentalIncomeReportPdf');
	Route::get('showExpenseDetailsReport', 'BackOfficeReportController@showExpenseDetailsReport')->name('showExpenseDetailsReport');
	Route::post('expenseDetailsReportPdf', 'BackOfficeReportController@expenseDetailsReportPdf')->name('expenseDetailsReportPdf');
	//newly added ends

    Route::match(['get', 'post'],'contractGlobleSearch', 'GlobalSearchController@contractGlobleSearch')->name('contractGlobleSearch');
    

});
