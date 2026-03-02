<?php
// Enquiy Index
Breadcrumbs::for('enquiry.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Enquiry ', route('enquiry.index'));
});

//Enquiy Edit
Breadcrumbs::for('enquiry.edit', function ($trail, $enquiy,$current) {
    $trail->parent($current);
    $trail->push('Edit Enquiry', route('enquiry.edit', $enquiy->id));
});

//Enquiy Create
Breadcrumbs::for('enquiry.create', function ($trail) {
    $trail->parent('enquiry.index');
    $trail->push('Create Enquiry', route('enquiry.create'));
});

//Enquiy View
Breadcrumbs::for('enquiry.show', function ($trail, $enquiy) {
    $trail->parent('enquiry.index');
    $trail->push('View Enquiry', route('enquiry.show',$enquiy->id));
});

//Tenant Enquiry
Breadcrumbs::for('enquiry.tenant', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Enquiry ', route('tenantEnquiries'));
});
// Pending Approval details page
Breadcrumbs::for('tenantPendingStageInfo', function ($trail,$details,$stage) { 
    $trail->parent('finalDocPendingApprovalList'); 
    $trail->push('Final Documentation View', route('leadAssign.tenantPendingStageInfo',[$details,$stage]));
});

//Landlord Enquiry
Breadcrumbs::for('enquiry.landlord', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Enquiry ', route('landlordEnquiries'));
});

Breadcrumbs::for('call-center-dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Call Center Dashboard ', route('callCenter'));
});
/********************************* Tenant Enquiry ******************************************************/

//Unassigned Enquiry Tenant 
Breadcrumbs::for('leadAssign.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Unassigned Enquiry', route('leadAssign.index'));
});

//Assigned  Tenant Index
Breadcrumbs::for('leadAssign.assignedList', function ($trail) {
    $trail->parent('home');
    $trail->push('Assigned Enquiry', route('leadAssign.assignedList'));
});
//inprogress  Tenant Index
Breadcrumbs::for('inprogressList', function ($trail) {
    $trail->parent('home');
    $trail->push('In Progress Enquiry', route('inprogressList'));
});
//preliminary Document  Tenant Index
Breadcrumbs::for('documentationList', function ($trail) {
    $trail->parent('home');
    $trail->push('Preliminary Documentation', route('documentationList'));
});
//Approval Document Tenant Index
Breadcrumbs::for('preliminaryApprovalList', function ($trail) {
    $trail->parent('home');
    $trail->push('Preliminary Documentation Approval', route('preliminaryApprovalList'));
});
//Final Document Tenant Index
Breadcrumbs::for('finalDocumentationList', function ($trail) {
    $trail->parent('home');
    $trail->push('Final Documentation', route('finalDocumentationList'));
});
//Final Document Pending Tenant Index
Breadcrumbs::for('finalDocPendingApprovalList', function ($trail) {
    $trail->parent('home');
    $trail->push('Final Documentation Pending List', route('finalDocPendingApprovalList'));
});
//Final ApprovalDocument Tenant Index
Breadcrumbs::for('finalApprovalList', function ($trail) {
    $trail->parent('home');
    $trail->push('Final Documentation Approval', route('finalApprovalList'));
});
//Won Tenant Index
Breadcrumbs::for('wonList', function ($trail) {
    $trail->parent('home');
    $trail->push('Won Enquiry', route('wonList'));
});
//Won Tenant Index
Breadcrumbs::for('closedList', function ($trail) {
    $trail->parent('home');
    $trail->push('Closed Enquiry', route('closedList'));
});
Breadcrumbs::for('landlordLeadAssign.nextStage', function ($trail,$enquiryId, $stage) {
    $trail->parent('landlordContractLoss');
    $trail->push('Contract info', route('landlordLeadAssign.nextStage',[$enquiryId,$stage]));
});
Breadcrumbs::for('landlordcontractApprovalList', function ($trail,$enquiryId, $stage) {
    $trail->parent('landlordContractLoss');
    $trail->push('Contract info', route('landlordcontractApprovalList',[$enquiryId,$stage]));
});
// Land-lord Contract final info
Breadcrumbs::for('leadAssign.nextStage', function ($trail,$details,$stage,$routes) { 
    $trail->parent('home');
    $trail->push($details->salesEnquiry->workFlowProcess->work_flow_processes_name, route($routes));
    $trail->push('Detail View', route('leadAssign.nextStage',[$details,$stage]));
});
Breadcrumbs::for('stageApprove', function ($trail,$contract,$stage) { 
    $trail->parent('home');
    $trail->push('Preliminary Documentation Approval', route('preliminaryApprovalList'));
    $trail->push('Approval', route('contractCreation',[$contract,$stage]));
});
Breadcrumbs::for('contractCreation', function ($trail,$contract,$stage) { 
    $trail->parent('home');
    $trail->push('Final Documentation', route('finalDocumentationList'));
    $trail->push('Contract', route('contractCreation',[$contract,$stage]));
});

/***************************************************************************************/
// U
// Unassigned Enquiry Landloard Index
Breadcrumbs::for('landloardLeadAssign.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landloard Unassigned Enquiries', route('leadAssign.index'));
});
// Land-lord Won enquiry
Breadcrumbs::for('landlordContractWon', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Won Enquiry', route('contractApprovalWon'));
});
// Land-lord Won info
Breadcrumbs::for('contractApprovalListInfoWon', function ($trail,$id,$stage) {
    $trail->parent('landlordContractWon');
    $trail->push('Contract Info', route('contractApprovalListInfo',[$id,$stage]));
});

// Land-lord Loss enquiry
Breadcrumbs::for('landlordContractLoss', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Loss Enquiry', route('contractApprovalLoss'));
});

// Land-lord Approve List
Breadcrumbs::for('contractApprovalList', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Documentation / Contract Approval', route('contractApprovalList'));
});

// Land-lord Pending Approve List
Breadcrumbs::for('contractApprovalPendingList', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Documentation Pending Approval', route('landlordPendingApproval'));
});
// Land-lord Enquiry stage
Breadcrumbs::for('landlordLeadAssign', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Enquiry', route('landlordLeadAssign.index'));
});
// Land-lord contract list
Breadcrumbs::for('landlordContract', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Final Documentation ', route('landlordContract.index'));
});
// Land-lord Contract final info
Breadcrumbs::for('contractApprovalListInfo', function ($trail,$id,$stage) {
    $trail->parent('contractApprovalList');
    $trail->push('Contract Info', route('contractApprovalListInfo',[$id,$stage]));
}); 

// Land-lord Contract final info
Breadcrumbs::for('contractApprovalListPendingInfo', function ($trail,$id,$stage) {
    $trail->parent('contractApprovalPendingList');
    $trail->push('Contract Info', route('contractApprovalListInfo',[$id,$stage]));
}); 
// Land-lord Contract info
Breadcrumbs::for('landlordContract.show', function ($trail,$id) {
    $trail->parent('landlordContract');
    $trail->push('Contract Detail', route('landlordContract.show',$id));
}); 
// Land-lord Add Contract info
Breadcrumbs::for('landlordContract.create', function ($trail) {
    $trail->parent('landlordContract');
    $trail->push('Create Contract Details', route('landlordContract.create'));
}); 
// Land-lord Edit Contract info
Breadcrumbs::for('landlordContract.edit', function ($trail,$id) {
    $trail->parent('landlordContract');
    $trail->push('Edit Contract Details', route('landlordContract.edit',$id));
}); 

// Enquiy Index
Breadcrumbs::for('landlordLead.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Landlord Enquiry ', route('landlordLead.index'));
});
//Landlord Enquiry View in Landlord Final Documentation    
Breadcrumbs::for('enquiryViewInLandlord', function ($trail) {
    $trail->parent('landlordContract');
    $trail->push('View Enquiry Details');
});
