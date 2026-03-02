<?php

/*
*
*
* Complaint Enquiries
*
*/
// Complaint Enquiries Index
Breadcrumbs::for('complaint.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Complaint', route('complaint.index'));
});

//Complaint Enquiries Edit
Breadcrumbs::for('complaint.edit', function ($trail, $complaintEnquiry,$current) {
    $trail->parent( ($current != null)? $current : 'complaint.index' );
    $trail->push('Edit Complaint', route('complaint.edit', $complaintEnquiry->id));
});

//Complaint Enquiries Create
Breadcrumbs::for('complaint.create', function ($trail) {
    $trail->parent('complaint.index');
    $trail->push('Create Complaint', route('complaint.create'));
});

//Complaint Enquiries View
Breadcrumbs::for('complaint.show', function ($trail) {
    $trail->parent('complaint.index');
    $trail->push('View Complaint');
});
//Unassigned Enquiry Complaint 
Breadcrumbs::for('complaintStage.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Unassigned Complaint', route('complaintStage.index'));
});
//Assigned  Complaint
Breadcrumbs::for('complaintAssignedList', function ($trail) {
    $trail->parent('home');
    $trail->push('Assigned Complaint', route('complaintAssignedList'));
});
// Un assigned view
Breadcrumbs::for('complaintStage.view', function ($trail) { 
    $trail->parent('home');
    $trail->push('Un Assigned View', route('complaintStage.view'));
});
// Un assigned view
Breadcrumbs::for('complaintUnassigned.view', function ($trail,$ComplaintEnquiries) { 
    $trail->parent('complaintStage.index');
    $trail->push('Un Assigned View', route('complaintUnassigned.view',$ComplaintEnquiries->id));
});
// Assigned view
Breadcrumbs::for('complaintAssigned.view', function ($trail,$ticket) { 
    $trail->parent('complaintAssignedList');
    $trail->push('Assigned View', route('complaintAssigned.view',$ticket->id));
});
// Assigned view
Breadcrumbs::for('complaintSubAssigned.view', function ($trail,$complaint,$sub_assign) { 
    $trail->parent('complaintSubAssignedList');
    $trail->push('Sub Assigned View', route('complaintSubAssigned.view',[$complaint->id,$sub_assign]));
});
// Contractor service report
Breadcrumbs::for('contractorServiceReport', function ($trail,$ticket) { 
    $trail->parent('complaintAssignedList');
    $trail->push('Service Report', route('contractorServiceReport',$ticket->id));
});
// Technician service report
Breadcrumbs::for('technicianServiceReport', function ($trail,$ticket,$sub_assign) { 
    $trail->parent('complaintSubAssignedList');
    $trail->push('Service Report', route('technicianServiceReport',[$ticket->id,$sub_assign]));
});
// Technician service report
Breadcrumbs::for('technicianServiceReportClosed', function ($trail,$ticket,$sub_assign) { 
    $trail->parent('complaintClosedList');
    $trail->push('Service Report', route('technicianServiceReportClosed',[$ticket->id,$sub_assign]));
});
// Sub assigned view
Breadcrumbs::for('complaintSubAssignedList', function ($trail) { 
    $trail->parent('home');
    $trail->push('Sub Assigned', route('complaintSubAssignedList'));
});
//Review List Complaint
Breadcrumbs::for('complaintReviewList', function ($trail) {
    $trail->parent('home');
    $trail->push('Review', route('complaintReviewList'));
});
// Review view
Breadcrumbs::for('complaintReviewView', function ($trail,$complaint) { 
    $trail->parent('complaintReviewList');
    $trail->push('Review', route('complaintReview.view',$complaint->id));
});
//Closed List Complaint
Breadcrumbs::for('complaintClosedList', function ($trail) {
    $trail->parent('home');
    $trail->push('Closed Ticket', route('complaintClosedList'));
});
// Closed view
Breadcrumbs::for('complaintClosedView', function ($trail,$complaint) { 
    $trail->parent('complaintClosedList');
    $trail->push('Closed Ticket', route('complaintClosed.view',$complaint->id));
});
//Completely Closed List Complaint
Breadcrumbs::for('complaintCompletelyClosedList', function ($trail) {
    $trail->parent('home');
    $trail->push('Closed Complaint', route('complaintCompletelyClosedList'));
});
// Closed view
Breadcrumbs::for('complaintCompletelyClosedView', function ($trail,$complaint) { 
    $trail->parent('complaintCompletelyClosedList');
    $trail->push('Closed Complaint', route('complaintCompletelyClosed.view',$complaint->id));
});
// Amc Contract Index
Breadcrumbs::for('amcContract.index', function ($trail) {
    $trail->parent('home');
    $trail->push('AMC Contract', route('amcContract.index'));
});

//Amc Contract Edit
Breadcrumbs::for('amcContract.edit', function ($trail, $amcContract,$current) {
    $trail->parent($current);
    $trail->push('Edit AMC Contract', route('amcContract.edit', $amcContract->id));
});

//..................Reshma..................................................................

//Amc Contract Create
Breadcrumbs::for('amcContract.create', function ($trail) {
    $trail->parent('amcContract.index');
    $trail->push('AMC Contract Add', route('amcContract.create'));
});

//Amc Contract View
Breadcrumbs::for('amcContract.show', function ($trail) {
    $trail->parent('amcContract.index');
    $trail->push('AMC Contract View');
});

// Amc Schedule Index
Breadcrumbs::for('amcSchedule.index', function ($trail) {
    $trail->parent('home');
    $trail->push('AMC Schedule', route('amcSchedule.index'));
});

//Amc Schedule Edit
Breadcrumbs::for('amcSchedule.edit', function ($trail, $amcSchedule,$current) {
    $trail->parent($current);
    $trail->push('Edit AMC Schedule', route('amcSchedule.edit', $amcSchedule->id));
});

//Amc Schedule Create
Breadcrumbs::for('amcSchedule.create', function ($trail) {
    $trail->parent('amcSchedule.index');
    $trail->push('AMC Schedule Add', route('amcSchedule.create'));
});

//Amc Schedule View
Breadcrumbs::for('amcSchedule.show', function ($trail) {
    $trail->parent('amcSchedule.index');
    $trail->push('AMC Schedule View');
});

// Amc Task Index
Breadcrumbs::for('amcTask.index', function ($trail) {
    $trail->parent('home');
    $trail->push('AMC Task', route('amcTask.index'));
});

//Amc Task Edit
Breadcrumbs::for('amcTask.edit', function ($trail, $amcTask,$current) {
    $trail->parent($current);
    $trail->push('Edit AMC Task', route('amcTask.edit', $amcTask->id));
});

//Amc Task Create
Breadcrumbs::for('amcTask.create', function ($trail) {
    $trail->parent('amcTask.index');
    $trail->push('AMC Task Add', route('amcTask.create'));
});

//Amc Task View
Breadcrumbs::for('amcTask.show', function ($trail) {
    $trail->parent('amcTask.index');
    $trail->push('AMC Task View');
});



// Maintenance Invoice Index
Breadcrumbs::for('maintenanceInvoice.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Invoice', route('maintenanceInvoice.index'));
});
// Maintenance Generate Invoice
Breadcrumbs::for('groupInvoiceGeneration', function ($trail) {
    $trail->parent('home');
    $trail->push('Generate Maintenance Invoice', route('groupInvoiceGeneration'));
});
//Maintenance Invoice Edit
Breadcrumbs::for('maintenanceInvoice.edit', function ($trail, $maintenanceInvoice) {
    $trail->parent('maintenanceInvoice.index');
    $trail->push('Edit Maintenance Invoice', route('maintenanceInvoice.edit', $maintenanceInvoice->id));
});

//Maintenance Invoice Create
Breadcrumbs::for('maintenanceInvoice.create', function ($trail) {
    $trail->parent('maintenanceInvoice.index');
    $trail->push('Add Maintenance Invoice', route('maintenanceInvoice.create'));
});

//Maintenance Invoice View
Breadcrumbs::for('maintenanceInvoice.show', function ($trail) {
    $trail->parent('maintenanceInvoice.index');
    $trail->push('View Maintenance Invoice');
});

//Maintenance Invoice Approval 
Breadcrumbs::for('maintenanceInvoiceApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance Invoice Approval', route('maintenanceInvoiceApproval'));
});


/*
*
*Report Starts
*
*/
Breadcrumbs::for('showMaintenanceReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance  Report', route('showMaintenanceReport'));
});
Breadcrumbs::for('showComplaintStatusReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Complaint status percentage wise', route('showComplaintStatusReport'));
});

Breadcrumbs::for('showComplaintTicketReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Maintenance  Report - (Open Tickets/ Closed Tickets)', route('showComplaintTicketReport'));
});

/*
*
*Report Starts
*
*/
Breadcrumbs::for('showServiceReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Service Report',route('showServiceReport'));
});

