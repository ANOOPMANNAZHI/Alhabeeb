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
            <div class="page-title">{{(isset($tenantContract))? 'Tenant Contract Edit' :'Tenant Contract Creation' }}</div>
        </div>
        {{ (isset($tenantContract))?   Breadcrumbs::render('tenant-contract.edit',$tenantContract) :  Breadcrumbs::render('tenant-contract.create') }} 
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
            <form autocomplete="off" action="{{ !isset($tenantContract)? route('tenant-contract.store'): route('tenant-contract.update',$tenantContract->id)}}" method="POST" id="tenant_contract_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
                {{csrf_field()}} @if(isset($tenantContract)){{method_field('PUT')}}@endif

                <input  type="hidden" class="form-control" name="sales_enquiry_id" value="{{ !isset($tenantContract)? '': $tenantContract->sale_enquiry_id}}">
                <input type="hidden" name="workflow_id" value="{{$stage}}">
                <div class="dataSearchBox ">

                    <div class="row">
                        <div class="w-100"></div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="tenant_contract_no">Agreement No <small class="textRed">*</small></label>
                                <div class="p-relative">
                                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                                    <input type="text" class="form-control" id="tenant_contract_no" readonly name="tenant_contract_no" required value="{{ isset($tenantContract)?  old('tenant_contract_no',$tenantContract->tenant_contract_no): old('tenant_contract_no',$contractCode)}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="tenant_contract_date">Agreement Date <small class="textRed">*</small></label>
                                <div class="p-relative">
                                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                                    <input type="date" class="form-control" id="tenant_contract_date" placeholder="Agreement Date"  name="tenant_contract_date" value="{{ isset($tenantContract->created_at)?  old('tenant_contract_date',$tenantContract->created_at->format('Y-m-d')): old('tenant_contract_date',date('Y-m-d'))}}">
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="sub-head">Building Details
               
                    <span id="create_build_span" style="display:none">
                    </div>
                    <div class="dataSearchBox ">

                        <div class="row">
                           <div class="col-sm-6">
                            <div class="form-group">
                                <label for="building_id">Building Name<small class="textRed">*</small></label>
                                <div class="p-relative">
                                    <i class="icon icon-building" aria-hidden="true"></i>
                                    <input type="text" placeholder="Enter Building Name" required name="building_name" class="form-control building_name building" id="building_name" value="{{ isset($tenantContract)?  old('building_name',$tenantContract->building->building_name): old('building_name','')}}">

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
            <label for="tenants_id">
			 @if(empty($tenantContract))
                <a href="#" class="create_tenant" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Create Tenant</a>
             @else
			Tenant	
			@endif 
			<small class="textRed">*</small></label>
            <div class="p-relative">
                <i class="icon icon-tenant" aria-hidden="true"></i>
                <input type="text" name="tenants_id" required class="form-control tenants_id" id="tenants_id" value="{{ isset($tenantContract)?  old('tenants_id',$tenantContract->tenant->tenant_name): old('tenants_id','')}}">
                
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
       <!-- <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                    <select class="form-control Unit" name="unit_id" id="unit_id" required>
                        <option value="">Select Unit</option> 


                        @if(isset($tenantContract))
                        @foreach($units as $unit)  
                        @if($tenantContract->building_id == $unit->building_id))
                        <option {{ isset($tenantContract)? ((old('unit_id',$tenantContract->unit_id) == $unit->id)? 'selected' : '') : ''}} value="{{$unit->id}}" >{{$unit->unit_no}}</option>
                        @endif
                        @endforeach 

                        @endif

                    </select>
                </div>
            </div>
        </div>-->
		<div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                    <select class="form-control Unit" name="unit_id" id="unit_id" required>
                        <option value="">Select Unit</option>


                        @if(isset($tenantContract))
                        <option selected value="{{$tenantContract->Unit->id}}">{{$tenantContract->Unit->unit_no}}</option>

                        @foreach($units as $unit)  
                        @if($tenantContract->building_id == $unit->building_id))
                        <option {{ isset($tenantContract)? ((old('unit_id',$tenantContract->unit_id) == $unit->id)? 'selected' : '') : ''}} value="{{$unit->id}}" >{{$unit->unit_no}}</option>
                        @endif
                        @endforeach

                        @endif

                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="unitType">Unit Type</label>
                <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                    <input type="text" name="unitType" class="form-control" id="unitType" readonly value="{{ isset($tenantContract)?  old('unitType',$tenantContract->unit->unit->unit_types_name): old('unitType','')}}">
                </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Unit Usage<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>

                    <select class="form-control" name="unit_usage" id="unit_usage" required>
                        <option {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Residential')? 'selected' : '') : 'selected'}} value="Residential" >Residential</option>
                        <option  {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Commercial')? 'selected' : '') : ''}} value="Commercial" >Commercial</option>


                    </select>
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
                    <label for="simpleFormEmail">Occupant Name<small class="textRed">*</small></label>
                    <div class="p-relative">
                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <!-- <select class="form-control Occupant" name="occupant_id" id="occupant_id">
                    <option value="">Select Occupant</option> 
                    @foreach($occupants as $occupant)
                    <option {{ isset($tenantContract)? ((old('nationality',$tenantContract->tenant->nationalities_id) == $occupant->id)? 'selected' : '') : ''}} value="{{$occupant->id}}" >{{$occupant->occupant_name}}</option>
                    @endforeach
                </select> -->
                <input type="text" class="form-control" required id="occupant_name" id="occupant_name" placeholder="Enter Occupant Name" name="occupant_name" value="{{ isset($tenantContract)?old('occupant_name',((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_name:$tenantContract->occupant->occupant_name)):old('occupant_name','')}}">

                <input type="hidden" class="form-control" id="occupant_id" id="occupant_id" placeholder="Enter Occupant id" name="occupant_id" value="{{ isset($tenantContract->occupant_id)?  old('occupant_id',$tenantContract->occupant->id): old('occupant_id','')}}">
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label for="occupant_primary_contact_no">Occupant Mob No<small class="textRed">*</small></label>
            <div class="p-relative">
                <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control mob_validation_8" required id="occupant_primary_contact_no" placeholder="Enter Occupant Mob No" name="occupant_primary_contact_no" value="{{(isset($tenantContract))?((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_contact_no:$tenantContract->occupant->occupant_primary_contact_no):old('occupant_primary_contact_no','')}}" onkeypress="return isNumber(event)">
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label for="occupant_email">Occupant Email ID</label>
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
				<input type="hidden" name="effectiveDtVal" value="{{old('landlord_contract_valid_to_date',isset($landlordContractInfo->landlord_contract_valid_to_date)? $landlordContractInfo->landlord_contract_valid_to_date->format('d/m/Y') : '')}}" id="effectiveDtVal" class="effectiveDtVal">
                <input type="date" class="form-control" id="tenant_contract_start_date" placeholder="Enter start date" name="tenant_contract_start_date" value="{{old('tenant_contract_start_date',isset($tenantContract->tenant_contract_start_date)? $tenantContract->tenant_contract_start_date->format('Y-m-d') : '')}}">
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="tenant_contract_effective_date">Effective Date</label>
            <div class="p-relative">
                <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" required id="tenant_contract_effective_date" placeholder="Enter Effective date" name="tenant_contract_effective_date" value="{{ isset($tenantContract->tenant_contract_effective_date)?  old('tenant_contract_effective_date',$tenantContract->tenant_contract_effective_date->format('Y-m-d')): old('tenant_contract_effective_date','')}}">
            </div>
        </div>
    </div>
    <div class="w-100"></div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="tenant_contract_valid_to_date">Valid To<small class="textRed">*</small></label>
            <div class="p-relative">
                <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_valid_to_date" placeholder="Enter Valid To" name="tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date',isset($tenantContract->tenant_contract_valid_to_date)? $tenantContract->tenant_contract_valid_to_date->format('Y-m-d') : '')}}" required>
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            <label for="tenant_contract_rent">Rent (P.M)<small class="textRed">*</small></label>
            <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control rent" id="tenant_contract_rent" placeholder="Enter Rent (P.M)" name="tenant_contract_rent" onkeyup="FormatCurrency(this)"
                value="{{ isset($tenantContract)?  old('tenant_contract_rent',numberFormat($tenantContract->tenant_contract_rent)): old('tenant_contract_rent','')}}" required data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            <label for="tenant_contract_value">Contract Value</label>
            <div class="p-relative">
                <i class="icon icon-contract" aria-hidden="true"></i>
                <input type="text" readonly class="form-control " id="tenant_contract_value" placeholder="Contract Value" name="tenant_contract_value" value="{{ isset($tenantContract)?  old('tenant_contract_value',numberFormat($tenantContract->tenant_contract_value)): old('tenant_contract_value')}}">
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
            <input type="text" class="form-control" id="tenant_contract_electric_water" placeholder="Enter Electric/Water Deposit" name="tenant_contract_electric_water" value="{{ isset($tenantContract)?  old('tenant_contract_electric_water',$tenantContract->tenant_contract_electric_water): old('tenant_contract_electric_water',0.00)}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
        </div>
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
        <label for="tenant_contract_vacant_since">Vacant Since</label>
        <div class="p-relative">
            <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
            <input type="date" readonly class="form-control" id="tenant_contract_vacant_since" placeholder="Enter Last Payment Date" name="tenant_contract_vacant_since" value="{{old('tenant_contract_vacant_since',isset($tenantContract->tenant_contract_vacant_since)? $tenantContract->tenant_contract_vacant_since->format('Y-m-d') : '')}}" >
        </div>
    </div>
</div>

<div class="w-100"></div>

<div class="col-sm-6">
    <div class="form-group">
        <label for="tenant_contract_rent_paid_prev_tenant">Rent Paid by Previous Tenant</label>
        <div class="p-relative">
            <i class="fa  fa-money icn-add" aria-hidden="true"></i>
            <input type="text" readonly class="form-control" id="tenant_contract_rent_paid_prev_tenant" placeholder="Rent Paid by Previous Tenant" name="tenant_contract_rent_paid_prev_tenant" value="{{ isset($tenantContract)?  old('tenant_contract_rent_paid_prev_tenant',$tenantContract->tenant_contract_rent_paid_prev_tenant): old('tenant_contract_rent_paid_prev_tenant')}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
        </div>
    </div>
</div>

<div class="col-sm-6">
    <div class="form-group">
        <label for="tenant_contract_muncipality_agr_no">Municipality Agr. No</label>
        <div class="p-relative">
            <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
            <input type="text" class="form-control" id="tenant_contract_muncipality_agr_no" placeholder="Enter Municipality Agr. No" name="tenant_contract_muncipality_agr_no"  value="{{ isset($tenantContract)?  old('tenant_contract_muncipality_agr_no',$tenantContract->tenant_contract_muncipality_agr_no): old('tenant_contract_muncipality_agr_no')}}">
        </div>
    </div>
</div>
<div class="w-100"></div>
<div class="col-sm-6">
    <div class="form-group">
        <label for="tenant_contract_registered_in">Contract Registered In<small class="textRed">*</small></label>
        <div class="p-relative">
            <i class="icon icon-contract" aria-hidden="true"></i>
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
        <label for="tenant_marketing_executive">Marketing Executive Name<small class="textRed">*</small></label>
        <div class="p-relative">
            <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
            <select class="form-control" name="tenant_marketing_executive" required="">
                <option value="">Select Marketing Executive</option>
                @foreach($employeeList as $emp)
                <option value={{$emp->id}} {{(old('tenant_marketing_executive', isset($tenantContract)?  $tenantContract->tenant_marketing_executive : '0') == $emp->id) ? 'selected' : '' }}>{{$emp->employee_name.'('.$emp->employee_code.')'}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="w-100"></div>

@if(isset($tenantContract))
<div class="col-sm-6">
  <div class="form-group">
      <label>Status</label>
      <div class="p-relative">
          <i class="fa fa-money icn-add" aria-hidden="true"></i>
          <select class="form-control" id="Status" name="Status" >
              <option value="">Select Status</option>
              <option {{(old('Status', isset($tenantContract)?  $tenantContract->status : '0') == '0') ? 'selected' : '' }} value="0">Normal</option>
              <option {{(old('Status', isset($tenantContract)?  $tenantContract->status : '1') == '1') ? 'selected' : '' }} value="1">On Hold</option>
              <option {{(old('Status', isset($tenantContract)?  $tenantContract->status : '2') == '2') ? 'selected' : '' }} value="2">No Maintenance</option>
              <option {{(old('Status', isset($tenantContract)?  $tenantContract->status : '3') == '3') ? 'selected' : '' }} value="3">Blacklisted</option>
              <option {{(old('Status', isset($tenantContract)?  $tenantContract->status : '4') == '4') ? 'selected' : '' }} value="4">Legal</option>
          </select>
      </div>
  </div> 
</div>
@endif
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
       <div class="w-100"></div>
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
                <input type="text" class="form-control" id="tenant_contract_deposit_amt" placeholder="Enter Deposit Rent Amount" name="tenant_contract_deposit_amt"  value="{{ isset($tenantContract)?  old('tenant_contract_deposit_amt',numberFormat($tenantContract->tenant_contract_deposit_amt)): old('tenant_contract_deposit_amt','')}}"  onkeyup="FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="tenant_contract_guarantee_cheque_details">Guarantee Cheque</label>
            <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_guarantee_cheque_details" placeholder="Enter Guarantee Cheque" name="tenant_contract_guarantee_cheque_details"  value="{{ isset($tenantContract)?  old('tenant_contract_guarantee_cheque_details',$tenantContract->tenant_contract_guarantee_cheque_details): old('tenant_contract_guarantee_cheque_details','')}}" data-rule-pattern="^[a-zA-Z0-9]+$" data-msg-pattern="Allowed only Alpha Numeric Values" maxlength="100">
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
            <label for="tenant_contract_receipt_amount">Receipt Amount</label>
            <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" onkeyup="FormatCurrency(this)" id="tenant_contract_receipt_amount" name="tenant_contract_receipt_amount"  placeholder="Enter Receipt Amount" value="{{ isset($tenantContract)?  old('tenant_contract_receipt_amount',numberFormat($tenantContract->tenant_contract_receipt_amount)): old('tenant_contract_receipt_amount','')}}" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values">
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
        	<!--<div class="col-sm-2">
                <div class="dataSearchLabel w-100" style="margin-top: 36px"></div>
                <button type="button" class="btn btn-primary add_button">Add</button>
            </div>
            <div class="w-100"></div> -->

            <div class="field_wrapper">
                @if(!empty($tenantContract->tenantDocument)) 

                <!--  <label >If delete Doc, after click SAVE button</label> -->
                @foreach ($tenantContract->tenantDocument  as $doc) 
                <div class="row ro ">
                   <div class="col-sm-4">
                      <div class="form-group">
                       <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}">
                        {{$doc->tenant_documents_name}}  </a>
                    </div>
                </div>
                <div class="col-sm-2">
                  <div class="dataSearchLabel1 w-100"></div>
                  <input type="hidden" name="doc_path_id" value="{{$doc->id}}"><!-- img-closed -->
                  <button type="button" title="Delete" class="btn btn-danger remove_button align-right"><i class="fa fa-trash-o "></i></button>
              </div>
              <div class="w-100"></div>
          </div>
          @endforeach    

          @endif
      </div>       </div>
      <div class="col-sm-12">
        <div class="row">
            <div class="col no-padding">
                <button type="submit" class="btn btn-primary save-contract">Save</button> 
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

<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script>
    $(document).ready(function() {

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
//$("#tenant_contract_start_date").change(function(){
 //     var effectiveDtVal = $("#effectiveDtVal").val();
 //     alert('The Landlord Contract is Expiring on ' + effectiveDtVal);
 // });
$("#tenant_contract_start_date, #tenant_contract_valid_to_date").change(function(){

 var start       = $("#tenant_contract_start_date").val();
 var end         = $("#tenant_contract_valid_to_date").val();
 var expireDt    =  oneYearExpire(start);

 if($(this).attr('id')=='tenant_contract_start_date'){
    $("#tenant_contract_effective_date").val(start);
    $('#tenant_contract_valid_to_date').val(expireDt);
    end =  expireDt;
}	

var effectiveDt = $("#tenant_contract_effective_date").val();
var tenant_contract_rent = $("#tenant_contract_rent").val().replace(",", "");
var noOfDaysInStart   = parseInt(daysInThisMonth(start));

var durationObj = durationEffectiveCalculation(start,end);


if(durationObj.year != NaN)
    $('#yeartxt').val(durationObj.year);
if(durationObj.month != NaN)
    $('#monthtxt').val(durationObj.month);
if(durationObj.day != NaN)
    $('#daytxt').val(durationObj.day);

if(tenant_contract_rent !=''){
    var daySum = monthSum = yearSum = 0;
    var valueObj 	= durationEffectiveCalculation(effectiveDt,end);

    if(valueObj.day){

      daySum = tenant_contract_rent/30*valueObj.day;
  }
  if(valueObj.month){

      monthSum = tenant_contract_rent * valueObj.month;
  }
  if(valueObj.year){
   years = valueObj.year * 12
   yearSum = tenant_contract_rent * years;
}

sumOfRent = daySum + monthSum + yearSum;
$("#tenant_contract_value").val(formatNumber(sumOfRent.toFixed(3)));

}

});


/*****Contract Value calculation with rent, Start date and Valid date*****/
$(document).on('change',".rent, #tenant_contract_effective_date", function(){
    var yearSum = monthSum = daySum= 0;
    var tt = $("#tenant_contract_rent").val();
    var tenant_contract_rent = tt.replace(/,/g, '');
    var effectiveDt = $("#tenant_contract_effective_date").val();
    var end         = $("#tenant_contract_valid_to_date").val();

    var noOfDaysInStart   = parseInt(daysInThisMonth(effectiveDt));

    var valueObj 	= durationEffectiveCalculation(effectiveDt,end); 


    if(valueObj.day){

       daySum = tenant_contract_rent/30 * valueObj.day;
   }
   if(valueObj.month){

       monthSum = tenant_contract_rent * valueObj.month;
   }
   if(valueObj.year){
    var years = valueObj.year * 12
    yearSum = tenant_contract_rent * years;
}

sumOfRent = daySum + monthSum + yearSum;
$("#tenant_contract_value").val(formatNumber(sumOfRent.toFixed(3)));

});  
function getRemanningDays(start) {
    var date = new Date(start);
    var time = new Date(date.getTime());
    time.setMonth(date.getMonth() + 1);
    time.setDate(0);
    var days =time.getDate() > date.getDate() ? time.getDate() - date.getDate() : 0;
    return days;

}
function daysInThisMonth(noOfDaysInDt) {
  var now = new Date(noOfDaysInDt);
  return new Date(now.getFullYear(), now.getMonth()+1, 0).getDate();
}
function monthDiff(d1, d2) {
    var months;
    months = (d2.getFullYear() - d1.getFullYear()) * 12;
    months -= d1.getMonth()+1;
    months += d2.getMonth()+1;

    var months = (d2.getFullYear()-d1.getFullYear())*12+(d2.getMonth()-d1.getMonth());

    return months <= 0 ? 0 : months;
}

/***************************************************************************************/

$(document).ready(function() {
	@if($isYearCorrect == false)
        alert("Current Year Is Not Match With the Sequence Year");
        @endif
	var dtToday = new Date();

    var month = dtToday.getMonth()- 1;  //alert(dtToday.getMonth())
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
	   
    {{-- @php
    if(isset($tenantContract)){
       @endphp 
       $('#tenant_contract_start_date').attr('min', '{{$fromContract}}');
       $('#tenant_contract_effective_date').attr('min', '{{$fromContract}}');
       @if(isset($toContract)) $('#tenant_contract_start_date').attr('max', '{{$toContract}}'); @endif
       @php
    }else{
       @endphp  --}}
       // $('#tenant_contract_start_date').attr('min', maxDate);
     {{-- @php
    }
    @endphp --}} 

   // $('#tenant_contract_valid_to_date').attr('min',  '{{isset($tenantContract->tenant_contract_start_date)? $tenantContract->tenant_contract_start_date->format("Y-m-d"):'+ greaterThan: #tenant_contract_start_date+'}}');
   
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
                $('#occupant_name').val(data.tenant_name);
                $('#occupant_primary_contact_no').val(data.tenant_contact_no);
                $('#occupant_email').val(data.tenant_personal_email);
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
        if (ui.item == null || ui.item == undefined) {
           $('#unit_id').empty();
           $('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
           $("#unitType").val('');
           $('#unit_id-error').show();
       }else {

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
				$('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_no'] +'</option>');
            });
                  //alert(diffDate(new Date(data['contractValid'][0]), new Date(maxDate)));


                 // $('#tenant_contract_start_date').attr('min', data['contractValid'][0]);
                 // $('#tenant_contract_start_date').attr('max', data['contractValid'][1]);

                  
              }
              else{
                $('#unit_id').html('<option value="">No Available Units</option>');
               // $('#tenant_contract_start_date').attr('min', data['contractValid'][0]);
               // $('#tenant_contract_start_date').attr('max', data['contractValid'][1]);
				$('#effectiveDtVal').val(convertDate(data['contractValid'][1]));
            }
        } 
    });
    }
}<!-- -->
});
   /**********************************************************/
   jQuery.validator.addMethod("greaterThan", 
    function(value, element, params) {

        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
        || (Number(value) > Number($(params).val())); 
    },'Must be greater than Start Date.');
   $("#tenant_contract_form").validate({
      rules: {
        tenant_contract_valid_to_date: { greaterThan: "#tenant_contract_start_date"} ,
        tenant_contract_effective_date: { greaterThanEqual: "#tenant_contract_start_date"},
    },
    submitHandler: function(form) {
      $('.save-contract').prop('disabled', true);
      form.submit();
  }, 
});

   jQuery.validator.addMethod("greaterThanEqual", 
    function(value, element, params) {

        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) >= new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
        || (Number(value) > Number($(params).val())); 
    },'Must be greater than or equal to Start Date.');
    // $("#tenant_contract_form").validate();


});

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

        if( data ==''){
            $('#unitType').val('');
            $('#tenant_contract_vacant_since').attr("disabled", false);
            $('#tenant_contract_vacant_since').val('');
            $('#tenant_contract_vacant_since').attr("disabled", true);
            $('#tenant_contract_rent_paid_prev_tenant').val('');

        }
        else{
            $('#unitType').val(data.unit_types_name);
            $('#tenant_contract_vacant_since').val(data.vaccant_date);
            $('#tenant_contract_rent_paid_prev_tenant').val(data.last_rent);
        }
    }, 
    error: function(error)
    {
        $('#unitType').val('');
        $('#tenant_contract_vacant_since').attr("disabled", false);
        $('#tenant_contract_vacant_since').val('');
        $('#tenant_contract_vacant_since').attr("disabled", true);
        $('#tenant_contract_rent_paid_prev_tenant').val('');
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
/**********************************************************************/
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="row ro"><div class="col-sm-6"><div class="form-group"><label for="tenant_document_file_name"></label><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control"  id="tenant_document_file_name"  name="tenant_document_file_name[]" data-rule-extension="pdf|doc|docx|jpeg|jpg" data-msg-extension="Only allowes pdf,docx, doc, jpeg, jpg"></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 36px"></div><button type="button" class="btn btn-warning remove_doc_button"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){

        var values = $("input[name='tenant_document_file_name[]']")
        .map(function(){
            if($(this).val())return $(this).val();}).get();

        var len = $("input[name='tenant_document_file_name[]']").length;
        if ( values.length != len ) {

        }else{
			//Check maximum number of input fields
           if(x < maxField){ 
				  x++; //Increment field counter
				  $(wrapper).append(fieldHTML); //Add field html
               }
           } 
       });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Document')) {
           $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('tenant-contract') }}' + '/' + img_path_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
          });
           $(this).closest('.ro').remove();
            // Remove the file preview.
           // _this.removeFile(file);
       }

        x--; //Decrement field counter
    });

    $(wrapper).on('click', '.remove_doc_button', function(e){
        e.preventDefault();

        $(this).closest('.ro').remove();

        
        x--; //Decrement field counter
    });
    function durationEffectiveCalculation(start, end){

     var remainEndDays = 0;
     var startDtOneMonth= 0;
     var endDtOneMonth = 0;
     var remainDaysInStartMonth = 0 ;
     var noOfDays =0;
     var noOfMonths = 0;
     var endDtOneMonth = 0 ;
     var sumOfMonth = 0 ;
     var sumOfStartDays = 0;
     var sumOfRent = 0;
     var sumOfEndDays = 0;
     var years = 0;
     var days = 0;

     var startObj    = new Date(start);
     var endObj      = new Date(end);

     var startdt  	= start.split('-');
     var enddt     	= end.split('-');

            // Entering date - Start date greater than end date return false
            if(Date.parse(startObj) > Date.parse(endObj) || start =='' || end=='' ){

                return false;
            }
            var startDtMonthNext     = new Date(startObj.getFullYear(), startObj.getMonth()+1, 1);
            var prevEndMonthLastDate = new Date(endObj.getFullYear(), endObj.getMonth(), 0);
            

            // check no of day in this month
            var noOfDaysInStart   = daysInThisMonth(start);
            // Check this date day number is 1. If the condition is true take that full month as One month
            if(startdt[2] == 1){

               startDtOneMonth       = 1;
           }
            // If this date day is greater than one take remaining days
            else if(startdt[2] > 1){ 
                // Month
                remainDaysInStartMonth  = getRemanningDays(start)+1;
                
            }
            // If end date is exist
            if(end){
                // Take no of days in this end date
                var noOfDaysInEnd    = daysInThisMonth(end);
                // If no of days is equal to end date day value Ie 30 == 30 Or 31 == 31. Consider as one month

                if(enddt[2] == noOfDaysInEnd){

                    endDtOneMonth       = 1;
                }
                else{


                // Otherwise take day value Ie- 20-04-2019 - 20 days
                remainEndDays  = enddt[2];
            }

        }
            // Check if it is same month
            if(startObj.getMonth() == endObj.getMonth() && startObj.getFullYear() == endObj.getFullYear() ){

                if(startdt[2] == 1 && enddt[2] == noOfDaysInEnd){
                 noOfMonths       = 1;
             }
             else{
                days     = (enddt[2] - startdt[2]) + 1;

            }

            return { "year":years,"month":noOfMonths,"day":days};


        }
            // If start date less than end date
            if(Date.parse(startDtMonthNext) < Date.parse(prevEndMonthLastDate)){
                // start date next month till end date previous month last date(30 or 31)


                var noOfMonths = monthDiff(startDtMonthNext,prevEndMonthLastDate) + 1;

                if(noOfMonths > 0){
                        // Add consider as One month in start date and End date
                        noOfMonths = startDtOneMonth + endDtOneMonth + noOfMonths;

                    }
                    if(remainDaysInStartMonth > 0){

                        days = days + parseInt(remainDaysInStartMonth);

                    }

                    if(remainEndDays > 0){
                        days = days + parseInt(remainEndDays);

                    }

                    if(days > 0){

                        var isMonth = parseInt(days) / noOfDaysInStart;
                        days = parseInt(days) % noOfDaysInStart;

                        noOfMonths = parseInt(noOfMonths) + parseInt(isMonth);


                    }

                    if(noOfMonths >= 12){
                        years      = parseInt(noOfMonths/12);
                        noOfMonths = noOfMonths%12;

                    }


                }else{

                //return false;
                var days 	= (parseInt(remainDaysInStartMonth) + parseInt(remainEndDays)) % noOfDaysInStart;
                
                var isMonth = parseInt(remainDaysInStartMonth) + parseInt(remainEndDays);

                var isMonth = parseInt(isMonth) / noOfDaysInStart;
                noOfMonths 	= parseInt(noOfMonths) + parseInt(isMonth)+ startDtOneMonth + endDtOneMonth;

            }

            return {"year":years,"month":noOfMonths,"day":days};

        }
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
        function oneYearExpire(startDt){

            var str = startDt;

            var parts = str.split("-");

            var year = parts[0] && parseInt( parts[0], 10 );
            var month = parts[1] && parseInt( parts[1], 10 );
            var day = parts[2] && parseInt( parts[2], 10 );
            var duration = 1;

            if( day <= 31 && day >= 1 && month <= 12 && month >= 1 ) {

                var expiryDate = new Date( year, month - 1, day );
                expiryDate.setFullYear( expiryDate.getFullYear() + duration );

                var day = ( '0' + expiryDate.getDate() ).slice( -2 );
                var month = ( '0' + ( expiryDate.getMonth() + 1 ) ).slice( -2 );
                var year = expiryDate.getFullYear();
                var expDt = year+"-"+ month +"-"+day; 
                var date = new Date(expDt);
                date.setDate(date.getDate()-1);

                day = ( '0' + date.getDate()).slice( -2 );
                month = ( '0' + (date.getMonth()+1)).slice( -2 );
                year = date.getFullYear();

                dateYesterday = year + '-' + month+ '-' +day ;    
                return dateYesterday;

            } else {
                return str;
            }


        }


        /************************* Create Tenant ********************/
         // Create Landlord
    $('.create_tenant').on('click', function(e) {  
        
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('tenantPopup')}}", // This is the url we gave in the route
            data: {"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
   
        
         
    });
    </script>
    @endsection
