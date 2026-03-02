@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} "><!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">{{(isset($tenantContract))? 'Tenant Contract Revoke' :'Tenant Contract Creation' }}</div>
        </div>
        {{ (isset($tenantContract))?   Breadcrumbs::render('tenant-contract.revoke',$tenantContract) :  Breadcrumbs::render('tenant-contract.create') }} 
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form autocomplete="off" action="{{ !isset($tenantContract)? route('tenant-contract.store'): route('storeTenantRevoke',$tenantContract->id)}}" method="POST" id="tenant_contract_form" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} 
        
        <input  type="hidden" class="form-control" name="sales_enquiry_id" value="{{ !isset($tenantContract)? '': $tenantContract->sale_enquiry_id}}">
        <input type="hidden" name="workflow_id" value="{{$stage}}">
    <div class="dataSearchBox ">

        <div class="row">
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="tenant_contract_no" readonly name="tenant_contract_no" required value="{{ isset($tenantContract)?  old('tenant_contract_no',$tenantContract->tenant_contract_no): old('tenant_contract_no',$contractCode)}}">
                </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement Date <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="simpleFormEmail" placeholder="Agreement Date"  name="tenant_contract_date" value="{{ isset($tenantContract)?  old('tenant_contract_date',$tenantContract->created_at->format('Y-m-d')): old('tenant_contract_date',date('Y-m-d'))}}">
                </div>
                </div>
            </div>
            
        </div>

    </div>

<div class="sub-head">Building Details</div>
<div class="dataSearchBox ">

        <div class="row">
             <div class="col-sm-6">
            <div class="form-group">
                <label for="building_id">Building Name<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                    <input type="text" placeholder="Enter Building Name" required name="building_name" class="form-control building_name building" id="building_name" value="{{ isset($tenantContract)?  old('building_name',$tenantContract->building->building_name): old('building_name','')}}" readonly>
				
                    <input type="hidden" name="building_id" id="building_id" value="{{ isset($tenantContract)?  old('building_id',$tenantContract->building_id): old('building_id','')}}">

                <!-- <select class="form-control building" name="building_id" id="building_id" required>
                    <option value="">Select Building</option> 
                    @foreach($buildings as $building)                           
                    <option {{ isset($tenantContract)? ((old('location_id',$tenantContract->building_id) == $building->id)? 'selected' : '') : ''}} value="{{$building->id}}" >{{$building->building_code}}</option>
                    @endforeach                          
                </select> -->
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Tenant Name<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-superpowers icn-add" aria-hidden="true"></i>
                    <input type="text" readonly name="tenants_id" required class="form-control tenants_id" id="tenants_id" value="{{ isset($tenantContract)?  old('tenants_id',$tenantContract->tenant->tenant_name): old('tenants_id','')}}">
                
                <input type="hidden" name="tenant_id" id="tenant_id" value="{{ isset($tenantContract)?  old('tenant_id',$tenantContract->tenant->id): old('tenant_id','')}}">

                <input type="hidden" name="tenant_name" id="tenant_name" value="{{ isset($tenantContract)?  old('tenant_name',$tenantContract->tenant->tenant_name): old('tenant_name','')}}">
            </div>
            </div>
        </div>
         <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Building Name</label>
                <div class="p-relative">
                    <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="building_name" placeholder="Enter Building Name" value="{{ isset($tenantContract)?  old('building_name',$tenantContract->building->building_name): old('building_name','')}}" readonly>
            </div>
            </div>
        </div> -->
           <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                    <input type="text" name="unit_id" class="form-control" id="unit_id" readonly value="{{ isset($tenantContract)?  old('unit_id',$tenantContract->unit->unit_code): old('unit_id','')}}">

                <input type="hidden" name="unit" id="unit" value="{{ isset($tenantContract)?  old('unit',$tenantContract->unit_id): old('unit','')}}">
                
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit Type</label>
                <div class="p-relative">
                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                <input type="text" name="unitType" class="form-control" id="unitType" readonly value="{{ isset($tenantContract)?  old('unitType',$tenantContract->unit->unit->unit_types_name): old('unitType','')}}">
            </div>
            </div>
        </div>
         <div class="w-100"></div>
         <div class="col-sm-6">
            <div class="form-group">
                <label>Unit Usage<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
                <input type="text" name="unit_usage" class="form-control" id="unit_usage" readonly value="{{ isset($tenantContract)?  old('unit_usage',$tenantContract->unit_usage): old('unit_usage','')}}">
                
            </div>
            </div> 
        </div>
        <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Tenant Code</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input disabled="" type="text" class="form-control" id="tenant_code" placeholder="Enter Tenant Code" value="{{ isset($tenantContract)?  old('tenant_code',$tenantContract->tenant->tenant_code): old('tenant_code','')}}">
            </div>
            </div>
        </div>
        
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_address">Tenant Contact Address</label>
                <div class="p-relative">
                    <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea name="tenant_contact_address" id="tenant_contact_address" class="form-control" placeholder="Enter Contract Address"  >{{ isset($tenantContract)?  old('tenant_contact_address',$tenantContract->tenant->tenant_contact_address): old('tenant_contact_address','')}}</textarea>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_secondary_address"> Tenant Secondary Address</label>
                <div class="p-relative">
                    <i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
                <textarea name="tenant_secondary_address" id="tenant_secondary_address" class="form-control" placeholder="Enter Contract Address"  >{{ isset($tenantContract)?  old('tenant_secondary_address',$tenantContract->tenant->tenant_secondary_address): old('tenant_secondary_address','')}}</textarea>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_no">Tenant Contact No</label>
                <div class="p-relative">
                    <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contact_no" placeholder="Enter Contact No" value="{{ isset($tenantContract)?  old('tenant_contact_no',$tenantContract->tenant->tenant_contact_no): old('tenant_contact_no','')}}" name="tenant_contact_no" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values">
            </div>
            </div>
        </div>
        
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_person">Tenant Contact Person</label>
                <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contact_person" placeholder="Enter Contact Person" value="{{ isset($tenantContract)?  old('tenant_contact_person',$tenantContract->tenant->tenant_contact_person): old('tenant_contact_person','')}}" name="tenant_contact_person">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contact_email">Tenant Contact Email<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
                <input type="email" required class="form-control" id="tenant_contact_email" placeholder="Enter Contact Email" value="{{ isset($tenantContract)?  old('tenant_contact_email',$tenantContract->tenant->tenant_contact_email): old('tenant_contact_email','')}}" name="tenant_contact_email">
            </div>
            </div>
        </div>
        
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_fax_no">Tenant Fax No</label>
                <div class="p-relative">
                    <i class="fa fa-fax icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_fax_no" placeholder="Enter Fax No" value="{{ isset($tenantContract)?  old('tenant_fax_no',$tenantContract->tenant->tenant_fax_no): old('tenant_fax_no','')}}" name="tenant_fax_no">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_acc_no">Tenant Account No</label>
                <div class="p-relative">
                    <i class="fa fa-credit-card icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_acc_no" placeholder="Enter Account No" value="{{ isset($tenantContract)?  old('tenant_acc_no',$tenantContract->tenant->tenant_acc_no): old('tenant_acc_no','')}}" name="tenant_acc_no" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_pc">Tenant Postal Code</label>
                <div class="p-relative">
                    <i class="fa fa-id-card-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_pc" placeholder="Enter Postal Code" value="{{ isset($tenantContract)?  old('tenant_pc',$tenantContract->tenant->tenant_pc): old('tenant_pc','')}}" name="tenant_pc" data-rule-pattern="\d+(\.\d{2})?" data-msg-pattern="Allowed only Numeric Values">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bank_id">Tenant Bank Name</label>
                <div class="p-relative">
                    <i class="fa fa-university icn-add" aria-hidden="true"></i>
                <select class="form-control" name="bank_id" >
                    <option value="">Select Bank Name</option>                            
                    @foreach($banks as $bank)                           
                    <option {{ isset($tenantContract)? ((old('location_id',$tenantContract->tenant->bank_id) == $bank->id)? 'selected' : '') : ''}} value="{{$bank->id}}" >{{$bank->bank_name}}</option>
                    @endforeach
                
                </select>
            </div>
            </div>
        </div>
        
        <div class="col-sm-6">
            <div class="form-group">
                <label for="location_id">Tenant Location</label>
                <div class="p-relative">
                    <i class="fa fa-map-marker icn-add" aria-hidden="true"></i>
                <select class="form-control" name="location_id">
                    <option value="">Select Location</option> 
                    @foreach($locations as $location)                           
                    <option {{ isset($tenantContract)? ((old('location_id',$tenantContract->tenant->location_id) == $location->id)? 'selected' : '') : ''}} value="{{$location->id}}" >{{$location->locations_name}}</option>
                    @endforeach                          
                </select>
            </div>
            </div>
        </div>        
        <div class="w-100"></div>
         <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_company_name">Company Name</label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="phone" class="form-control" id="tenant_company_name" placeholder="Enter Company Name" name="tenant_company_name" value="{{ isset($tenantContract)?  old('tenant_company_name',$tenantContract->tenant->tenant_company_name): old('tenant_company_name','')}}">
                </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Nationality</label>
                    <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <select class="form-control" name="nationality" id="nationality">
                        <option value="">Select Nationality</option> 
                        @foreach($nationalities as $nationality)
                        <option {{ isset($tenantContract)? ((old('nationality',$tenantContract->tenant->nationalities_id) == $nationality->nationalityid)? 'selected' : '') : ''}} value="{{$nationality->nationalityid}}" >{{$nationality->nationality}}</option>
                        @endforeach
                    </select>
                    </div>
                </div> 
            </div> -->
        <div class="w-100"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Name</label>
                <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <!-- <select class="form-control Occupant" name="occupant_id" id="occupant_id">
                    <option value="">Select Occupant</option> 
                    @foreach($occupants as $occupant)
                    <option {{ isset($tenantContract)? ((old('nationality',$tenantContract->tenant->nationalities_id) == $occupant->id)? 'selected' : '') : ''}} value="{{$occupant->id}}" >{{$occupant->occupant_name}}</option>
                    @endforeach
                </select> -->
                <input type="text" class="form-control" id="occupant_name" id="occupant_name" placeholder="Enter Occupant Name" name="occupant_name" value="{{ isset($tenantContract)?old('occupant_name',((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_name:$tenantContract->occupant->occupant_name)):old('occupant_name','')}}"> 

                <input type="hidden" class="form-control" id="occupant_id" id="occupant_id" placeholder="Enter Occupant id" name="occupant_id" value="{{ isset($tenantContract->occupant_id)?  old('occupant_id',$tenantContract->occupant->id): old('occupant_id','')}}">
            </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Mob No</label>
                <div class="p-relative">
                    <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="phone" class="form-control" id="occupant_primary_contact_no" placeholder="Enter Occupant Mob No" name="occupant_primary_contact_no" value="{{(isset($tenantContract))?((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_contact_no:$tenantContract->occupant->occupant_primary_contact_no):old('occupant_primary_contact_no','')}}">
            </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Email ID</label>
                <div class="p-relative">
                    <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
                <input type="email" class="form-control" id="occupant_email" placeholder="Enter Occupant Email Id" name="occupant_email" value="{{(isset($tenantContract->occupant_id))?old('occupant_email',((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_contact_email:$tenantContract->occupant->occupant_email)):old('occupant_email','')}}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
           
        </div>

</div>

<div class="clearfix"></div>
<div class="sub-head">Contract Details</div>
<div class="dataSearchBox">
    <div class="row">
    	
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_start_date">Start Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_start_date" placeholder="Enter Start Date" name="tenant_contract_start_date" value="{{old('tenant_contract_start_date',isset($tenantContract->tenant_contract_start_date)? $tenantContract->tenant_contract_start_date->format('Y-m-d') : '')}}" required readonly>
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_effective_date">Effective Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_effective_date" placeholder="Enter Effective Date" name="tenant_contract_effective_date" value="{{old('tenant_contract_effective_date',isset($tenantContract->tenant_contract_effective_date)? $tenantContract->tenant_contract_effective_date->format('Y-m-d'): '')}}" required readonly>
            </div>
            </div>
        </div> 
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_valid_to_date" placeholder="Enter Valid To" name="tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date',isset($tenantContract->tenant_contract_valid_to_date)? $tenantContract->tenant_contract_valid_to_date->format('Y-m-d') : '')}}" required readonly>
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_rent">Rent (P.M)<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
               <!-- <input type="text" class="form-control rent allownumericwithdecimal" id="tenant_contract_rent" placeholder="Enter Rent (P.M)" name="tenant_contract_rent" value="{{ isset($tenantContract)?  old('tenant_contract_rent',numberFormat($tenantContract->tenant_contract_rent)): old('tenant_contract_rent','')}}" readonly> -->
                <input type="text" class="form-control rent allownumericwithdecimal" id="tenant_contract_rent" placeholder="Enter Rent (P.M)" name="tenant_contract_rent" value="{{ isset($tenantContract->tenant_contract_rent)?  old('tenant_contract_rent',numberFormat($tenantContract->tenant_contract_rent)): old('tenant_contract_rent','')}}" readonly>

			</div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_value">Contract Value</label>
                 <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
               <!-- <input type="text" readonly class="form-control allownumericwithdecimal" id="tenant_contract_value" placeholder="Contract Value" name="tenant_contract_value" value="{{ isset($tenantContract)?  old('tenant_contract_value',numberFormat($tenantContract->tenant_contract_rent * $tenantContract->tenant_contract_duration)): old('tenant_contract_value')}}">-->
                <input type="text" readonly class="form-control allownumericwithdecimal" id="tenant_contract_value" placeholder="Contract Value" name="tenant_contract_value" value="{{ isset($tenantContract->tenant_contract_value)?  old('tenant_contract_value',numberFormat($tenantContract->tenant_contract_rent * $tenantContract->tenant_contract_duration)): old('tenant_contract_value')}}">

			</div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label> Duration (Year - Month - Day)</label>
                <div class="p-relative">   
                @php 
                if(isset($tenantContract->tenant_contract_duration_countdown)){
                    $duration = $tenantContract->tenant_contract_duration_countdown;
                    $count = explode('-',$duration); 
                }

                @endphp

                <input type="textbox" readonly="readonly" class="form-control box-width-txt" id="yeartxt" name="yeartxt" value="{{ isset($count['0'])?  old('yeartxt',$count['0']): old('yeartxt')}}" placeholder="Year">

               <input type="textbox" readonly="readonly" class="form-control box-width-txt" id="monthtxt" name="monthtxt" value="{{ isset($count['1'])?  old('monthtxt',$count['1']): old('monthtxt')}}" placeholder="Month">
                <input type="textbox" readonly="readonly" class="form-control box-width-txt" id="daytxt" name="daytxt" value="{{ isset($count['2'])?  old('monthtxt',$count['2']): old('daytxt')}}" placeholder="Month"" placeholder="Day"> 

            </div>
            </div>
        </div>      
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_electric_water">Deposit Electric/Water</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormEmail" placeholder="Enter Electric/Water Deposit" name="tenant_contract_electric_water" value="{{ isset($tenantContract)?  old('tenant_contract_electric_water',$tenantContract->tenant_contract_electric_water): old('tenant_contract_electric_water',0.00)}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_vacant_since">Vacant Since</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_vacant_since" readonly placeholder="Enter Vacant Since" name="tenant_contract_vacant_since" value="{{old('tenant_contract_vacant_since',isset($tenantContract->tenant_contract_vacant_since)? $tenantContract->tenant_contract_vacant_since->format('Y-m-d') : '')}}" >
            </div>
            </div>
        </div>
       <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_rent_paid_prev_tenant">Rent Paid by Previous Tenant</label>
                <div class="p-relative">
                    <i class="fa  fa-money icn-add" aria-hidden="true"></i>
                <input type="text" readonly class="form-control" id="tenant_contract_rent_paid_prev_tenant" placeholder="Enter Rent Paid by Previous Tenant" name="tenant_contract_rent_paid_prev_tenant" value="{{ isset($tenantContract)?  old('tenant_contract_rent_paid_prev_tenant',$tenantContract->tenant_contract_rent_paid_prev_tenant): old('tenant_contract_rent_paid_prev_tenant')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_muncipality_agr_no">Municipality Agr. No<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_muncipality_agr_no" placeholder="Enter Municipality Agr. No" name="tenant_contract_muncipality_agr_no"  value="{{ isset($tenantContract)?  old('tenant_contract_muncipality_agr_no',$tenantContract->tenant_contract_muncipality_agr_no): old('tenant_contract_muncipality_agr_no')}}"required>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_registered_in">Contract Registered In<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="tenant_contract_registered_in" required>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_registered_in',$tenantContract->tenant_contract_registered_in) == '1')? 'selected' : '') : ''}} value="1" >Muscat</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_registered_in',$tenantContract->tenant_contract_registered_in) == '2')? 'selected' : '') : ''}} value="2" >Not in Muscat</option>
                </select>
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_registered_date">Contract Registered Date </label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_registered_date" placeholder="Enter Contract Registered Date" name="tenant_contract_registered_date"  value="{{old('tenant_contract_registered_date',isset($tenantContract->tenant_contract_registered_date)? $tenantContract->tenant_contract_registered_date->format('Y-m-d') : '')}}">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_payment_type">Payment Term<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="tenant_contract_payment_type" required>
                    <option value="">Select Payment Term </option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '1')? 'selected' : '') : ''}} value="1" >Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '2')? 'selected' : '') : ''}} value="2" >Bi-Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '3')? 'selected' : '') : ''}} value="3" >Quarterly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '4')? 'selected' : '') : ''}} value="4" >Half Yearly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '5')? 'selected' : '') : ''}} value="5" >Yearly</option>

                </select>
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_note">Remark</label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_note" placeholder="Enter Remark" name="tenant_contract_note" value="{{ isset($tenantContract)?  old('tenant_contract_note',$tenantContract->tenant_contract_note): old('tenant_contract_note','')}}">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_marketing_executive">Marketing Executive Name <small class="textRed">*</small></label>
                 <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                 <select class="form-control" name="tenant_marketing_executive" required="">
                   
                    @foreach($employeeList as $emp)
                        <option value={{$emp->id}} {{(old('tenant_marketing_executive', isset($tenantContract)?  $tenantContract->tenant_marketing_executive : '0') == $emp->id) ? 'selected' : '' }}>{{$emp->employee_name.'('.$emp->employee_code.')'}}</option>
                    @endforeach
                </select>
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        
        <div class="col-sm-1"> 
            <div class="form-group">
                <label for="pdc_check">PDC </label>
            </div>
        </div>
       <div class="col-sm-5" >
    <label class="radio-inline"><input type="radio" value="1" name="pdc_check" id="pdc1" {{ isset($tenantContract)?(($tenantContract->pdc_check==1)?'checked':''):''}}> Full</label>
    <label class="radio-inline"><input type="radio" value="2" name="pdc_check" id="pdc2" {{ isset($tenantContract)?(($tenantContract->pdc_check==2)?'checked':''):''}} > Partial</label>
    <textarea style="width: 95%;" rows="4" name="partial_comment" id="partial_comment">{{ isset($tenantContract)?  old('partial_comment',$tenantContract->partial_comment): old('partial_comment')}}</textarea>
 </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_note">Deposit</label>
               
                <input type="checkbox" value="1" name="deposit_check" {{ isset($tenantContract)?(($tenantContract->deposit_check==1)?'checked':''):''}}>
            
            </div>
        </div>
		<!--
         <div class="col-sm-6">
            <div class="form-group">

                <div class="form-check-inline">
                    <label class="form-check-label"> Registered in Municipality  &nbsp;</label>
                    <input type="checkbox" class="form-check-input" name="tenant_contract_is_reg_municipality"  value="1" {{isset($tenantContract)? (($tenantContract->tenant_contract_is_reg_municipality)?'checked':''):'' }}>
                </div> 
           </div>
        </div>
		-->
        <!-- <div class="w-100"></div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_status">Contract Status</label>
                 <select class="form-control" name="tenant_contract_status" required>
                    <option>Select Contract Status </option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_status',$tenantContract->tenant_contract_status) == 'Inactive')? 'selected' : '') : ''}} value="0" >Inactive</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_status',$tenantContract->tenant_contract_status) == 'Active')? 'selected' : '') : ''}} value="1" >Active</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_status',$tenantContract->tenant_contract_status) == 'Expired')? 'selected' : '') : ''}} value="2" >Expired</option>
                  </select>
            </div>
        </div> -->

        <div class="w-100"></div>

    </div>
</div>
 <div class="clearfix"></div>
 <div class="sub-head">Payment Details</div>
<div class="dataSearchBox ">
  
        <div class="row">
           <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_deposit_amt">Deposit Rent Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <!-- <input type="text" class="form-control allownumericwithdecimal" id="tenant_contract_deposit_amt" placeholder="Enter Deposit Rent Amount" name="tenant_contract_deposit_amt"  value="{{ isset($tenantContract)?  old('tenant_contract_deposit_amt',numberFormat($tenantContract->tenant_contract_deposit_amt)): old('tenant_contract_deposit_amt','')}}">-->
                <input type="text" class="form-control allownumericwithdecimal" id="tenant_contract_deposit_amt" placeholder="Enter Deposit Rent Amount" name="tenant_contract_deposit_amt"  value="{{ isset($tenantContract->tenant_contract_deposit_amt)?  old('tenant_contract_deposit_amt',numberFormat($tenantContract->tenant_contract_deposit_amt)): old('tenant_contract_deposit_amt','')}}">

			</div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_guarantee_cheque_details">Guarantee Cheque Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <!--<input type="text" class="form-control allownumericwithdecimal" id="tenant_contract_guarantee_cheque_details" placeholder="Enter Guarantee Cheque Amount" name="tenant_contract_guarantee_cheque_details"  value="{{ isset($tenantContract)?  old('tenant_contract_guarantee_cheque_details',numberFormat($tenantContract->tenant_contract_guarantee_cheque_details)): old('tenant_contract_guarantee_cheque_details','')}}">-->
                <input type="text" class="form-control allownumericwithdecimal" id="tenant_contract_guarantee_cheque_details" placeholder="Enter Guarantee Cheque Amount" name="tenant_contract_guarantee_cheque_details"  value="{{ isset($tenantContract->tenant_contract_guarantee_cheque_details)?  old('tenant_contract_guarantee_cheque_details',numberFormat($tenantContract->tenant_contract_guarantee_cheque_details)): old('tenant_contract_guarantee_cheque_details','')}}">

		   </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="tenant_contract_receipt_no">Receipt No</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_receipt_no" placeholder="Enter Receipt No" name="tenant_contract_receipt_no"  value="{{ isset($tenantContract)?  old('tenant_contract_receipt_no',$tenantContract->tenant_contract_receipt_no): old('tenant_contract_receipt_no','')}}">
            </div>
            </div>
        </div>
         <div class="col-sm-4">
            <div class="form-group">
                <label for="tenant_contract_receipt_date">Receipt date</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_receipt_date" placeholder="Enter Amount" name="tenant_contract_receipt_date"  value="{{old('tenant_contract_receipt_date',isset($tenantContract->tenant_contract_receipt_date)? $tenantContract->tenant_contract_receipt_date->format('Y-m-d') : '')}}">
            
			</div>
            </div>
        </div>
         <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Receipt Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <!--<input type="text" class="form-control allownumericwithdecimal" name="tenant_contract_receipt_amt" id="simpleFormEmail" placeholder="Enter Receipt Amount" value="{{old('tenant_contract_receipt_date',numberFormat($tenantContract->tenant_contract_receipt_amt))}}">-->
                <input type="text" class="form-control allownumericwithdecimal" name="tenant_contract_receipt_amt" id="simpleFormEmail" placeholder="Enter Receipt Amount" value="{{ isset($tenantContract->tenant_contract_receipt_amt)?  old('tenant_contract_receipt_amt',numberFormat($tenantContract->tenant_contract_receipt_amt)): old('tenant_contract_receipt_amt','')}}">

		   </div>
            </div>
        </div>
        <div class="w-100"></div>
            
        </div>
    

</div>
<div class="clearfix"></div>
<div class="sub-head">Document Upload (Max : {{$upload_size/1000000}} MB)</div>
<div class="dataSearchBox ">
	    <div class="row">
           <div class="col-sm-6">
            <div class="form-group">
                <div class="control-group input-group increment" id="1">
                  <input type="file" name="tenant_document_file_name[1]" id="tenant_document_file_name[1]" class="form-control upload" style="width: 70%;">
                    <div class="input-group-btn" style="display: none;"> 
                        <button class="btn btn-danger" type="button"><i class="fa fa-trash-o "></i></button>
                    </div>
                    <label id="tenant_document_file_name[1]-error" class="error" for="tenant_document_file_name[1]"></label>
                </div>
                
               
            </div>
            </div>
        	<div class="input-group-btn"> 
                    <button class="btn btn-default btn-reset" type="button"><i class="glyphicon glyphicon-remove"></i>Clear</button>
                   <!-- <button class="btn btn-success" type="button"><i class="glyphicon glyphicon-plus"></i>Add</button> -->
            </div>
            </div>
            
        
        <div class="field_wrapper">
        @if(!empty($tenantContract->tenantDocument)) 

         <!--  <label >If delete Doc, after click SAVE button</label> -->
          @foreach ($tenantContract->tenantDocument  as $doc) 
             <div class="row ro ">
                 <div class="col-sm-6">
                  <div class="form-group">
                     <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}">
                        {{$doc->tenant_documents_name}}  </a>
                  </div>
                </div>
                <div class="col-sm-2">
                      <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                      <input type="hidden" name="doc_path_id" value="{{$doc->id}}"><!-- img-closed -->
                      <button type="button" title="Delete" class="btn btn-warning remove_button "><i class="fa fa-trash-o "></i></button>
                  </div>
              <div class="w-100"></div>
              </div>
          @endforeach    
      
          @endif
        </div>    
        


</div>
<div class="col-sm-12">
            <div class="row">
                <div class="col no-padding">
                    <button type="submit" class="btn btn-primary ">Save</button> 
                   <!--  <button type="submit" class="btn btn-primary "><i class="fa fa-trash fa-fw" aria-hidden="true"></i>Delete</button> 
                    <button type="submit" class="btn btn-primary "><i class="fa fa-print fa-fw" aria-hidden="true"></i>Print</button> 
                    <button type="submit" class="btn btn-primary "><i class="fa fa-share-square fa-fw" aria-hidden="true"></i>Post</button>  -->
                    <!-- <button type="submit" class="btn btn-primary "><i class="fa fa-paperclip fa-fw" aria-hidden="true"></i>Attachment</button> -->
                </div>
            </div>
            <!-- <div class="row">
                <div class="col mt-5 mb-3">
                    <p><a href="#">Generate Invoice</a> | <a href="#">PDC (Post Dated Cheques)</a></p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <button type="submit" class="btn btn-warning">Renew Contract</button>
                    <button type="submit" class="btn btn-warning">Premature Termination of Contract</button>
                    <button type="submit" class="btn btn-warning">Approve Contract</button>
                    <button type="submit" class="btn btn-warning">Normal Termination of Contract</button>
                    <button type="submit" class="btn btn-warning">Reject</button>
                </div>
            </div> -->
        </div>
</form>

<div class="clearfix"></div>

    
</div>
</div>
</div>


@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script>
    
$("#tenant_contract_start_date").change(function(){
    var now = $("#tenant_contract_start_date").val();
    var duration_type = $("#tenant_contract_duration_type").val();
    var duration = parseInt($("#tenant_contract_duration").val());
    $("#tenant_contract_effective_date").val(now);

    var current = new Date(now);
    var endDate = addMonths(current,duration);

    $('#tenant_contract_valid_to_date').val(endDate);
    $('#tenant_contract_valid_to_date').attr('readonly',true);
    
});
function daysInMonth(year, month)
{
return new Date(year, month + 1, 0).getDate();
}

function addMonths(date, months)
{
    var target_month = date.getMonth() + months;
    year = date.getFullYear() + parseInt(target_month / 12);
    month = target_month % 12;
    day = date.getDate() -1;
    last_day = daysInMonth(year, month);
    if (day > last_day)
    {
        day = last_day;
    }
    new_date = new Date(year, month, day);
    var s =new_date.getFullYear() + '-' + ("0" + (new_date.getMonth() + 1)).slice(-2) + '-' + ("0" + new_date.getDate()).slice(-2) ;
    return s;
}
/***************************************************************************************/
$(document).on('change',".rent", function(){
    var tenant_contract_rent = $("#tenant_contract_rent").val();
    if(tenant_contract_rent == "")tenant_contract_rent = 0;
    var duration = $("#tenant_contract_duration").val();
    if(duration == "")duration = 0;
    var tenant_contract_value = parseFloat(tenant_contract_rent) * parseFloat(duration);
    $("#tenant_contract_value").val(tenant_contract_value);
});
/***************************************************************************************/
/****************************partial comment ***************************************/
        $('input[id="pdc2"]').on('change', function() {
         $('#partial_comment').show();
     });
        $('input[id="pdc1"]').on('change', function() {
          $('#partial_comment').hide();
      });

        $(document).ready(function(){
         var pdc1 = $("input[type=radio][id='pdc1']:checked").val();
         var pdc2 = $("input[type=radio][id='pdc2']:checked").val();
         if(pdc1 == undefined && pdc2 != undefined){
            $('#partial_comment').show();
        }else{
           $('#partial_comment').hide();
       }
   });
        /************************************************************************************/
$(document).ready(function() {
    $('#tenants_id').autocomplete({
      source : '{!!URL::route('tenantsAutocomplete')!!}',
      minlenght:1,
      autoFocus:true,
      change:function(e,ui){
         if (ui.item == null || ui.item == undefined) {
            $("#tenants_id").val('');
            $("#tenant_id").val('');
            $('#tenants_id-error').show();
        }else {
            var id = ui.item.ids;
            if(id!= null){
                $.ajax({
                  type: "POST",
                  url: "{{url('/tenants/tenantDetail')}}",
                  data: {"id":id,"_token": "{{ csrf_token() }}"},
                  cache: false,
                  dataType: "json",
                  success: function(data)
                  {
                    $('#tenant_code').val(data.tenant_code);
                    $('#tenant_id').val(data.id);
                    $('#tenant_name').val(data.tenant_name);
                    $('#tenant_company_name').val(data.tenant_company_name);
                    $('#tenant_contact_address').val(data.tenant_contact_address);
                    $('#tenant_contract_address').val(data.tenant_contact_address);
                    $('#tenant_secondary_address').val(data.tenant_secondary_address);
                    $('#tenant_pc').val(data.tenant_pc);
                    $('#tenant_contact_no').val(data.tenant_contact_no);
                    $('#tenant_contact_person').val(data.tenant_contact_person);
                    $('#tenant_contact_email').val(data.tenant_contact_email);
                    $('#tenant_fax_no').val(data.tenant_fax_no);
                    $('#tenant_acc_no').val(data.tenant_acc_no);
                    $('#nationality').val(data.nationalities_id);
                  } 
                });
            }else {
                $('#tenants_id-error').show();
            }
        }
        
      }
    });
    /****************************************************************/
    $('#building_name').autocomplete({
      source : '{!!URL::route('buildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        $('#building_id').val(ui.item.ids);
        /*$('#building_name').val(ui.item.value);*/
        var id =ui.item.ids;
        $.ajax({
              type: "POST",
              url: "{{url('/tenantContract/buildingByUnit')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                if(data['buildUnit'].length > 0){
                  $('#unit_id').empty();
                  $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                    $.each(data['buildUnit'], function(key, value) {
                        $('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
                  });

                  $('#tenant_contract_start_date').attr('min', data['contractValid'][0]);
                  $('#tenant_contract_start_date').attr('max', data['contractValid'][1]);

                  
                }
                else{
                    $('#unit_id').html('<option value="">No Available Units</option>');
                }
              } 
        });
        }
    });
    /**********************************************************/
    $("#tenant_contract_form").validate();

    /*$(document).on('change',".building", function(){
        //alert(55);
        var id = $("#building_id").val();
        $.ajax({
              type: "POST",
              url: "{{url('/tenantContract/buildingByUnit')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                if(data.length > 0){
                  $('#unit_id').empty();
                  $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
                    $.each(data, function(key, value) {
                        $('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_code'] +'</option>');
                  });
                }
                else{
                    $('#unit_id').html('<option value="">No Data</option>');
                }
              } 
        });
        $.ajax({
              type: "POST",
              url: "{{url('/tenantContract/getBuildingDetail')}}",
              data: {'id':id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                //var result = $.parseJSON(data);
                $('#building_name').val(data.building_name);
              } 
          });
    });*/
});
/**********************************************************************/
/*$(document).on('change','.tenants_id',function(){
        var id = $("#tenant_id").val();
        $.ajax({
              type: "POST",
              url: "{{url('/tenants/tenantDetail')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                $('#tenant_code').val(data.tenant_code);
                $('#tenant_name').val(data.tenant_name);
                $('#tenant_company_name').val(data.tenant_company_name);
                $('#tenant_contact_address').val(data.tenant_contact_address);
                $('#tenant_contract_address').val(data.tenant_contact_address);
                $('#tenant_secondary_address').val(data.tenant_secondary_address);
                $('#tenant_pc').val(data.tenant_pc);
                $('#tenant_contact_no').val(data.tenant_contact_no);
                $('#tenant_contact_person').val(data.tenant_contact_person);
                $('#tenant_contact_email').val(data.tenant_contact_email);
                $('#tenant_fax_no').val(data.tenant_fax_no);
                $('#tenant_acc_no').val(data.tenant_acc_no);
                $('#nationality').val(data.nationalities_id);
              } 
          });
    });*/
/**********************************************************************/
$(document).on('change','.Unit',function(){
        var id = $("#unit_id").val();
        $.ajax({
              type: "POST",
              url: "{{url('/tenant-contract/getUnitTypeByUnit')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                $('#unitType').val(data.unit_types_name);
                
              } 
          });
    });
/**********************************************************************/
$(document).on('change','.Occupant',function(){
        var id = $("#occupant_id").val();
        $.ajax({
              type: "POST",
              url: "{{url('/tenant-contract/getOccupant')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                $('#occupant_name').val(data.occupant_name);
                $('#occupant_primary_contact_no').val(data.occupant_primary_contact_no);
                $('#occupant_email').val(data.occupant_email);
                
              } 
          });
    });


 $(".btn-reset").click(function(){ 
            
            $(".increment").first().find('.upload').val(''); 
            $(".upload").valid();
 
       
      });

      $(".btn-success").click(function(){ 
            
            fileUpload($(this));
      });

      $("body").on("click",".btn-danger",function(){ 
          $(this).parents(".control-group").remove();
      });
      $(document).on("change",".upload",function(){
            
            fileUpload($(this));
      });
    
    function fileUpload(file){
   
    var currentRowId    = parseInt(file.closest('.increment').attr('id'));
    var lastRowId       = parseInt($(".increment").first().attr("id"));
  
    $(".form-group .input-group").first().find('.input-group-btn').hide();
    $('.upload').each(function() {
        $(this).rules("add", 
            {
                extension:"Pdf|Doc|Docx|Jpeg|Jpg",
                filesize: {{$upload_size}},
                messages: {
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
                }
            });
    });
   
   $.validator.addMethod('filesize', function(value, element, param) {
    // param = size (in bytes) 
    // element = element to validate (<input>)
    // value = value of the element (file name)
    return this.optional(element) || (element.files[0].size <= param) 
    });
   $(".upload").valid();
   var ext = file.val().split('.').pop().toLowerCase();

   if(ext !='' && $.inArray(ext, ['pdf','docx', 'doc', 'jpeg', 'jpg']) == -1) {
        alert('Invalid Extension!');
        return false;
   }
   else if(fileUpload =='' && currentRowId == lastRowId){
       alert('Please Upload The File');
       return false;    
   }

    if($(".upload").valid() == 1 && currentRowId == lastRowId  ){
    // Next Row Id
        var cont = lastRowId + 1;

        var html = '<div class="control-group input-group increment" id="'+cont +'"style="margin-bottom:10px"><input type="file" name="tenant_document_file_name['+cont +']" class="form-control upload" style="width: 70%;"><div class="input-group-btn" style="display:none"><button class="btn btn-danger" type="button"><i class="fa fa-trash-o "></i></button></div><label id="tenant_document_file_name['+cont +']-error" class="error" for="tenant_document_file_name['+cont +']"></label></div>';
        //html.find('.btn-danger').hide();
        $(".increment").first().before(html);
        
        //alert($(".form-group .input-group").length)
        $(".form-group .input-group:nth-child(2)").find('.input-group-btn').show();
    }
 }

</script>
@endsection
