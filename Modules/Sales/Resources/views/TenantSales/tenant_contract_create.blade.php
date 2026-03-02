@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Contract Creation</div>
        </div>
        {{ Breadcrumbs::render('contractCreation',$tenantContract->id,106) }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesSearchBox">
<form autocomplete="off" action="{{ !isset($tenantContract)? '': route('contractStore',$tenantContract->id)}}" method="POST" id="tenant_contract_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}}
        
        <input  type="hidden" class="form-control" name="sales_enquiry_id" value="{{ !isset($tenantContract)? '': $tenantContract->sale_enquiry_id}}">
        <input  type="hidden" class="form-control" name="building_id" value="{{ !isset($tenantContract)? '': $tenantContract->building_id}}" id="building_id">
        <input  type="hidden" class="form-control" name="unit_id" value="{{ !isset($tenantContract)? '': $tenantContract->unit_id}}"> 
        <input type="hidden" name="workflow_id" value="{{$stage}}">
<div class="dataSearchBox ">

        <div class="row">
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Agreement No <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control" id="simpleFormEmail" readonly name="tenant_contract_no" required value="{{$tenantContract->tenant_contract_no}}">
                </div>
                </div>
            </div>
            
             @if(isset($tenantContract->created_at))
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_contract_date">Agreement Date <small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="date" class="form-control" id="tenant_contract_date" placeholder="29-08-2018"  name="tenant_contract_date" value={{isset($tenantContract)? $tenantContract->created_at->format('Y-m-d'): old('tenant_contract_date',date('Y-m-d'))}}>
                </div>
                </div>
            </div>
             @endif
        </div>

</div>

<div class="sub-head">Building Details</div>
<div class="dataSearchBox ">

        <div class="row">
             <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Building Code<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-terminal icn-add" aria-hidden="true"></i>
                <input disabled="" type="text" class="form-control" id="simpleFormEmail" placeholder="Enter Building Code" value="{{$tenantContract->building->building_code}}">
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Building Name</label>
                <div class="p-relative">
                    <i class="fa fa-building-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" name="building_name" id="building_name" placeholder="Enter Building Name" value="{{$tenantContract->building->building_name}}">

                
            </div>
            </div>
        </div>
           <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit Code</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <!-- <select name="unit_id" id="unit_id" class="form-control">
                    @if($tenantContract)
                        @foreach($vaccant_units as $vaccant_unit)
                        <option value="{{$vaccant_unit->id}}" selected="selected">{{$vaccant_unit->unit_code}}</option>
                        @endforeach
                    @endif
                </select>-->
                 <input disabled="" type="text" class="form-control" id="simpleFormEmail" placeholder="Enter Unit Code" value="{{$tenantContract->unit->unit_code}}"> 
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit Type<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormEmail" placeholder="Enter Unit No" readonly value="{{$tenantContract->unit->unit->unit_types_name}}">
            </div>
            </div>
        </div>
         <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_code">Tenant Code</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input disabled="" type="text" class="form-control" id="tenant_code" placeholder="Enter Tenanat Code" value="{{$tenantContract->tenant->tenant_code}}">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
        <div class="form-group">
            <label for="tenants_id">Tenant Name<small class="textRed">*</small></label>
            <div class="p-relative">
                <i class="icon icon-tenant" aria-hidden="true"></i>
                <input type="text" name="tenants_id" required class="form-control tenants_id" id="tenants_id" value="{{ isset($tenantContract)?  old('tenants_id',$tenantContract->tenant->tenant_name): old('tenants_id','')}}">
                
                <input type="hidden" name="tenant_id" id="tenant_id" value="{{ isset($tenantContract)?  old('tenant_id',$tenantContract->tenant->id): old('tenant_id','')}}">

                <input type="hidden" name="tenant_name" id="tenant_name" value="{{ isset($tenantContract)?  old('tenant_name',$tenantContract->tenant->tenant_name): old('tenant_name','')}}">
            </div>
        </div>
    </div>
        <div class="w-100"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Unit Usage<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-rebel icn-add" aria-hidden="true"></i>
               
                <select class="form-control" name="unit_usage" id="unit_usage" required>
                                           
                    <option {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Residential')? 'selected' : '') : 'selected'}} value="Residential" >Residential</option>
                    <option  {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Commercial')? 'selected' : '') : ''}} value="Commercial" >Commercial</option>
                    
                    
                </select>
            </div>
            </div> 
        </div>
           
        <div class="w-100"></div>
        @if($tenantContract->tenant_contract_muncipality_agr_no =="")
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Name<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" required id="simpleFormEmail" placeholder="Enter Occupant Name" name="occupant_name" value="{{$tenantContract->tenant->tenant_name}}">

            </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Mob No<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="phone" class="form-control mob_oman_13 mob_validation_13" required onkeypress="return isNumber(event)" id="simpleFormEmail" placeholder="Enter Occupant Mob No" name="occupant_primary_contact_no" value="{{(isset($tenantContract))?((!isset($tenantContract->occupant_id))?$tenantContract->tenant->tenant_contact_no:$tenantContract->occupant->occupant_primary_contact_no):old('occupant_primary_contact_no','00968')}}" >
            </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Occupant Email ID</label>
                <div class="p-relative">
                    <i class="fa fa-envelope-o icn-add" aria-hidden="true"></i>
                <input type="email" class="form-control" id="simpleFormEmail" placeholder="Enter Occupant Email Id" name="occupant_email" value="{{$tenantContract->tenant->tenant_contact_email}}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
         @endif  
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
                <input type="date" class="form-control" id="tenant_contract_start_date" placeholder="Enter Start Date" name="tenant_contract_start_date" value="{{old('tenant_contract_start_date',isset($tenantContract->tenant_contract_start_date)? $tenantContract->tenant_contract_start_date->format('Y-m-d') : '')}}" required>
            </div>
            </div>
        </div>
       
          <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_effective_date">Effective Date<small class="textRed">*</small></label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_effective_date" placeholder="Enter Effective Date" name="tenant_contract_effective_date" value="{{old('tenant_contract_effective_date',isset($tenantContract->tenant_contract_effective_date)? $tenantContract->tenant_contract_effective_date->format('Y-m-d') : '')}}" required>
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
                <input type="text" class="form-control rent" id="tenant_contract_rent" placeholder="Enter Rent (P.M)" name="tenant_contract_rent" value="{{ isset($tenantContract)?  old('tenant_contract_rent',$tenantContract->tenant_contract_rent): old('tenant_contract_rent')}}" required data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_value">Contract Value</label>
                 <div class="p-relative">
                <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" readonly class="form-control " id="tenant_contract_value" placeholder="Contract Value" name="tenant_contract_value" value="{{ isset($tenantContract)?  old('tenant_contract_value', number_format(str_replace(',','',$tenantContract->tenant_contract_value),3)): old('tenant_contract_value')}}">
            </div>
            </div>
        </div>  
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_last_paid_date"> Duration (Year - Month - Day)</label>
                <div class="p-relative">   
				@php
				if(isset($tenantContract->tenant_contract_duration_countdown))
					$count = explode('-',$tenantContract->tenant_contract_duration_countdown);
                else
					$count = array(0,0,0);
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
                <label for="tenant_contract_last_paid_date">Vacant Since</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" disabled="disabled" class="form-control" id="tenant_contract_last_paid_date" name="tenant_contract_last_paid_date" value="{{ isset($tenantContract->tenant_contract_last_paid_date)?  old('tenant_contract_last_paid_date',$tenantContract->tenant_contract_last_paid_date->format('Y-m-d')): old('tenant_contract_last_paid_date')}}" >
            </div>
            </div>
        </div>
       
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_last_paid_amt">Rent Paid by Previous Tenant</label>
                <div class="p-relative">
                    <i class="fa  fa-money icn-add" aria-hidden="true"></i>
                <input type="text" disabled class="form-control" id="tenant_contract_last_paid_amt" placeholder="Enter Rent Paid by Previous Tenant" name="tenant_contract_last_paid_amt" value="{{ isset($tenantContract)?  old('tenant_contract_last_paid_amt',$tenantContract->tenant_contract_last_paid_amt): old('tenant_contract_last_paid_amt',0)}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
        <div class="w-100"></div> 
        <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_muncipality_agr_no">Municipality Agr. No</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_muncipality_agr_no" placeholder="Enter Municipality Agr. No" name="tenant_contract_muncipality_agr_no"  value="{{ isset($tenantContract)?  old('tenant_contract_muncipality_agr_no',$tenantContract->tenant_contract_muncipality_agr_no): old('tenant_contract_muncipality_agr_no')}}">
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_electric_water">Deposit Electric/Water</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="simpleFormEmail" placeholder="Enter Electric/Water Deposit" name="tenant_contract_electric_water" value="{{ isset($tenantContract)?  old('tenant_contract_electric_water',$tenantContract->tenant_contract_electric_water): old('tenant_contract_electric_water','')}}" >
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

                    <option {{ isset($tenantContract)? ((old('tenant_contract_registered_in',$tenantContract->tenant_contract_registered_in) == '1')? 'selected' : '') : 'selected'}} value="1" >Muscat</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_registered_in',$tenantContract->tenant_contract_registered_in) == '2')? 'selected' : '') : ''}} value="2" >Not in Muscat</option>
                </select>
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
        
        <div class="col-sm-12">
            <div class="form-group">
                <label for="tenant_contract_note">Remark</label>
                <div class="p-relative">
                    <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_note" placeholder="Enter Remark" name="tenant_contract_note" value="{{ isset($tenantContract)?  old('tenant_contract_note',$tenantContract->tenant_contract_note): old('tenant_contract_note')}}">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
         <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_document_file_name">Occupant ID Upload (Max : {{$upload_size/1000000}} MB)</label>
                     <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="form-control" id="tenant_document_file_name" name="tenant_document_file_name" value="{{ isset($tenantContract)?  old('tenant_document_file_name',''): old('tenant_document_file_name')}}"  >
                    @if(isset($tenantContract->tenantDocument))
                    @foreach($tenantContract->tenantDocument as $documents)
                   
                   <div class="col-sm-12 visit-card" id="div{{$documents->id}}">
                     <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">{{$documents->tenant_documents_name}}</a>
                        <input type="hidden" class="doc_id" name="doc_id" id="doc_id" value="{{$documents->id}}">
                        <button type="button" title="Delete" class="btn btn-warning remove_button img-closed visit-card-btn"><i class="fa fa-trash-o "></i></button>
                    </div>
                   
                    </br>  
                    @endforeach
                    @endif
                </div>
               
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_document_file_name">Visiting Card Upload (Max : {{$upload_size/1000000}} MB)</label>
                     <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="form-control" id="tenant_visitingcard_file_name" name="tenant_visitingcard_file_name" value="{{ isset($tenantContract)?  old('tenant_visitingcard_file_name',''): old('tenant_visitingcard_file_name')}}" >
                   
                    @if(isset($tenantContract->tenant->tenantVisitingDocs) )
                        @foreach($tenantContract->tenant->tenantVisitingDocs as $documents)
                    <div class="col-sm-12 visit-card" id="divDoc{{$documents->id}}">
                     <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_doc_path_name)}}">{{$documents->tenant_doc_name}}</a>
                        <input type="hidden" class="doc_id" name="doc_id" id="doc_id" value="{{$documents->id}}">
                        <button type="button" title="Delete" class="btn btn-warning remove_button_doc img-closed visit-card-btn"><i class="fa fa-trash-o "></i></button>
                    </div>
                   
                    </br>  

                             
                      @endforeach
                    @endif
                   
                </div>
                </div>
		</div>
		 <div class="w-100"></div>
        <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_marketing_executive">Marketing Executive Name<small class="textRed">*</small></label>
                     <div class="p-relative">
                        <i class="fa fa-id-badge icn-add" aria-hidden="true"></i>
                        @if(isset($employee->employee->employee_name))
                        <input readonly type="text" name="tenant_marketing_executive_name" id="tenant_marketing_executive_name" class="form-control" value="{{old('tenant_marketing_executive_name',isset($tenantContract->marketExecutiveEmployeeInfo->employee_name)? $tenantContract->marketExecutiveEmployeeInfo->employee_name.'('.$tenantContract->marketExecutiveEmployeeInfo->employee_code.')' : (isset($employee->employee->employee_name)?$employee->employee->employee_name.'('.$employee->employee->employee_code.')':''))}}">
                        <input type="hidden" name="tenant_marketing_executive" id="tenant_marketing_executive" class="form-control" value="{{old('tenant_marketing_executive',isset($tenantContract->tenant_marketing_executive)? $tenantContract->tenant_marketing_executive: $employee->employee->id)}}">
                        @else
                         <select class="form-control" name="tenant_marketing_executive" required="">
							<option value="">Select Marketing Executive</option>
							@foreach($employeeList as $emp)
								<option value={{$emp->id}} {{(old('tenant_marketing_executive', isset($tenantContract)?  $tenantContract->tenant_marketing_executive : '0') == $emp->id) ? 'selected' : '' }}>{{$emp->employee_name.'('.$emp->employee_code.')'}}</option>
							@endforeach
						</select>
                       @endif
                    </div>
                </div>
            </div>
        <div class="w-100"></div>
        <div class="col-sm-1"> 
            <div class="form-group">
                <label for="pdc_check">PDC </label>
            </div>
        </div>
       <div class="col-sm-5">
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
                <input type="text" class="form-control" id="tenant_contract_deposit_amt" placeholder="Enter Deposit Rent Amount" name="tenant_contract_deposit_amt"  value="{{ isset($tenantContract)?  old('tenant_contract_deposit_amt',$tenantContract->tenant_contract_deposit_amt): old('tenant_contract_deposit_amt',0)}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
         <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_contract_guarantee_cheque_details">Guarantee Cheque Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_guarantee_cheque_details" placeholder="Enter Amount" name="tenant_contract_guarantee_cheque_details"  value="{{ isset($tenantContract)?  old('tenant_contract_guarantee_cheque_details',$tenantContract->tenant_contract_guarantee_cheque_details): old('tenant_contract_guarantee_cheque_details',0)}}" data-rule-pattern="\d{1,9}(\.\d{0,3})?" data-msg-pattern="Allowed only Numeric and Decimal Values">
            </div>
            </div>
        </div>
        <div class="w-100"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <label for="tenant_contract_receipt_no">Receipt No</label>
                <div class="p-relative">
                    <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" id="tenant_contract_receipt_no" placeholder="Enter Receipt No" name="tenant_contract_receipt_no"  value="{{ isset($tenantContract)?  old('tenant_contract_receipt_no',$tenantContract->tenant_contract_receipt_no): old('tenant_contract_receipt_no')}}">
            </div>
            </div>
        </div>
         <div class="col-sm-4">
            <div class="form-group">
                <label for="tenant_contract_receipt_date">Receipt date</label>
                <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                <input type="date" class="form-control" id="tenant_contract_receipt_date" placeholder="Enter Date" name="tenant_contract_receipt_date"  value="{{ isset($tenantContract->tenant_contract_receipt_date)?  old('tenant_contract_receipt_date',$tenantContract->tenant_contract_receipt_date->format('Y-m-d')): old('tenant_contract_receipt_date')}}">
            </div>
            </div>
        </div>
         <div class="col-sm-4">
            <div class="form-group">
                <label for="simpleFormEmail">Receipt Amount</label>
                <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control" name="tenant_contract_receipt_amt" id="tenant_contract_receipt_amt" placeholder="Enter Amount">
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
                  <input type="file" name="tenant_other_file_name[1]" id="tenant_other_file_name[1]" class="form-control upload" style="width: 70%;">
                    <div class="input-group-btn" style="display: none;"> 
                        <button class="btn btn-danger" type="button"><i class="fa fa-trash-o "></i></button>
                    </div>
                    <label id="tenant_other_file_name[1]-error" class="error" for="tenant_other_file_name[1]"></label>
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
                 <div class="col-sm-4">
                  <div class="form-group">
                     <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}">
                        {{$doc->tenant_documents_name}}  </a>
                  </div>
                </div>
                <div class="col-sm-2">
                      <div class="dataSearchLabel1 w-100"></div>
                      <input type="hidden" name="doc_path_id" value="{{$doc->id}}"><!-- img-closed -->
                      <div class="input-group-btn">
						<button type="button" title="Delete" class="btn btn-danger remove_button align-right"><i class="fa fa-trash-o "></i></button>
                      </div>
                  </div>
              <div class="w-100"></div>
              </div>
          @endforeach    
      
		@endif
        </div>       
        </div>    
        <div class="col no-padding">
			<button type="submit" class="btn btn-primary save-contract">Save</button> 
		</div>
	
</div>

</form>
   
</div>
</div>
</div>


@endsection
@section('scripts')
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script> 
$(document).ready(function() {
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
    
	var dtToday = new Date();
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if(month < 10)
        month = '0' + month.toString();
    if(day < 10)
        day = '0' + day.toString();
    
    var maxDate = year + '-' + month + '-' + day;
    var started   = $("#tenant_contract_start_date").val();
   
   
	{{-- @if(isset($tenantContract))
$('#tenant_contract_start_date').attr('min', '{{$fromContract}}');
@if(isset($toContract)) $('#tenant_contract_start_date').attr('max', '{{$toContract}}'); @endif
$('#tenant_contract_effective_date').attr('min', started);
@else
$('#tenant_contract_start_date').attr('min', maxDate);
$('#tenant_contract_effective_date').attr('min', maxDate);
$('#tenant_contract_valid_to_date').attr('min', maxDate);
 
@endif --}}
	
    jQuery.validator.addMethod("greaterThan", 
              function(value, element, params) {

                  if (!/Invalid|NaN/.test(new Date(value))) {
                      return new Date(value) > new Date($(params).val());
                  }

                  return isNaN(value) && isNaN($(params).val()) 
                      || (Number(value) > Number($(params).val())); 
              },'Must be greater than Start Date.');

    jQuery.validator.addMethod("lessThan", 
              function(value, element, params) {

                  if (!/Invalid|NaN/.test(new Date(value))) {
                      return new Date(value) < new Date($(params).val());
                  }

                  return isNaN(value) && isNaN($(params).val()) 
                      || (Number(value) < Number($(params).val())); 
              },'Must be less than Valid To.');

        $("#tenant_contract_form").validate({
          rules: {
                  tenant_contract_valid_to_date: { greaterThan: "#tenant_contract_start_date" ,
                 
                 },
                 tenant_contract_start_date: { 
                    lessThan: "#tenant_contract_valid_to_date" ,
                      
                  }
              },
             submitHandler: function(form) {
				  $('.save-contract').prop('disabled', true);
				  form.submit();
			},  
              
        });
        $('#tenant_contract_start_date, #tenant_contract_valid_to_date').on('change', function() {
            $('#tenant_contract_start_date').valid(); // <- force re-validation
            $('#tenant_contract_valid_to_date').valid();
        });
		
	/********************************************************************/
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
               
            } 
        });
        }else {
            $('#tenants_id-error').show();
        }
    }
   }
   });
/********************************************************************/
    $('#building_name').autocomplete({
      source : '{!!URL::route('buildingAutocomplete')!!}',
      minlenght:2,
      autoFocus:true,
      change:function(e,ui){
        if (ui.item == null || ui.item == undefined) {
                $("#building_name").val('');
                $('#building_name-error').show();
            }else {
                $('#building_id').val(ui.item.ids);
                var id = ui.item.ids;
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
                                $('#unit_id').append('<option value="'+ value['id'] +'">'+ value['unit_no'] +'</option>');
                          });
                        }
                        else{
                            $('#unit_id').html('<option value="">No Available Units</option>');
                        }
                      } 
                  });
              
           }
        
        }
    }); 
});
/********************************************************************/
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.field_wrapper'); //Input field wrapper
    var fieldHTML = '<div class="row ro"><div class="col-sm-6"><div class="form-group"><label for="tenant_document_file_name"></label><div class="p-relative"><i class="fa fa-paperclip icn-add" aria-hidden="true"></i><input type="file" class="form-control"  id="tenant_document_file_name"  name="tenant_document_file_name[]" data-rule-extension="pdf|doc" data-msg-extension="Only allowes pdf and doc"></div></div></div><div class="col-sm-2"><div class="dataSearchLabel w-100" style="margin-top: 36px"></div><button type="button" class="btn btn-warning remove_button"><i class="fa fa-trash-o "></i></button></div></div>'; //New input field html 
    var x = 1; //Initial field counter is 1
    
    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
        if(x < maxField){ 
            x++; //Increment field counter
            $(wrapper).append(fieldHTML); //Add field html
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        $(this).closest('.row').remove();
        x--; //Decrement field counter
    });
    $(wrapper).on('click', '.remove_doc_button', function(e){
        e.preventDefault();
        /*$(this).parent('div').remove();*/ //Remove field html
        var img_path_id =$(this).siblings('input').val();
        //$(this).closest('.rows').remove();
        if(confirm('Sure want to Delete this Document')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('tenantContract') }}' + '/' + img_path_id,
              type: "DELETE",
              data: {  "_method": 'DELETE', 'img_path_id': img_path_id }
              });
              $(this).closest('.row').remove();
            // Remove the file preview.
           // _this.removeFile(file);
          }
        
        x--; //Decrement field counter
    });



/********************************************************************/
	$("#tenant_contract_start_date, #tenant_contract_valid_to_date").change(function(){
		
			var start       = $("#tenant_contract_start_date").val();
            var end         = $("#tenant_contract_valid_to_date").val();
            
            if($(this).attr('id')=='tenant_contract_start_date')
					$("#tenant_contract_effective_date").val(start);
					
            var effectiveDt = $("#tenant_contract_effective_date").val();
            var tenant_contract_rent = $("#tenant_contract_rent").val();
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

					 daySum = tenant_contract_rent/30 *valueObj.day;
				}
				if(valueObj.month){

					 monthSum = tenant_contract_rent * valueObj.month;
				}
				if(valueObj.year){
					years = valueObj.year * 12
					yearSum = tenant_contract_rent * years;
				}
			   
				sumOfRent = daySum + monthSum + yearSum;
				$("#tenant_contract_value").val(sumOfRent.toFixed(3));
				
			}
           
  });

	 $(document).on('change',".rent, #tenant_contract_effective_date", function(){
            var yearSum = monthSum = daySum= 0;
            var tenant_contract_rent = $("#tenant_contract_rent").val();
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
            $("#tenant_contract_value").val(sumOfRent.toFixed(3));

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

</script>
@endsection
