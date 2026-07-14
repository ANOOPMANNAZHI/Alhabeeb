<?php
// Renewal Index
Breadcrumbs::for('tenantRenewal.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant - Due For Renewal', route('tenantRenewal.index'));
});

// Renewal Index
Breadcrumbs::for('tenantContract.underRenewal', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant - Contract Under Renewal', route('tenantContract.underRenewal'));
});


//Renewal View
Breadcrumbs::for('tenantRenewal.show', function ($trail, $tenantRenewal) {
    $trail->parent('tenantRenewal.index');
    $trail->push('View Tenant Renewal Due', route('tenantRenewal.show',$tenantRenewal->id));
});

//Renewal Contract 
Breadcrumbs::for('tenantRenewalContract', function ($trail) {
    $trail->parent('home');
    $trail->push('Contract Renewal', route('tenantRenewalContract'));
});

//Renewal Contract View
Breadcrumbs::for('newContractShow', function ($trail, $renewContract,$stage) {
    $trail->parent('tenantRenewalContract');
    $trail->push('Tenant Renewal Contract View', route('newContractShow',[$renewContract->id,$stage]));
});
//Renewal Contract 
Breadcrumbs::for('renewalContract', function ($trail, $renewContract) {
    $trail->parent('tenantRenewalContract');
    $trail->push('Renewal Contract Creation', route('tenantRenewalNewContract',$renewContract));
});
//Renewal Contract Update
Breadcrumbs::for('renewalContractUpdate', function ($trail, $renewContract) {
    $trail->parent('tenantRenewalContract');
    $trail->push('Renewal Contract Update', route('tenantRenewalNewContract',$renewContract));
});
//Renewal Contract Approval
Breadcrumbs::for('renewalContractApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Renewal Approval', route('renewalContractApproval'));
});
//Renewal Contract Approval View
Breadcrumbs::for('newContractApprovalShow', function ($trail, $renewContract,$stage) {
    $trail->parent('renewalContractApproval');
    $trail->push('Tenant Renewal Contract Approval View', route('newContractShow',[$renewContract->id,$stage]));
});
//renewedContract
Breadcrumbs::for('renewedContract', function ($trail) {
    $trail->parent('home');
    $trail->push('Renewed Contract', route('tenantRenewedContract'));
});
//renewedContract View
Breadcrumbs::for('renewedContractShow', function ($trail, $renewContract) {
    $trail->parent('renewedContract');
    $trail->push('Tenant Renewed Contract View', route('renewedContractShow',$renewContract->id));
});
/*
*
*
* Landlord Renewal
*
*/
// Renewal Index
Breadcrumbs::for('landlordRenewal.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Due For Renewal', route('landlordRenewal.index'));
});

//Renewal Edit
Breadcrumbs::for('landlordRenewal.edit', function ($trail, $landlordRenewal) {
    $trail->parent('landlordRenewal.index');
    $trail->push('Edit Landlord Renewal', route('landlordRenewal.edit', $landlordRenewal->id));
});

//Renewal Create
Breadcrumbs::for('landlordRenewal.create', function ($trail) {
    $trail->parent('landlordRenewal.index');
    $trail->push('Create Landlord Renewal', route('landlordRenewal.create'));
});



//Due For Renewal  View-reshma
Breadcrumbs::for('landlordRenewal.show', function ($trail) {
    $trail->parent('landlordRenewal.index');
    $trail->push('View Landlord Renewal Due');
});

//Landlord Contract Renewal-reshma
Breadcrumbs::for('landlordRenewalContract', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Contract Renewal', route('renewalContract'));
});
//Landlord Contract View-reshma
Breadcrumbs::for('landlordRenewalShow', function ($trail, $renewContract) {
    $trail->parent('landlordRenewalContract');
    $trail->push('Landlord Contract Renewal View', route('renewalContract',[$renewContract->id]));
});


//Renewal Contract edit-reshma
Breadcrumbs::for('landlordRenewalEdit', function ($trail, $renewContract) {
    $trail->parent('landlordRenewalContract');
    $trail->push('Landlord Contract Renewal Edit', route('renewalContract',[$renewContract->id]));
});
//Renewal Contract Renew-reshma
Breadcrumbs::for('landlordRenewContract', function ($trail, $renewContract) {
    $trail->parent('landlordRenewalContract');
    $trail->push('Landlord Contract Renew', route('renewalContract',[$renewContract->id]));
});

//Renewal Contract Approval-reshma
Breadcrumbs::for('landlordContractApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Contract Approval', route('landlordContractApproval'));
});
//Renewal Contract Approval View-reshma
Breadcrumbs::for('landlordApprovalShow', function ($trail, $renewContract) {
    $trail->parent('landlordContractApproval');
    $trail->push('Landlord Contract Approval View', route('landlordContractApproval',[$renewContract->id]));
});
// landlord contract  Renewed grid-reshma
Breadcrumbs::for('landlordContractRenewed', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Renewed Contract Grid', route('landlordContractRenewed'));
}); 
//landlord Contract Renewed View-reshma
Breadcrumbs::for('landlordContractShow', function ($trail, $renewContract) {
    $trail->parent('landlordContractRenewed');
    $trail->push('Landlord Renewed Contract View', route('landlordRenewedContract',[$renewContract->id]));
});


//Renewal Contract 
Breadcrumbs::for('landlordRenewalNewContract', function ($trail,$renewContract,$status) {
    $trail->parent('landlordRenewalContract');
    $trail->push('Renewal Contract', route('landlordRenewalNewContract',[$renewContract,$status]));
});


// --------------- Jackson ---------------------------

Breadcrumbs::for('tenant-contract.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Contract', route('tenant-contract.index'));
});

Breadcrumbs::for('tenantPdcView', function ($trail, $id) {
    $trail->parent('tenant-contract.index');
    $trail->push('Add PDC', route('tenantPdcView',$id));
});
// Edit
Breadcrumbs::for('tenant-contract.edit', function ($trail, $tenantContract) {
    $trail->parent('tenant-contract.index');
    $trail->push('Edit', route('tenant-contract.edit', $tenantContract->id));
});

// Create
Breadcrumbs::for('tenant-contract.create', function ($trail) {
    $trail->parent('tenant-contract.index');
    $trail->push('Create', route('tenant-contract.create'));
});
Breadcrumbs::for('tenant-contract-show', function ($trail, $tenantContract) {
    $trail->parent('tenant-contract.index');
    $trail->push('View', route('tenant-contract.show',$tenantContract->id));
});
// Revoke
Breadcrumbs::for('tenant-contract.revoke', function ($trail, $tenantContract) {
    $trail->parent('tenant-contract.index');
    $trail->push('Revoke', route('tenantRevoke', $tenantContract->id));
});
// Revoke Approve list

Breadcrumbs::for('tenantContractApprovedRevoke', function ($trail,$title) {
    $trail->parent('home');
    $trail->push($title, route('tenantContractApprovedRevoke'));
});
// Revoke Pending list

Breadcrumbs::for('tenantContractPendingRevoke', function ($trail,$title) {
    $trail->parent('home');
    $trail->push($title, route('tenantContractPendingRevoke'));
});
// Revoke Approve
Breadcrumbs::for('leadAssign.revokeProcess', function ($trail,$route, $salesEnquiry,$workFlowStage,$title) {
	if($route == 'tenantContractPendingRevoke')
			$parentTitle= 'Tenant Direct/Revoke Contract Pending List';
	if($route == 'tenantContractApprovedRevoke')
			$parentTitle= 'Tenant Direct/Revoke Contract Approve';
			
    $trail->parent($route, $parentTitle);
    $trail->push($title, route('leadAssign.revokeProcess',[$route,$salesEnquiry,$workFlowStage]));
});
//-------------------------- End ----------------------
/* Land lord Direct Contract */
Breadcrumbs::for('landlord-contract.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Contract', route('landlord-contract.index'));
});
// Edit
Breadcrumbs::for('landlord-contract-edit', function ($trail, $landlordContract) {
    $trail->parent('landlord-contract.index');
    $trail->push('Edit Landlord Contract', route('landlord-contract.edit', $landlordContract));
});

// Create
Breadcrumbs::for('landlord-contract.create', function ($trail) {
    $trail->parent('landlord-contract.index');
    $trail->push('Create Landlord Contract', route('landlord-contract.create'));
});
// Edit
Breadcrumbs::for('landlord-contract-show', function ($trail, $landlordContract) {
    $trail->parent('landlord-contract.index');
    $trail->push('View Landlord Contract', route('landlord-contract.show',$landlordContract->id));
});
/*
*
*
* Tenant Termination
*
*/
// Termination Index
Breadcrumbs::for('tenantTermination.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Open For Termination ', route('tenantTermination.index'));
});

//Termination Edit
Breadcrumbs::for('tenantTermination.edit', function ($trail, $tenantTermination) {
    $trail->parent('tenantTermination.index');
    $trail->push('Edit Early Termination Requests', route('tenantTermination.edit', $tenantTermination->id));
});

//Termination Create
Breadcrumbs::for('tenantTermination.create', function ($trail) {
    $trail->parent('tenantTermination.index');
    $trail->push('Early Termination Request', route('tenantTermination.create'));
});


//Termination View
Breadcrumbs::for('tenantTermination.show', function ($trail, $tenantContract) {
    $trail->parent('tenantTermination.index');
    $trail->push('Open For Termination View', route('tenantTermination.show',$tenantContract->id));
});
// Termination Index
Breadcrumbs::for('tenantTerminationApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Termination Approval', route('tenantTerminationApproval'));
});
//Termination View
Breadcrumbs::for('tenantTerminationApprovalView', function ($trail, $tenantContract) {
    $trail->parent('tenantTerminationApproval');
    $trail->push('Termination Approval View', route('tenantTerminationApprovalView',$tenantContract->id));
});
// Handover Unassigned
Breadcrumbs::for('handoverUnassigned', function ($trail) {
    $trail->parent('home');
    $trail->push('Handover Unassigned', route('handoverUnassigned'));
});
//Handover Unassigned View
Breadcrumbs::for('handoverUnassignedView', function ($trail, $termination) {
    $trail->parent('handoverUnassigned');
    $trail->push('Handover Unassigned View', route('handoverUnassignedView',$termination->id));
});
// Handover Assigned
Breadcrumbs::for('handoverAssigned', function ($trail) {
    $trail->parent('home');
    $trail->push('Handover Assigned', route('handoverAssigned'));
});
//Handover Assigned View
Breadcrumbs::for('handoverAssignedView', function ($trail, $termination) {
    $trail->parent('handoverAssigned');
    $trail->push('Handover Assigned View', route('handoverAssignedView',$termination->id));
});
// Inspection Create 
Breadcrumbs::for('handoverAssignedInspection', function ($trail,$termination) {
    $trail->parent('handoverAssigned');
    $trail->push('Vacating Unit Inspection Details', route('handoverAssignedInspection',$termination->id));
});
// Inspection Edit 
Breadcrumbs::for('handoverAssignedInspectionEdit', function ($trail,$termination) {
    $trail->parent('handoverAssigned');
    $trail->push('Vacating Unit Inspection Details', route('handoverAssignedInspectionEdit',$termination->id));
});
// takeoverForTermination
Breadcrumbs::for('takeoverForTermination', function ($trail) {
    $trail->parent('home');
    $trail->push('Takeover For Termination', route('takeoverForTermination'));
});
//takeoverForTermination View
Breadcrumbs::for('takeoverForTerminationView', function ($trail, $termination) {
    $trail->parent('takeoverForTermination');
    $trail->push('Taken Over For Termination View', route('takeoverForTerminationView',$termination->id));
});
// Terminated Contract
Breadcrumbs::for('tenantTerminatedContract', function ($trail) {
    $trail->parent('home');
    $trail->push('Terminated Tenant Contracts', route('tenantTerminatedContract'));
});
//Terminated Contract View
Breadcrumbs::for('tenantTerminatedContractView', function ($trail, $termination) {
    $trail->parent('tenantTerminatedContract');
    $trail->push('Terminated Tenant Contract View', route('tenantTerminatedContractView',$termination->id));
});
/*
*
*
* Landlord Termination
*
*/
// Landlord Termination Index
Breadcrumbs::for('landlordTermination.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Open For Termination ', route('landlordTermination.index'));
});
//Termination View
Breadcrumbs::for('landlordTermination.show', function ($trail, $termination) {
    $trail->parent('landlordTermination.index');
    $trail->push('Open For Termination View', route('landlordTermination.show',$termination->id));
});
//Termination Create
Breadcrumbs::for('landlordTermination.create', function ($trail) {
    $trail->parent('landlordTermination.index');
    $trail->push('Create Early Termination Request', route('landlordTermination.create'));
});
//Termination Edit
Breadcrumbs::for('landlordTermination.edit', function ($trail, $landlordTermination,$current) {
    $trail->parent($current);
    $trail->push('Edit Early Termination Request', route('landlordTermination.edit', $landlordTermination->id));
});
// Termination Verify
Breadcrumbs::for('LCTerminationVerify', function ($trail) {
    $trail->parent('home');
    $trail->push('LC Termination Verification', route('LCTerminationVerify'));
});
//Termination Verify View
Breadcrumbs::for('LCTerminationVerifyView', function ($trail, $termination) {
    $trail->parent('LCTerminationVerify');
    $trail->push('LC Termination Verification View', route('LCTerminationVerifyView',$termination->id));
});
// Termination Approval
Breadcrumbs::for('LCTerminationApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Approval', route('LCTerminationApproval'));
});
//Termination Approval View
Breadcrumbs::for('LCTerminationApprovalView', function ($trail, $termination) {
    $trail->parent('LCTerminationApproval');
    $trail->push('Approval View', route('LCTerminationApprovalView',$termination->id));
});
// Terminated landlord
Breadcrumbs::for('TerminatedContractLandlord', function ($trail) {
    $trail->parent('home');
    $trail->push('Terminated Landlord Contract', route('TerminatedContractLandlord'));
});
//Termination Verify View
Breadcrumbs::for('TerminatedContractLandlordView', function ($trail, $termination) {
    $trail->parent('TerminatedContractLandlord');
    $trail->push('Terminated Landlord Contract View', route('TerminatedContractLandlordView',$termination->id));
});
//Termination Landlord By tenant Contract View
Breadcrumbs::for('TerminatedContractLandlordByTenantView', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationVerify');
    $trail->push(' LC Termination Verification View', route('LCTerminationVerifyView',$termination->id));
    $trail->push('Tenant Contract View', route('tenantContractByLandlord',[$tenantContract->id,$termination->id,0]));
});
//Termination Landlord By tenant Pdc View
Breadcrumbs::for('LandlordByTenantPdcView', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationVerify');
    $trail->push(' LC Termination Verification View', route('LCTerminationVerifyView',$termination->id));
    $trail->push('Tenant Contract Pdc View', route('tenantContractByLandlord',[$tenantContract->id,$termination->id,0]));
});
//Termination Landlord By tenant Pdc View
Breadcrumbs::for('LandlordByTenantInvoiceView', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationVerify');
    $trail->push('LC Termination Verification View', route('LCTerminationVerifyView',$termination->id));
    $trail->push('Tenant Contract Invoice View', route('tenantContractByLandlord',[$tenantContract->id,$termination->id,0]));
});


//Termination Landlord By tenant Contract View
Breadcrumbs::for('tenantContractByLandlordApproval', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationApproval');
    $trail->push('Approval View', route('LCTerminationApprovalView',$termination->id));
    $trail->push('Tenant Contract View', route('tenantContractByLandlordApproval',[$tenantContract->id,$termination->id,1]));
});
//Termination Landlord By tenant Pdc View
Breadcrumbs::for('LandlordApprovalByTenantPdcView', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationApproval');
    $trail->push('Approval View', route('LCTerminationApprovalView',$termination->id));
    $trail->push('Tenant Contract Pdc View', route('LandlordApprovalByTenantPdcView',[$tenantContract->id,$termination->id,1]));
});
//Termination Landlord By tenant Pdc View
Breadcrumbs::for('LandlordApprovalByTenantInvoiceView', function ($trail, $tenantContract,$termination) {
    $trail->parent('LCTerminationApproval');
    $trail->push('Approval View', route('LCTerminationApprovalView',$termination->id));
    $trail->push('Tenant Contract Invoice View', route('LandlordApprovalByTenantInvoiceView',[$tenantContract->id,$termination->id,1]));
});
// CHeque Bounce
Breadcrumbs::for('tenantPdcBounce', function ($trail) {
    $trail->parent('home');
    $trail->push('Cheque Bounce', route('tenantPdcBounce'));
});
// Tenant Invoice 
Breadcrumbs::for('tenant-invoice', function ($trail, $id) {
    $trail->parent('tenant-contract.index');
    $trail->push('Tenant Invoice',route('invoice.show',$id));
});
// Tenant Invoice view
Breadcrumbs::for('tenant-invoice-view', function ($trail, $id, $tenant_contract_id) {
	$trail->parent('tenant-contract.index');
    $trail->push(' Tenant Invoice',route('invoice.show',$tenant_contract_id));
    $trail->push('Tenant Invoice View', route('tenantRentInvoiceDetails',$id));
});
// Tenant Contract PDCs
Breadcrumbs::for('tenant-pdc', function ($trail,  $tenant_contract_id) {
   // $trail->parent('tenant-contract.index');
    $trail->parent('tenant-contract.index');
    $trail->push('Tenant Pdc',route('invoice.show',$tenant_contract_id));
});

/*
*
* Key Module
*
*/
//Key Accept  Create
Breadcrumbs::for('keyManagement.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Key List', route('keyManagement.index'));
});
//Key Accept  Create
Breadcrumbs::for('keyManagement.create', function ($trail) {
    $trail->parent('keyManagement.index');
    $trail->push('Create Key Accept', route('keyManagement.create'));
});


/** Routines starts **/

Breadcrumbs::for('pdcPosting', function ($trail) {
    $trail->parent('home');
    $trail->push('PDC Bulk Posting', route('pdcPosting'));
});
Breadcrumbs::for('rentalIncomePosting', function ($trail) {
    $trail->parent('home');
    $trail->push('Rental Income Posting', route('rentalIncomePosting'));
});
Breadcrumbs::for('tenantReceiptPosting', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Receipt Posting', route('tenantReceiptPosting'));
});
Breadcrumbs::for('costRecognition', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Invoice Bulk Posting', route('costRecognition'));
});
/** Routines ends **/

/** Maintenance Payment Starts **/
Breadcrumbs::for('maintenancePayment.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Payment', route('maintenancePayment.index'));
});

Breadcrumbs::for('maintenancePayment.edit', function ($trail, $maintenancePayment) {
    $trail->parent('maintenancePayment.index');
    $trail->push('Edit Maintenance Payment', route('maintenancePayment.edit', $maintenancePayment->id));
});

Breadcrumbs::for('maintenancePayment.create', function ($trail) {
    $trail->parent('maintenancePayment.index');
    $trail->push('Maintenance Payment Add', route('maintenancePayment.create'));
});

Breadcrumbs::for('maintenancePayment.show', function ($trail) {
    $trail->parent('maintenancePayment.index');
    $trail->push('Maintenance Payment View');
});
//Maintenance Payment Approval
Breadcrumbs::for('maintenancePaymentApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Payment Approval', route('maintenancePaymentApproval'));
});
//Maintenance Payment Approval View
Breadcrumbs::for('maintenancePaymentApprovalShow', function ($trail, $maintenancePaymentApproval) {
    $trail->parent('maintenancePaymentApproval');
    $trail->push('Maintenance Payment Approval View', route('maintenancePaymentApproval',[$maintenancePaymentApproval->id]));
});
/** Maintenance Payment ends **/

/** Landlord Payment Starts **/
Breadcrumbs::for('landlordPayment.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Payment', route('landlordPayment.index'));
});

Breadcrumbs::for('landlordPayment.edit', function ($trail, $landlordPayment) {
       $trail->parent('landlordPayment.index');
    $trail->push('Edit Landlord Payment', route('landlordPayment.edit', $landlordPayment->id));
});

Breadcrumbs::for('landlordPayment.create', function ($trail) {
    $trail->parent('landlordPayment.index');
    $trail->push('Add Landlord Payment', route('landlordPayment.create'));
});

Breadcrumbs::for('landlordPayment.show', function ($trail) {
    $trail->parent('landlordPayment.index');
    $trail->push('Landlord Payment View');
});
//Landlord Payment Approval
Breadcrumbs::for('landlordPaymentApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Payment Approval', route('landlordPaymentApproval'));
});
//Landlord Payment Approval View
Breadcrumbs::for('landlordPaymentApprovalShow', function ($trail, $landlordPaymentApproval) {
    $trail->parent('landlordPaymentApproval');
    $trail->push('Landlord Payments Approval View', route('landlordPaymentApproval',[$landlordPaymentApproval->id]));
});
/** Landlord Payment ends **/

/** Deposit Refund Starts **/
Breadcrumbs::for('depositRefund.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Deposit Refund', route('depositRefund.index'));
});

Breadcrumbs::for('depositRefund.edit', function ($trail, $depositRefund) {
    $trail->parent('depositRefund.index');
    $trail->push('Edit Deposit Refund', route('depositRefund.edit', $depositRefund->id));
});

Breadcrumbs::for('depositRefund.create', function ($trail) {
    $trail->parent('depositRefund.index');
    $trail->push('Deposit Refund Add', route('depositRefund.create'));
});

Breadcrumbs::for('depositRefund.show', function ($trail) {
    $trail->parent('depositRefund.index');
    $trail->push('Deposit Refund View');
});
//Deposit Refund Approval
Breadcrumbs::for('depositRefundApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Deposit Refund Approval', route('depositRefundApproval'));
});
//Deposit Refund Approval View
Breadcrumbs::for('depositRefundApprovalShow', function ($trail, $depositRefundApproval) {
    $trail->parent('depositRefundApproval');
    $trail->push('Deposit Refund Approval View', route('depositRefundApproval',[$depositRefundApproval->id]));
});
/** Deposit Refund ends **/

/** Receipt list **/

Breadcrumbs::for('receiptsTabViewList', function ($trail) {
    $trail->parent('home');
    $trail->push('Receipt', route('receiptsTabViewList'));
});
Breadcrumbs::for('receiptsRequestForApprovalTabViewList', function ($trail) {
    $trail->parent('home');
    $trail->push('Receipt Approval', route('receiptsRequestForApprovalTabViewList'));
});
Breadcrumbs::for('addRentReceipt', function ($trail) {
    $trail->parent('receiptsTabViewList');
    $trail->push('Add Rent Receipt', route('rentReceiptGeneration.create'));
});
Breadcrumbs::for('viewReceipt', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsTabViewList');
    $trail->push('View Rent Receipt', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('viewReceipts', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsRequestForApprovalTabViewList');
    $trail->push('View Rent Receipt Approval', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('viewDepositReceipt', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsTabViewList');
    $trail->push('View Deposit Receipt', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('viewDepositReceipts', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsRequestForApprovalTabViewList');
    $trail->push('View Deposit Receipt Approval', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('viewGeneralReceipt', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsTabViewList');
    $trail->push('View General Receipt', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('viewGeneralReceipts', function ($trail,$rentReceiptInfo) {
    $trail->parent('receiptsRequestForApprovalTabViewList');
    $trail->push('View General Receipt Approval', route('rentReceiptGeneration.show',$rentReceiptInfo->id));
});
Breadcrumbs::for('addGeneralReceipt', function ($trail) {
    $trail->parent('receiptsTabViewList');
    $trail->push('Add General Receipt', route('addGeneralReceipt'));
});
Breadcrumbs::for('addDepositReceipt', function ($trail) {
    $trail->parent('receiptsTabViewList');
    $trail->push('Add Deposit Receipt', route('addDepositReceipt'));
});
//Amc Contract Edit
Breadcrumbs::for('editDepositReceipt', function ($trail, $depositReceiptInfo,$current) {
    $trail->parent('receiptsTabViewList');
    $trail->push('Edit Deposit Receipt', route('updateDepositReceipt', $depositReceiptInfo->id));
});
/** Receipt End **/
/******************************************************************/





// landlordInvoice Approval List 
Breadcrumbs::for('landlordInvoicesApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Invoice Approval', route('landlordInvoiceApproval'));
});

//landlordInvoice  View
Breadcrumbs::for('landlordInvoicesApproval.show', function ($trail) {
    $trail->parent('landlordInvoicesApproval');
    $trail->push('Landlord Invoice Approval View');
});




//landlordInvoice  Edit
Breadcrumbs::for('landlordInvoice.edit', function ($trail, $landlordContract) {
    $trail->parent('landlord-contract.index');
    $trail->push('Edit Landlord Invoice', route('landlordInvoice.edit', $landlordContract->id));
});

//landlordInvoice  Create
Breadcrumbs::for('landlordInvoice.create', function ($trail,$landlordContract) {
    $trail->parent('landlord-contract.index');
    $trail->push('Add Landlord Invoice', route('landlordInvoiceGenerate',$landlordContract->id));
});

//landlordInvoice  View
Breadcrumbs::for('landlordInvoice.show', function ($trail) {
    $trail->parent('landlord-contract.index');
    $trail->push('View Landlord Invoice Booking');
});


//generalLedger  Edit
Breadcrumbs::for('generalLedger.edit', function ($trail,$generalLedger) {
    $trail->parent('generalLedger.index');
    $trail->push('Edit General Ledger', route('generalLedger.edit', $generalLedger));
});

//generalLedger  Create
Breadcrumbs::for('generalLedger.create', function ($trail) {
    $trail->parent('generalLedger.index');
    $trail->push('Add General Ledger');
});

//generalLedger  View
Breadcrumbs::for('generalLedger.show', function ($trail) {
    $trail->parent('generalLedger.index');
    $trail->push('View General Ledger');
});

//generalLedger  
Breadcrumbs::for('generalLedger.index', function ($trail) {
    $trail->parent('home');
    $trail->push('General Ledger',route('generalLedger.index'));
});

//generalLedger  View
Breadcrumbs::for('generalLedgerApproval.show', function ($trail) {
    $trail->parent('generalLedgerApproval.index');
    $trail->push('View General Ledger Approval');
});

//generalLedger  
Breadcrumbs::for('generalLedgerApproval.index', function ($trail) {
    $trail->parent('home');
    $trail->push('General Ledger Approval',route('generalLedger.index'));
});
Breadcrumbs::for('massMail.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Mass Mail', route('massMail.index'));
});
Breadcrumbs::for('massMail.create', function ($trail) {
    $trail->parent('massMail.index');
    $trail->push('Compose Mail', route('massMail.create'));
});
Breadcrumbs::for('massMail.view', function ($trail,$massMail) {
    $trail->parent('massMail.index');
    $trail->push('Compose Mail View', route('massMail.show',[$massMail->id]));
});
Breadcrumbs::for('renewalPdc', function ($trail,$parent,$params) {
    $trail->parent($parent);
    $trail->push('Tenant PDC', route('pdcGeneration',[$params[0],$params[1],$params[2]]));
});

Breadcrumbs::for('tenantinvoice', function ($trail,$parent,$params) {
    $trail->parent($parent);
    $trail->push('Tenant Invoice', route('invoiceGeneration',[$params[0],$params[1],$params[2]]));
});

/************Report starts************/
Breadcrumbs::for('showTenantContractReport', function ($trail) {
    $trail->parent('home');
    $trail->push('New Tenant For period',route('showTenantContractReport'));
});
Breadcrumbs::for('showCorporateTenantReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Corporate Tenant',route('showCorporateTenantReport'));
});
Breadcrumbs::for('showTenantContractExpiryReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on Contract Expiry',route('showTenantContractExpiryReport'));
});

Breadcrumbs::for('showTenantContractRenewalReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Contract Renewal Status',route('showTenantContractRenewalReport'));
});
Breadcrumbs::for('showEarlyTerminationReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Early Termination Report',route('showEarlyTerminationReport'));
});

Breadcrumbs::for('showTerminationReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Termination Report',route('showTerminationReport'));
});

Breadcrumbs::for('showEmployeeTenantContractReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on flats Rented - Employee wise',route('showEmployeeTenantContractReport'));
});
Breadcrumbs::for('showDepositRentReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on Deposit for rent / E,W',route('showDepositRentReport'));
});

Breadcrumbs::for('showRentReceiptReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Rent Receipt',route('showRentReceiptReport'));
});
Breadcrumbs::for('showGeneralReceiptReport', function ($trail) {
    $trail->parent('home');
    $trail->push('General Receipt',route('showGeneralReceiptReport'));
});
Breadcrumbs::for('showPaymentHistoryReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenants Payment History',route('showPaymentHistoryReport'));
});
Breadcrumbs::for('showLegalRentAmountReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Rent Amount collected through Legal Case',route('showLegalRentAmountReport'));
});

Breadcrumbs::for('showtenancyDetailsReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenancy Details by Building Wise',route('showtenancyDetailsReport'));
});

Breadcrumbs::for('showtenantReceivablesReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Receivables',route('showtenantReceivablesReport'));
});

Breadcrumbs::for('showtenantReceivablesReportV2', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Receivable v2',route('showtenantReceivablesReportV2'));
});

Breadcrumbs::for('showLegalReceivablesReportV2', function ($trail) {
    $trail->parent('home');
    $trail->push('Legal Receivable v2', route('showLegalReceivablesReportV2'));
});

Breadcrumbs::for('showNormalManagementReportV2', function ($trail) {
    $trail->parent('home');
    $trail->push('Normal Management Report v2', route('showNormalManagementReportV2'));
});

Breadcrumbs::for('showchequeReturnReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Cheque Return Statement',route('showchequeReturnReport'));
});

Breadcrumbs::for('showUnitTakeoverReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Unit Take Over Status',route('showUnitTakeoverReport'));
});

Breadcrumbs::for('showLegalCaseReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Legal Case',route('showLegalCaseReport'));
});
/************Report ends************/
// TenantContract Details Index
Breadcrumbs::for('contract-details.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Building/Unit/Tenant', route('contract-details.index'));
});

//newly added starts

Breadcrumbs::for('showRentalIncomeReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Rental Income',route('showRentalIncomeReport'));
});

Breadcrumbs::for('showExpenseDetailsReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Expense Details',route('showExpenseDetailsReport'));
});

//newly added ends
//Global Search
Breadcrumbs::for('contractGlobleSearch', function ($trail) {
    $trail->parent('home');
    $trail->push('Global Search',route('contractGlobleSearch'));
});

Breadcrumbs::for('showMonthlyTenancyReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Monthly Tenancy Details', route('showMonthlyTenancyReport'));
});

Breadcrumbs::for('showMeraRentReceiptReport', function ($trail) {
    $trail->parent('home');
    $trail->push('MERA Rent Receipt Report', route('showMeraRentReceiptReport'));
});

Breadcrumbs::for('showLeasingConsultantPerformance', function ($trail) {
    $trail->parent('home');
    $trail->push('Leasing Consultant Performance', route('showLeasingConsultantPerformance'));
});