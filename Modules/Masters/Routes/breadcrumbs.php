<?php
// Building Type Index
Breadcrumbs::for('buildingType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Building Type ', route('buildingType.index'));
});

//Building Type Edit
Breadcrumbs::for('buildingType.edit', function ($trail, $buildingType,$current,$currentId){
    $trail->parent($current,$currentId);
    $trail->push('Edit Building Type', route('buildingType.edit', $buildingType->id));
});

//Building Type Create
Breadcrumbs::for('buildingType.create', function ($trail) {
    $trail->parent('buildingType.index');
    $trail->push('Create Building Type', route('buildingType.create'));
});

// View
Breadcrumbs::for('buildingType.show', function ($trail, $buildingType) {
    $trail->parent('buildingType.index');
    $trail->push('View Building Type', route('buildingType.show',$buildingType));
});
/*    **************************************************************************     */

//  Index
Breadcrumbs::for('amentityType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Amenity Type ', route('amentityType.index'));
});

// Edit
Breadcrumbs::for('amentityType.edit', function ($trail, $amentityType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Amenity Type', route('amentityType.edit', $amentityType->id));
});

// Create
Breadcrumbs::for('amentityType.create', function ($trail) {
    $trail->parent('amentityType.index');
    $trail->push('Create Amenity Type', route('amentityType.create'));
});


// View
Breadcrumbs::for('amentityType.show', function ($trail,$amentityType ) {
    $trail->parent('amentityType.index');
    $trail->push('View Amenity Type', route('buildingType.show',$amentityType));
});

/*    **************************************************************************     */
//  Index
Breadcrumbs::for('jobCategory.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Job Category ', route('jobCategory.index'));
});

// Edit
Breadcrumbs::for('jobCategory.edit', function ($trail, $jobCategory,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Job Category', route('jobCategory.edit', $jobCategory->id));
});

// Create
Breadcrumbs::for('jobCategory.create', function ($trail) {
    $trail->parent('jobCategory.index');
    $trail->push('Create Job Category', route('jobCategory.create'));
});


// View
Breadcrumbs::for('jobCategory.show', function ($trail,$jobCategory ) {
    $trail->parent('jobCategory.index');
    $trail->push('View Job Category', route('jobCategory.show',$jobCategory));
});

/*    **************************************************************************     */
//  Index
Breadcrumbs::for('bank.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Bank ', route('bank.index'));
});

// Edit
Breadcrumbs::for('bank.edit', function ($trail, $bank,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Bank', route('bank.edit', $bank->id));
});

// Create
Breadcrumbs::for('bank.create', function ($trail) {
    $trail->parent('bank.index');
    $trail->push('Create Bank', route('bank.create'));
});


// View
Breadcrumbs::for('bank.show', function ($trail,$bank) {
    $trail->parent('bank.index');
    $trail->push('View bank', route('bank.show', $bank));
});
/*    **************************************************************************     */

//  Index
Breadcrumbs::for('complaintReason.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Complaint Reason ', route('complaintReason.index'));
});

// Edit
Breadcrumbs::for('complaintReason.edit', function ($trail, $complaintReason,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Complaint Reason', route('complaintReason.edit', $complaintReason->id));
});

// Create
Breadcrumbs::for('complaintReason.create', function ($trail) {
    $trail->parent('complaintReason.index');
    $trail->push('Create Complaint Reason', route('complaintReason.create'));
});


// View
Breadcrumbs::for('complaintReason.show', function ($trail, $complaintReason) {
    $trail->parent('complaintReason.index');
    $trail->push('View Complaint Reason', route('complaintReason.edit', $complaintReason));
});
/*    **************************************************************************     */

//  Index
Breadcrumbs::for('workLink.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Contractor Work Link ', route('workLink.index'));
});

// Edit
Breadcrumbs::for('workLink.edit', function ($trail, $workLink,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Contractor Work Links', route('workLink.edit', $workLink->id));
});

// Create
Breadcrumbs::for('workLink.create', function ($trail) {
    $trail->parent('workLink.index');
    $trail->push('Create Contractor Work Link', route('workLink.create'));
});


// View
Breadcrumbs::for('workLink.show', function ($trail, $workLink) {
    $trail->parent('workLink.index');
    $trail->push('View Contractor Work Link', route('workLink.edit', $workLink));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('country.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Country', route('country.index'));
});

// Edit
Breadcrumbs::for('country.edit', function ($trail, $country,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Country', route('country.edit', $country->id));
});

// Create
Breadcrumbs::for('country.create', function ($trail) {
    $trail->parent('country.index');
    $trail->push('Create Country', route('country.create'));
});


// View
Breadcrumbs::for('country.show', function ($trail, $country) {
    $trail->parent('country.index');
    $trail->push('View Country', route('country.edit', $country));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('currency.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Currency', route('currency.index'));
});

// Edit
Breadcrumbs::for('currency.edit', function ($trail, $currency, $current,$currentId) {
   $trail->parent($current,$currentId);
    $trail->push('Edit Currency', route('currency.edit', $currency->id));
});

// Create
Breadcrumbs::for('currency.create', function ($trail) {
    $trail->parent('currency.index');
    $trail->push('Create Currency', route('currency.create'));
});


// View
Breadcrumbs::for('currency.show', function ($trail, $currency) {
    $trail->parent('currency.index');
    $trail->push('View Currency', route('currency.edit', $currency));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('enquirySource.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Enquiry Source', route('enquirySource.index'));
});

// Edit
Breadcrumbs::for('enquirySource.edit', function ($trail, $enquirySource,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Enquiry Source', route('enquirySource.edit', $enquirySource->id));
});

// Create
Breadcrumbs::for('enquirySource.create', function ($trail) {
    $trail->parent('enquirySource.index');
    $trail->push('Create Enquiry Source', route('enquirySource.create'));
});


// View
Breadcrumbs::for('enquirySource.show', function ($trail, $enquirySource) {
    $trail->parent('enquirySource.index');
    $trail->push('View Enquiry Source', route('enquirySource.show', $enquirySource));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('homeUtility.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Home Amenities', route('homeUtility.index'));
});

// Edit
Breadcrumbs::for('homeUtility.edit', function ($trail, $homeUtility,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Home Amenities', route('homeUtility.edit', $homeUtility->id));
});

// Create
Breadcrumbs::for('homeUtility.create', function ($trail) {
    $trail->parent('homeUtility.index');
    $trail->push('Create Home Amenities', route('homeUtility.create'));
});


// View
Breadcrumbs::for('homeUtility.show', function ($trail,$homeUtility) {
    $trail->parent('homeUtility.index');
    $trail->push('View Home Amenities', route('homeUtility.show', $homeUtility));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('inventory.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Inventory', route('inventory.index'));
});

// Edit
Breadcrumbs::for('inventory.edit', function ($trail, $inventory, $current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Inventory', route('inventory.edit', $inventory->id));
});

// Create
Breadcrumbs::for('inventory.create', function ($trail) {
    $trail->parent('inventory.index');
    $trail->push('Create Inventory', route('inventory.create'));
});


// View
Breadcrumbs::for('inventory.show', function ($trail,$inventory) {
    $trail->parent('inventory.index');
    $trail->push('View Inventory', route('inventory.show', $inventory));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('invoiceType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Invoice Type', route('invoiceType.index'));
});

// Edit
Breadcrumbs::for('invoiceType.edit', function ($trail, $invoiceType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Invoice Type', route('invoiceType.edit', $invoiceType->id));
});

// Create
Breadcrumbs::for('invoiceType.create', function ($trail) {
    $trail->parent('invoiceType.index');
    $trail->push('Create Invoice Type', route('invoiceType.create'));
});


// View
Breadcrumbs::for('invoiceType.show', function ($trail, $invoiceType) {
    $trail->parent('invoiceType.index');
    $trail->push('View Invoice Type' ,route('building-insurance.show', $invoiceType));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('location.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Location', route('location.index'));
});

// Edit
Breadcrumbs::for('location.edit', function ($trail, $location,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Location', route('location.edit', $location->id));
});

// Create
Breadcrumbs::for('location.create', function ($trail) {
    $trail->parent('location.index');
    $trail->push('Create Location', route('location.create'));
});


// View
Breadcrumbs::for('location.show', function ($trail, $location) {
    $trail->parent('location.index');
    $trail->push('View Location', route('location.show', $location));
});

/*    **************************************************************************     */



/*    **************************************************************************     */

//  Index
Breadcrumbs::for('region.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Region', route('region.index'));
});

// Edit
Breadcrumbs::for('region.edit', function ($trail, $location,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Region', route('region.edit', $location->id));
});

// Create
Breadcrumbs::for('region.create', function ($trail) {
    $trail->parent('region.index');
    $trail->push('Create Region', route('region.create'));
});


// View
Breadcrumbs::for('region.show', function ($trail, $location) {
    $trail->parent('region.index');
    $trail->push('View Region', route('region.show', $location));
});

/*    **************************************************************************     */


//  Index
Breadcrumbs::for('managementType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Management Type', route('managementType.index'));
});

// Edit
Breadcrumbs::for('managementType.edit', function ($trail, $managementType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Management Type', route('managementType.edit', $managementType->id));
});

// Create
Breadcrumbs::for('managementType.create', function ($trail) {
    $trail->parent('managementType.index');
    $trail->push('Create Management Type', route('managementType.create'));
});


// View
Breadcrumbs::for('managementType.show', function ($trail, $managementType) {
    $trail->parent('managementType.index');
    $trail->push('View Management Type',route('managementType.show', $managementType));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('paymentMethod.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Payment Method', route('paymentMethod.index'));
});

// Edit
Breadcrumbs::for('paymentMethod.edit', function ($trail, $paymentMethod,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Payment Method', route('paymentMethod.edit', $paymentMethod->id));
});

// Create
Breadcrumbs::for('paymentMethod.create', function ($trail) {
    $trail->parent('paymentMethod.index');
    $trail->push('Create Payment Method', route('paymentMethod.create'));
});


// View
Breadcrumbs::for('paymentMethod.show', function ($trail, $paymentMethod) {
    $trail->parent('paymentMethod.index');
    $trail->push('View Payment Method', route('paymentMethod.show', $paymentMethod));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('priceRange.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Price Range', route('priceRange.index'));
});

// Edit
Breadcrumbs::for('priceRange.edit', function ($trail, $priceRange,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Price Range', route('priceRange.edit', $priceRange->id));
});

// Create
Breadcrumbs::for('priceRange.create', function ($trail) {
    $trail->parent('priceRange.index');
    $trail->push('Create Price Range', route('priceRange.create'));
});


// View
Breadcrumbs::for('priceRange.show', function ($trail, $priceRange) {
    $trail->parent('priceRange.index');
    $trail->push('View Price Range', route('priceRange.show', $priceRange));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('reason.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Reason', route('reason.index'));
});

// Edit
Breadcrumbs::for('reason.edit', function ($trail, $reason,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Reason', route('reason.edit', $reason->id));
});

// Create
Breadcrumbs::for('reason.create', function ($trail) {
    $trail->parent('reason.index');
    $trail->push('Create Reason', route('reason.create'));
});


// View
Breadcrumbs::for('reason.show', function ($trail,$reason) {
    $trail->parent('reason.index');
    $trail->push('View Reason',route('reason.show', $reason));
});

/*    **************************************************************************     */
//  Index
Breadcrumbs::for('designation.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Designation', route('designation.index'));
});

// Edit
Breadcrumbs::for('designation.edit', function ($trail, $tenantStatus,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Designation', route('designation.edit', $tenantStatus->id));
});

// Create
Breadcrumbs::for('designation.create', function ($trail) {
    $trail->parent('designation.index');
    $trail->push('Create Designation', route('designation.create'));
});


// View
Breadcrumbs::for('designation.show', function ($trail, $designation) {
    $trail->parent('designation.index');
    $trail->push('View Designation', route('designation.show', $designation));
});

/*    **************************************************************************     */
//  Index
Breadcrumbs::for('tenantStatus.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Status', route('tenantStatus.index'));
});

// Edit
Breadcrumbs::for('tenantStatus.edit', function ($trail, $tenantStatus,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Tenant Status', route('tenantStatus.edit', $tenantStatus->id));
});

// Create
Breadcrumbs::for('tenantStatus.create', function ($trail) {
    $trail->parent('tenantStatus.index');
    $trail->push('Create Tenant Status', route('tenantStatus.create'));
});


// View
Breadcrumbs::for('tenantStatus.show', function ($trail, $tenantStatus) {
    $trail->parent('tenantStatus.index');
    $trail->push('View Tenant Status', route('building-insurance.show', $tenantStatus));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('tenantType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Type', route('tenantType.index'));
});

// Edit
Breadcrumbs::for('tenantType.edit', function ($trail, $tenantType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Tenant Type', route('tenantType.edit', $tenantType->id));
});

// Create
Breadcrumbs::for('tenantType.create', function ($trail) {
    $trail->parent('tenantType.index');
    $trail->push('Create Tenant Type', route('tenantType.create'));
});


// View
Breadcrumbs::for('tenantType.show', function ($trail,$tenantType) {
    $trail->parent('tenantType.index');
    $trail->push('View Tenant Type', route('tenantType.show', $tenantType));
});


/*    **************************************************************************     */

//  Index
Breadcrumbs::for('unitType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Unit Type', route('unitType.index'));
});

// Edit
Breadcrumbs::for('unitType.edit', function ($trail, $unitType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Unit Type', route('unitType.edit', $unitType->id));
});

// Create
Breadcrumbs::for('unitType.create', function ($trail) {
    $trail->parent('unitType.index');
    $trail->push('Create Unit Type', route('unitType.create'));
});


// View
Breadcrumbs::for('unitType.show', function ($trail,$unitType) {
    $trail->parent('unitType.index');
    $trail->push('View Unit Type', route('unitType.show', $unitType));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('vendorType.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Vendor Type', route('vendorType.index'));
});

// Edit
Breadcrumbs::for('vendorType.edit', function ($trail, $vendorType,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Vendor Type', route('vendorType.edit', $vendorType->id));
});

// Create
Breadcrumbs::for('vendorType.create', function ($trail) {
    $trail->parent('vendorType.index');
    $trail->push('Create Vendor Type', route('vendorType.create'));
});


// View
Breadcrumbs::for('vendorType.show', function ($trail, $vendorType) {
    $trail->parent('vendorType.index');
    $trail->push('View Vendor Type',route('vendorType.show', $vendorType));
});

/*    **************************************************************************     */

//  Index
Breadcrumbs::for('work.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Work', route('work.index'));
});

// Edit
Breadcrumbs::for('work.edit', function ($trail, $work,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Work', route('work.edit', $work->id));
});

// Create
Breadcrumbs::for('work.create', function ($trail) {
    $trail->parent('work.index');
    $trail->push('Create Work', route('work.create'));
});


// View
Breadcrumbs::for('work.show', function ($trail, $work) {
    $trail->parent('work.index');
    $trail->push('View Work', route('work.show', $work));
});

//  Index
Breadcrumbs::for('subWork.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Sub Work', route('subWork.index'));
});

// Edit
Breadcrumbs::for('subWork.edit', function ($trail, $subWork,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Sub Work', route('subWork.edit', $subWork->id));
});

// Create
Breadcrumbs::for('subWork.create', function ($trail) {
    $trail->parent('subWork.index');
    $trail->push('Create Sub Work', route('subWork.create'));
});


// View
Breadcrumbs::for('subWork.show', function ($trail, $subWork) {
    $trail->parent('subWork.index');
    $trail->push('View Sub Work', route('subWork.show', $subWork));
});


// Vendor Index
Breadcrumbs::for('vendors.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Vendor', route('vendors.index'));
});

//Vendor Edit

Breadcrumbs::for('vendors.edit', function ($trail, $vendors,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Vendor', route('vendors.edit', $vendors->id));
});

//Vendor Create
Breadcrumbs::for('vendors.create', function ($trail) {
    $trail->parent('vendors.index');
    $trail->push('Create Vendor', route('vendors.create'));
});

// View

Breadcrumbs::for('vendors.show', function ($trail, $vendor) {
    $trail->parent('vendors.index');
    $trail->push('View Vendor', route('vendors.show', $vendor));
});



// Building Index
Breadcrumbs::for('building.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Building ', route('building.index'));
});

//Building Edit
Breadcrumbs::for('building.edit', function ($trail, $building,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Building', route('building.edit', $building->id));
});

//Building Create
Breadcrumbs::for('building.create', function ($trail) {
    $trail->parent('building.index');
    $trail->push('Create Building', route('building.create'));
});

// View
Breadcrumbs::for('building.show', function ($trail,$building) {
    $trail->parent('building.index');
    $trail->push('View Building', route('building.show',$building));
});




// Unit Index
Breadcrumbs::for('unit.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Unit ', route('unit.index'));
});

//Unit Edit
Breadcrumbs::for('unit.edit', function ($trail, $unit,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Unit', route('unit.edit', $unit->id));
});

//Unit Create
Breadcrumbs::for('unit.create', function ($trail) {
    $trail->parent('unit.index');
    $trail->push('Create Unit', route('unit.create'));
});

// View
Breadcrumbs::for('unit.show', function ($trail, $unit) {
    $trail->parent('unit.index');
    $trail->push('View Unit', route('unit.show',$unit));
});




// Unit Utility Index
Breadcrumbs::for('unit-utility.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Unit Utility ', route('unit-utility.index'));
});

//Unit Utility Edit
Breadcrumbs::for('unit-utility.edit', function ($trail, $unit_utility,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Edit Unit Utility', route('unit-utility.edit', $unit_utility->id));
});

//Unit Utility Create
Breadcrumbs::for('unit-utility.create', function ($trail) {
	
    $trail->parent('unit-utility.index');
    $trail->push('Create Unit Utility', route('unit-utility.create'));
});

// View
Breadcrumbs::for('unit-utility.show', function ($trail,$unit_utility ) {
    $trail->parent('unit-utility.index');
    $trail->push('View Unit Utility',route('unit-utility.show',$unit_utility));
});

//Building Insurance index

Breadcrumbs::for('building-insurance.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Building Insurance', route('building-insurance.index'));
});

Breadcrumbs::for('building-insurance.create', function ($trail,$current,$currentId) {
    $trail->parent($current,$currentId);
    $trail->push('Create Building Insurance', route('building-insurance.create'));
});

Breadcrumbs::for('building-insurance.edit', function ($trail, $building_amentity,$current,$currentId) {
	$trail->parent($current,$currentId);
    $trail->push('Edit Building Insurance', route('building-insurance.edit', $building_insurance->id));
});

// View
Breadcrumbs::for('building-insurance.show', function ($trail, $building_insurance) {
    $trail->parent('building-insurance.index');
    $trail->push('View Building Insurance', route('building-insurance.show', $building_insurance));
});


/*    **************************************************************************     */

// Unit Utility Index
Breadcrumbs::for('building-amentity.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Building Amenity ', route('building-amentity.index'));
});

//Unit Utility Edit
Breadcrumbs::for('building-amentity.edit', function ($trail, $building_amentity,$current,$currentId) {
	$trail->parent($current,$currentId);
    $trail->push('Edit Building Amenity', route('building-amentity.edit', $building_amentity->id));
});

//Unit Utility Create
Breadcrumbs::for('building-amentity.create', function ($trail,$current,$currentId) {
	$trail->parent($current,$currentId);
    $trail->push('Create Building Amenity', route('building-amentity.create'));
});

// View
Breadcrumbs::for('building-amentity.show', function ($trail, $building_amentity) {
    $trail->parent('building-amentity.index');
    $trail->push('View Building Amenity', route('building-amentity.show', $building_amentity));
});
/*    **************************************************************************     */

//  Index
Breadcrumbs::for('tenants.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant ', route('tenants.index'));
});

// Edit
Breadcrumbs::for('tenants.edit', function ($trail, $tenant) {
    $trail->parent('tenants.index');
    $trail->push('Edit Tenant', route('tenants.edit', $tenant->id));
});
// Create
Breadcrumbs::for('tenants.create', function ($trail) {
    $trail->parent('tenants.index');
    $trail->push('Create Tenant', route('tenants.create'));
});
// View
Breadcrumbs::for('tenants.show', function ($trail) {
    $trail->parent('tenants.index');
    $trail->push('View');
});

/*    **************************************************************************     */
// Are Building Assign  Index
Breadcrumbs::for('areBuildingAssign.index', function ($trail) {
    $trail->parent('home');
    $trail->push('ARE Building Assign', route('areBuildingAssign.index'));
});

Breadcrumbs::for('group_are_list', function ($trail) {
    $trail->parent('home');
    $trail->push('ARE', route('group_are_list'));
});
//Are Building Assign Edit
Breadcrumbs::for('areBuildingAssign.edit', function ($trail, $areBuildingAssign,$current) {
    $trail->parent($current);
    $trail->push('Edit ARE Building Assign', route('areBuildingAssign.edit', $areBuildingAssign->id));
});

//Are Building Assign Create
Breadcrumbs::for('areBuildingAssign.create', function ($trail) {
    $trail->parent('areBuildingAssign.index');
    $trail->push('Create ARE Building Assign ', route('areBuildingAssign.create'));
});

// View Are Building Assign
Breadcrumbs::for('areBuildingAssign.show', function ($trail) {
    $trail->parent('areBuildingAssign.index');
    $trail->push('ARE Building Assign View');
});
Breadcrumbs::for('groupView', function ($trail) {
    $trail->parent('group_are_list');
    $trail->push('ARE');
});

/*    **************************************************************************     */
// Legal  Index
Breadcrumbs::for('legalCase.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Legal Case List', route('legalCase.index'));
});

// View Legal Case
Breadcrumbs::for('legalCase.show', function ($trail) {
    $trail->parent('legalCase.index');
    $trail->push('Legal Case View');
});
//Plms Approval
Breadcrumbs::for('legalPlmsApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Approved Legal Cases for Review', route('plmsApproval'));
});
//Plms Approval View
Breadcrumbs::for('plmsApprovalShow', function ($trail, $plmsApproval) {
    $trail->parent('legalPlmsApproval');
    $trail->push('Legal Cases Approval View', route('plmsApproval',[$plmsApproval->id]));
});

//Lawyer Approval
Breadcrumbs::for('legalLawyerApproval', function ($trail) {
    $trail->parent('home');
    $trail->push('Approved Legal Cases for Review', route('lawyerApproval'));
});
//Lawyer Approval View
Breadcrumbs::for('lawyerApprovalShow', function ($trail, $lawyerApproval) {
    $trail->parent('legalLawyerApproval');
    $trail->push('Approved Legal Cases View', route('lawyerApproval',[$lawyerApproval->id]));
});
//Active Cases
Breadcrumbs::for('legalActiveCases', function ($trail) {
    $trail->parent('home');
    $trail->push('Accepted Legal Cases', route('activeCases'));
});
//Active Cases View
Breadcrumbs::for('activeCasesShow', function ($trail, $activeCases) {
    $trail->parent('legalActiveCases');
    $trail->push('Accepted Legal Cases View', route('activeCases',[$activeCases->id]));
});
//tenant contract view
Breadcrumbs::for('legaltenantContractShow', function ($trail, $tenantContract) {
    $trail->parent('home');
    $trail->push('Tenant Contract View', route('activeCases',[$tenantContract->id]));
});

//closed Legal Cases
Breadcrumbs::for('closedLegalCases', function ($trail) {
    $trail->parent('home');
    $trail->push('Closed Legal Cases', route('closedLegalCases'));
});
//Closed Cases View
Breadcrumbs::for('closedLegalCasesShow', function ($trail, $closedLegalCases) {
    $trail->parent('closedLegalCases');
    $trail->push('Closed Legal Cases View', route('closedLegalCases',[$closedLegalCases->id]));
});
/*    **************************************************************************     */
// Tenancy Details  Index
Breadcrumbs::for('tenancyDetails.index', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenancy', route('tenancyDetails.index'));
});
/*    **************************************************************************     */



/**************Report starts*******************/
Breadcrumbs::for('showBuildingUnitDetails', function ($trail) {
    $trail->parent('home');
    $trail->push('Building Unit Details', route('showBuildingUnitDetails'));
});
Breadcrumbs::for('showBuildingDetails', function ($trail) {
    $trail->parent('home');
    $trail->push('Building Details', route('showBuildingDetails'));
});
Breadcrumbs::for('showFurnishedUnitReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on units Rented as Furnished units', route('showFurnishedUnitReport'));
});

Breadcrumbs::for('showVacancyLossReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on Vacany loss', route('showVacancyLossReport'));
});

Breadcrumbs::for('showVacantUnitReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Report on Vacant Unit', route('showVacantUnitReport'));
});

Breadcrumbs::for('showTenantDetailsReport', function ($trail) {
    $trail->parent('home');
    $trail->push('Tenant Details - Building Wise', route('showTenantDetailsReport'));
});