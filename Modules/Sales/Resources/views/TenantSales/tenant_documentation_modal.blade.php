        <!-- The Modal -->
        
<div class="modal-dialog modal-lg assign">
    <div class="modal-content">
  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Sales Documentation </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    
    <!-- Modal body -->
    <div class="modal-body">
    <form autocomplete="off" action="{{ !isset($tenantContract)? route('tenantContract.store'): route('tenantContract.update',$tenantContract->id)}}" method="POST" id="sales_documentation_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
        {{csrf_field()}} @if(isset($tenantContract)){{method_field('PUT')}}@endif
 
    <div class="col">
      
        <div class="card card-box salesSearchBox">
        
        <input  type="hidden" class="form-control" name="sales_id" value="{{ !isset($tenantContract)? $sales_id: ''}}">
        <input  type="hidden" class="form-control" id="sales_enquiry_id" name="sales_enquiry_id" value="{{ !isset($tenantContract)? $enquiry_id: $tenantContract->sale_enquiry_id}}">
        <input  type="hidden" class="form-control" name="stage" value="{{ !isset($tenantContract)? $stage: 104}}">
        <!-- <div class="sub-head">Building Type Details</div> -->
        <h4>Property Section</h4>
        <div class="dataSearchBox">
        <div class="row">
             <div class="col-sm-6">
                <div class="form-group">
                    <label>Building Name<small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="icon icon-building" aria-hidden="true"></i>
                    <input type="text" placeholder="Enter Building Name" required name="building_name" class="form-control building_name building" id="building_name" value="{{ isset($tenantContract)?  old('building_name',$tenantContract->building->building_name): old('building_name','')}}">

                    <input type="hidden" name="building_id" id="building_id" value="{{ isset($tenantContract)?  old('building_id',$tenantContract->building_id): old('building_id','')}}">
                    <!-- <select class="form-control building" name="building_id" required id="building_id">
                        <option value="">Select Building Name</option> 
                        @foreach($buildings as $building)
                        <option {{ isset($tenantContract)? ((old('building_id',$tenantContract->building_id) == $building->id)? 'selected' : '') : ''}} value="{{$building->id}}" >{{$building->building_name}}</option>
                        @endforeach
                        
                        
                    </select> -->
                </div>
                </div> 
            </div>
           
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Unit No<small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                    <select class="form-control unit_ids" name="unit_id" required id="unit_id">
                        <option value="">Select Unit No</option> 
                        @if(isset($tenantContract))
                        <option  selected value="{{$tenantContract->unit_id}}" >{{$tenantContract->unit->unit_code}}</option>@endif
                        <!-- @if(isset($tenantContract))

                            @foreach($units as $unit)
                            <option {{ isset($tenantContract)? ((old('unit_id',$tenantContract->unit_id) == $unit->id)? 'selected' : '') : ''}} value="{{$unit->id}}" >{{$unit->unit_code}}</option>
                            @endforeach
                        @endif -->
                        
                        
                    </select>
                </div>
                </div> 
            </div>
            <div class="w-100"></div>
             <div class="col-sm-6">
            <div class="form-group">
                <label for="simpleFormEmail">Unit Type</label>
                <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                <input type="text" class="form-control"  id="unit_type" name="unit_type" placeholder="Enter Unit Type" readonly value="{{ isset($tenantContract)?  old('unit_type',$tenantContract->unit->unit->unit_types_name): old('unit_type','')}}">
            </div>
            </div>
        </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Unit Usage<small class="textRed">*</small></label>
                    <div class="p-relative">
                    <i class="icon icon-unit" aria-hidden="true"></i>
                   
                    <select class="form-control" name="unit_usage" id="unit_usage" required>
                        <option  value="">Select Unit Usage</option>                            
                        <option {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Residential')? 'selected' : '') : 'selected'}} value="Residential" >Residential</option>
                        <option  {{ isset($tenantContract)? ((old('unit_usage',$tenantContract->unit_usage) == 'Commercial')? 'selected' : '') : ''}} value="Commercial" >Commercial</option>
                        
                        
                    </select>
                </div>
                </div> 
            </div>
           
        </div>
        
    </div>
    
    <div class="clearfix"></div>
    <h4>Tenant Section</h4>
    <div class="dataSearchBox">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Tenant(If Exist) 
                    <input type="radio" name="isExistTenant" value="1" class="isExistTenant" @if(isset($tenantContract)) checked @endif /> Exist
                    <input type="radio" name="isExistTenant" value="0" class="isExistTenant" @if(empty($tenantContract)) checked @endif /> Not Exist
                    </label>
                    <div class="p-relative">
                    <i class="icon icon-tenant" aria-hidden="true"></i>
                    <input type="text" name="tenants_name" placeholder="If Exist Please Search The Tenant" class="form-control tenants_id" id="tenants_id" value="{{ isset($tenantContract)?  old('tenants_id',$tenantContract->tenant->tenant_name.'-'.$tenantContract->tenant->tenant_code): old('tenants_id','')}}" readonly>

                    <input type="hidden" class="hideShowCls" name="tenant_id" id="tenant_id" value="{{ isset($tenantContract)?  old('tenant_id',$tenantContract->tenant->id): old('tenant_id','')}}">

                    </div>
                </div> 
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_name">Name<small class="textRed">*</small></label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control hideShowCls" id="tenant_name" placeholder="Enter  Name" name="tenant_name" value="{{ isset($tenantContract)?  old('tenant_name',$tenantContract->tenant->tenant_name.'-'.$tenantContract->tenant->tenant_code): old('tenant_name',$enquiry_details->sales_enquiry_name)}}" required {{ isset($tenantContract)?"readonly":""}} >
                </div>
                </div>
            </div>
            <div class="col-sm-6">
            <div class="form-group">
                <label for="tenant_type_id">Tenant Type </label>
                 <div class="p-relative">
                <i class="fa fa-university icn-add" aria-hidden="true"></i>
                  <select class="form-control tenant_type_id"  id="tenant_type_id"  name="tenant_type_id" onchange="tenantTypeField(this.value)"  {{ isset($tenantContract)?"disabled":""}} >
                  <option value=""> Select Tenant Type </option>
                    @foreach($tenantTypes as $tenantType)
                     <option  {{(old('tenant_type_id', isset($tenantContract)?  $tenantContract->tenant->tenant_type_id : $enquiry_details->tenant_type_id) == $tenantType->id) ? 'selected' : '' }} value="{{$tenantType->id}}">{{$tenantType->tenant_types_name}}</option>
                    @endforeach                  
                </select> 
                </div>               
            </div>
          </div>
          </div>
          <div class="row" id="row_ind">
          
			  <div class="col-sm-6">
				<div class="form-group">
					<label for="resident_id">Residence ID No<small class="textRed">*</small></label>
					 <div class="p-relative">
					<i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
					<input  type="text" class="form-control hideShowCls" id="resident_id"  placeholder="Enter Residence ID No" name="resident_id" value="{{ isset($tenantContract)?  old('resident_id',$tenantContract->tenant->resident_id): old('resident_id')}}" data-rule-maxlength="25" data-msg-maxlength="Maximum 25 Characters Allowed" required  {{ isset($tenantContract)?"readonly":""}} >
				  </div>
				   <div class="error2" style="display:none">Residence ID No Exists !</div>
				</div>
			  </div>
			
			  <div class="col-sm-6">
				<div class="form-group">
					<label for="tenant_resident_exp_date">Residence ID Exp Date </label>
					 <div class="p-relative">
					<i class="fa fa-address-card-o icn-add" aria-hidden="true"></i>
					<input  type="date" class="form-control hideShowCls" id="tenant_resident_exp_date"  placeholder="Enter Resident Id" name="tenant_resident_exp_date" value="{{ isset($tenantContract->tenant->tenant_resident_exp_date)?  old('tenant_resident_exp_date',$tenantContract->tenant->tenant_resident_exp_date->format('Y-m-d')): old('tenant_resident_exp_date')}}"  {{ isset($tenantContract)?"readonly":""}} >
				  </div>
				</div>
			  </div>
			 
			
			  <div class="col-sm-6">
				<div class="form-group">
					<label>Nationality<small class="textRed">*</small></label>
					<div class="p-relative">
					<i class="fa fa-money icn-add" aria-hidden="true"></i>
					<input type="text"  name="nationality_name" class="form-control nationality_name hideShowCls" id="nationality_name" value="{{ isset($tenantContract->tenant->nationalities_id)?  old('nationality_name',$tenantContract->tenant->nationality->nationality): old('nationality_name','')}}"  required  {{ isset($tenantContract)?"readonly":""}} >

					<input type="hidden" name="nationality" class="hideShowCls" id="nationality" value="{{ isset($tenantContract->tenant->nationalities_id)?  old('nationality',$tenantContract->tenant->nationalities_id): old('nationality','')}}">
					</div>
				</div> 
			  </div>
			
			
			  <div class="col-sm-6">
                <div class="form-group">
                    <label for="designation">Designation</label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="text" class="form-control hideShowCls" id="designation" placeholder="Enter Designation" name="designation" value="{{ isset($tenantContract)?  old('designation',$tenantContract->tenant->designation): old('designation',$enquiry_details->designation)}}"  {{ isset($tenantContract)?"readonly":""}} >
                    </div>
                </div>
			  </div>
	
         </div>
        <div class="row" id="row_comp" style="display: none">

        <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_company_name">Company Name</label>
                     <div class="p-relative">
                    <i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
                    <input type="phone" class="form-control hideShowCls" id="tenant_company_name" placeholder="Enter Company Name" name="tenant_company_name" value="{{ isset($tenantContract)?  old('tenant_company_name',$tenantContract->tenant->tenant_company_name): old('tenant_company_name','')}}"  {{ isset($tenantContract)?"readonly":""}} >  
                </div>
                </div>
         </div> 
         
         <div class="col-sm-6">
          <div class="form-group">
              <label for="tenant_contact_person">Contact Person</label>
               <div class="p-relative">
                <i class="fa fa-id-card icn-add" aria-hidden="true"></i>
              <input  type="text" class="form-control hideShowCls" id="tenant_contact_person"  placeholder="Enter Contact Person" name="tenant_contact_person" value="{{ isset($tenantContract)?  old('tenant_contact_person',$tenantContract->tenant->tenant_contact_person): old('tenant_contact_person')}}"  {{ isset($tenantContract)?"readonly":""}} >
            </div>
          </div>
        </div>
 
      
        <div class="col-sm-6">
            <div class="form-group">
                <label for="com_reg_no">Commercial Reg. No<small class="textRed">*</small></label>
                <div class="p-relative">
                <i class="fa fa-volume-control-phone icn-add" aria-hidden="true"></i>
                <input type="text" class="form-control hideShowCls" id="com_reg_no" required  placeholder="Enter CR No" name="com_reg_no" value="{{ isset($tenantContract)?  old('com_reg_no',trim($tenantContract->tenant->com_reg_no)): old('com_reg_no')}}"  {{ isset($tenantContract)?"readonly":""}} >
              </div>
              <div class="error3" style="display:none">Commercial Reg. No Exists !</div>
            </div>
          </div>
       </div> 

       <div class="row">
       <div class="col-sm-6">
          <div class="form-group">
              <label for="gsm_no">Mobile No<small class="textRed">*</small></label>
               <div class="p-relative">
                <i class="fa fa-list-ol icn-add" aria-hidden="true"></i>
              <input  type="text" required class="form-control mob_validation mob_oman_code" id="tenant_contact_no"   placeholder="Enter Mobile No" name="mobile_no" value="{{ isset($tenantContract)?  old('tenant_contact_no',trim($tenantContract->tenant->tenant_contact_no)): old('tenant_contact_no','00968')}}"  onkeypress="return isNumber(event)"  {{ isset($tenantContract)?"readonly":""}} >
            </div>
             <div class="error1" style="display:none">Mobile No Exists !</div>
          </div>
      </div>
	  </div>
     </div>
 
    <h4>Contract Section</h4>
    <div class="dataSearchBox">
        <div class="row">
            <div class="col-sm-4">
					<div class="form-group">
						<label for="tenant_contract_start_date">Start Date<small class="textRed">*</small></label>
						 <div class="p-relative">
						<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
						<input type="hidden" name="effectiveDtVal" value="{{old('landlord_contract_valid_to_date',isset($landlordContractInfo->landlord_contract_valid_to_date)? $landlordContractInfo->landlord_contract_valid_to_date->format('d/m/Y') : '')}}" id="effectiveDtVal" class="effectiveDtVal">
						<input type="date" class="form-control" id="tenant_contract_start_date" placeholder="Enter start date" name="tenant_contract_start_date" value="{{old('tenant_contract_start_date',isset($tenantContract->tenant_contract_start_date)? $tenantContract->tenant_contract_start_date->format('Y-m-d') : '')}}" min="" max="" >
					</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label for="tenant_contract_effective_date">Effective Date</label>
						 <div class="p-relative">
						<i class="fa fa-life-ring icn-add" aria-hidden="true"></i>
						<input type="date" required class="form-control" id="tenant_contract_effective_date" placeholder="Enter Effective date" name="tenant_contract_effective_date" value="{{ isset($tenantContract->tenant_contract_effective_date)?  old('tenant_contract_effective_date',$tenantContract->tenant_contract_effective_date->format('Y-m-d')): old('tenant_contract_effective_date','')}}" min = "{{ isset($tenantContract->tenant_contract_start_date)?$tenantContract->tenant_contract_start_date->format('Y-m-d'):''}}" max = "{{ isset($tenantContract->tenant_contract_valid_to_date)?$tenantContract->tenant_contract_valid_to_date->format('Y-m-d'):''}}">
					</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label for="tenant_contract_valid_to_date">End Date<small class="textRed">*</small></label>
						 <div class="p-relative">
						<i class="fa fa-calendar-o icn-add" aria-hidden="true"></i>
						<input type="date" class="form-control" id="tenant_contract_valid_to_date" placeholder="Enter End date" name="tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date',isset($tenantContract->tenant_contract_valid_to_date)? $tenantContract->tenant_contract_valid_to_date->format('Y-m-d') : '')}}" min = "{{ isset($tenantContract->tenant_contract_start_date)?$tenantContract->tenant_contract_start_date->format('Y-m-d'):''}}" >
					</div> 
					</div>
				</div>            
				
			   
				<div class="w-100"></div> 
				<div class="col-sm-6">
					<div class="form-group">
						<label for="tenant_contract_rent">Rent Per Month<small class="textRed">*</small></label>
						 <div class="p-relative">
						<i class="fa fa-money icn-add" aria-hidden="true"></i>
						<input type="text" class="form-control rent allownumericwithdecimal" id="tenant_contract_rent" placeholder="Enter Rent Per Month" name="tenant_contract_rent" value="{{ isset($tenantContract)?  old('tenant_contract_rent',numberFormat($tenantContract->tenant_contract_rent)): old('tenant_contract_rent','')}}" required>
					</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label for="tenant_contract_value">Contract Value</label>
						 <div class="p-relative">
						<i class="icon icon-contract" aria-hidden="true"></i>
						<input type="text" readonly class="form-control allownumericwithdecimal" id="tenant_contract_value" placeholder="Contract Value" name="tenant_contract_value" value="{{ isset($tenantContract)?  old('tenant_contract_value',numberFormat($tenantContract->tenant_contract_value)): old('tenant_contract_value')}}">
					</div>
					</div>
				</div>
				<div class="w-100"></div>
				<div class="col-sm-6">
				<div class="form-group">
					<label for="tenant_contract_last_paid_date"> Duration (Year - Month - Day)</label>
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
             <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_contract_payment_type">Payment Method<small class="textRed">*</small></label>
                     <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <select class="form-control tenant_contract_payment_type" name="tenant_contract_payment_type" required id="tenant_contract_payment_type" >
                    <option value="" >Select Payment Method </option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '1')? 'selected' : '') : ''}} value="1" >Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '2')? 'selected' : '') : ''}} value="2" >Bi-Monthly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '3')? 'selected' : '') : ''}} value="3" >Quarterly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '4')? 'selected' : '') : ''}} value="3" >Half Yearly</option>
                    <option {{ isset($tenantContract)? ((old('tenant_contract_payment_type',$tenantContract->tenant_contract_payment_type) == '5')? 'selected' : '') : ''}} value="3" >Yearly</option>

                </select>
            </div>
            </div>
            </div>
		
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
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tenant_other_file_name">Others (Max : {{$upload_size/1000000}} MB)</label>
                     <div class="p-relative">
                    <i class="fa fa-paperclip icn-add" aria-hidden="true"></i>
                    <input type="file" class="form-control" id="tenant_other_file_name" name="tenant_other_file_name" value="{{ isset($tenantContract)?  old('tenant_other_file_name',''): old('tenant_other_file_name')}}" >
                   
                    @if(isset($tenantContract->tenant->tenantOtherDocs) )
                        @foreach($tenantContract->tenant->tenantOtherDocs as $documents)
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
                        <input readonly type="text" name="tenant_marketing_executive_name" id="tenant_marketing_executive_name" class="form-control" value="{{$employee->employee->employee_name.'('.$employee->employee->employee_code.')'}}">
                        <input type="hidden" name="tenant_marketing_executive" id="tenant_marketing_executive" class="form-control" value="{{old('tenant_marketing_executive',isset($employee->id)? $employee->id:'')}}">
                        @else 
                         <select class="form-control" name="tenant_marketing_executive" required="">
							<option value="">Select Marketing Executive</option>
							
							@foreach($employeeList as $emp)
								<option value={{$emp->id}} {{(old('tenant_marketing_executive', isset($tenantContract)?  $tenantContract->tenant_marketing_executive : '0') == $emp->id) ? 'selected' : '' }}>{{$emp->employee->employee_name.'('.$emp->employee->employee_code.')'}}</option>
							@endforeach
						</select> 
                       @endif
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
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
        
        </div>
    </div>
    
       <div class="row">
        <div class="col">
             <div class="w-100"></div>
           <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
        </div>
        </div>      
    </div>
    
    </div>

    </div> 
    
    </form>
    </div>
    
    <!-- Modal footer -->
    <div class="modal-footer">
      <!-- <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button> -->
    </div>

    </div>
</div>

<script>
	@if(isset($tenantContract))
		tenantTypeField('{{$tenantContract->tenant->tenant_type_id }}');		
	@else
		tenantTypeField('{{$enquiry_details->tenant_type_id}}');
	@endif
	
	$(".isExistTenant").click(function(){
	
		if($(this).val()==1){
		
			$("#tenants_id").val([]).attr('readonly',false);
			$(".hideShowCls").val([]).attr('readonly',true);
			$(".tenant_type_id").prop("disabled", true);
			$("#tenants_id").val([]).attr('readonly',false);
			$("#sales_documentation_form").validate();
			$("#tenant_contact_no").val('00968');
			$(".error1,.error2,.error3").css("display","none");
            $("#submitBtn").prop('disabled',false);
			
		}
		else{
			
			$(".hideShowCls").attr('readonly',false);
			$(".hideShowCls").val([])
			//$("#tenant_resident_exp_date").attr('readonly',false);
			//$("#tenant_resident_exp_date").val('yyyy/mm/dd');
			$(".tenant_type_id").prop("disabled", false);			
			$("#tenants_id").val([]).attr('readonly',true);
			$("#tenant_id").val([]);
			$("#sales_documentation_form").validate();
			$("#tenant_contact_no").val('00968').attr('readonly',false);
			$(".error1,.error2,.error3").css("display","none");
            $("#submitBtn").prop('disabled',false);
			
		}
	});
	//$("#tenant_contract_start_date").change(function(){
  //    var effectiveDtVal = $("#effectiveDtVal").val();
  //    alert('The Landlord Contract is Expiring on ' + effectiveDtVal);
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

  $(document).ready(function() {
	 $('.mob_oman_code').on('keypress, keydown', function(event) {
	
		var $field = $(this);
		var readOnlyLength = 5;
		if ((event.which != 37 && (event.which != 39)) &&
			  ((this.selectionStart < readOnlyLength) ||
				((this.selectionStart == readOnlyLength) && (event.which == 8)))) {
			  return false;
		}
	    
	});
	 $(".mob_validation").addClass("customRegMob");
  
	 $.validator.addClassRules({
			customRegMob: {
				customRegMob: true
			}
	 });
	 jQuery.validator.addMethod("customRegMob", function(value, element) {
			return this.optional(element) || /^\d{13}$/i.test(value);
	 }, "Please Enter 13 Digit Number");
	/*****************************************************************************************/ 
    $("#tenant_contact_no").keyup(function(){
		
      var tenant_contact_no = $("#tenant_contact_no").val();
      var tenant_id = $("#tenant_id").val();
      //alert(tenant_contact_no);
      $.ajax({
        method: "POST",
        url: "{{route('checkMobileExist')}}",
        data: { tenant_contact_no: tenant_contact_no, tenant_id: tenant_id, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           
           if(data > 0){
            $(".error1").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
            return false;
            
          }else{
           $(".error1").css("display","none");
           $("#submitBtn").prop('disabled',false);
           return false;
         }

       }
     });
    });
    /*****************************************************************************************/ 
     $("#resident_id").keyup(function(){
      var resident_id = $("#resident_id").val();
      var tenant_id = $("#tenant_id").val();
      //alert(resident_id);
      $.ajax({
        method: "POST",
        url: "{{route('checkResidenceExist')}}",
        data: { resident_id: resident_id, tenant_id: tenant_id, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
            $(".error2").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
          }else{
           $(".error2").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }

       }
     });
    });
    /*****************************************************************************************/    
      $("#com_reg_no").keyup(function(){
      var com_reg_no = $("#com_reg_no").val();
      var tenant_id = $("#tenant_id").val();
      //alert(com_reg_no);
      $.ajax({
        method: "POST",
        url: "{{route('checkCommercialExist')}}",
        data: { com_reg_no: com_reg_no, tenant_id: tenant_id, 
          "_token" : $('meta[name="csrf-token"]').attr('content')},
          success: function(data){
           //alert(data);
           if(data > 0){
            $(".error3").css("display","block").css("color","red");
            $("#submitBtn").prop('disabled',true);
          }else{
           $(".error3").css("display","none");
           $("#submitBtn").prop('disabled',false);
         }

       }
     });
    });
	
	
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
	@else	--}}
		//$('#tenant_contract_start_date').attr('min', maxDate);
		//$('#tenant_contract_effective_date').attr('min', maxDate);
		//$('#tenant_contract_valid_to_date').attr('min', maxDate);
 
	{{-- @endif --}}
	
	
    
    /***********************************************************************/  
    $('#tenants_id').autocomplete({
      source : '{!!URL::route('tenantsAutocomplete')!!}',
      minlenght:2,
      appendTo: "#myModal1",
      autoFocus:true,
      change:function(e,ui){
            if (ui.item == null || ui.item == undefined) {
                $("#tenants_id").val('');
                $("#tenant_id").val('');
                $('#tenant_name').val("");
	            $('#tenant_company_name').val("");
	            $('#designation').val("");
	            $('#nationality').val("");
	            $('#nationality_name').val("");
                $('#tenants_id-error').show();
            }else {
                var id = ui.item.ids;
                $.ajax({
                      type: "POST",
                      url: "{{url('/tenants/tenantDetail')}}",
                      data: {"id":id,"_token": "{{ csrf_token() }}"},
                      cache: false,
                      dataType: "json",
                      success: function(data)
                      {
						
						if(data.id != ''){  
							
							
							//$('#tenant_code').val(data.tenant_code);
							$('#tenant_id').val(data.id);
							$('#tenant_name').val(data.tenant_name);
							$('#tenant_company_name').val(data.tenant_company_name);
							$('#tenant_type_id').val(data.tenant_type_id);
							$('#resident_id').val(data.resident_id);
							$('#com_reg_no').val(data.com_reg_no);
							if(data.tenant_resident_exp_date !=''){
								var resident_exp_dt = new Date(data.tenant_resident_exp_date);
							
								var day = ("0" + resident_exp_dt.getDate()).slice(-2);
								var month = ("0" + (resident_exp_dt.getMonth() + 1)).slice(-2);
								var resident_exp = resident_exp_dt.getFullYear()+"-"+(month)+"-"+(day) ;
								$('#tenant_resident_exp_date').val(resident_exp);
							}
							$('#tenant_contact_no').val(data.tenant_contact_no);
							$('#tenant_contact_person').val(data.tenant_contact_person);
							$('#designation').val(data.designation);
							$('#nationality').val(data.nationalities_id);
							
							if(data.tenant_type_id){
								tenantTypeField(data.tenant_type_id);
							}
							//$('#nationality_name').val(data.nationalities_id);
							$.ajax({
								type: "POST",
								url: "{{url('/nationalityDetails')}}",
								data: {"id":data.nationalities_id,"_token": "{{ csrf_token() }}"},
								cache: false,
								dataType: "json",
								success: function(data)
								{	if(data != null){
										$('#nationality_name').val(data.nationality);
									}
								}
							});
							$("#sales_documentation_form").validate();
						}
                        else{
							$('#tenant_id').val([]);
							$('#tenant_name').val([]);
							$('#tenant_company_name').val([]);
							$('#tenant_type_id').val([]);
							$('#resident_id').val([]);
							$('#com_reg_no').val([]);
							$('#tenant_resident_exp_date').val([]);
							$('#tenant_contact_no').val([]);
							$('#designation').val([]);
							$('#nationality').val([]);
							$('#nationality_name').val([]);
							$('#tenant_contact_person').val([]);
						}
                      } 
                  });
            }
        }
    });
/***********************************************************************/
    $('#nationality_name').autocomplete({
      source : '{!!URL::route('nationalityAutocomplete')!!}',
      minlenght:2,
      appendTo: "#myModal1",
      autoFocus:true,
      change:function(e,ui){
            if (ui.item == null || ui.item == undefined) {
                $("#nationality").val('');
                $("#nationality_name").val('');
                $('#nationality-error').show();
            }else {
				
              $('#nationality').val(ui.item.ids);
              
           } 
        
        }
    });
/************************************************************/
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
                var sales_enquiry_id = $("#sales_enquiry_id").val();
                var id = ui.item.ids;
                $.ajax({
                      type: "POST",
                      url: "{{url('/tenantContract/buildingByUnit')}}",
                      data: {"id":id,"sales_enquiry_id":sales_enquiry_id,"_token": "{{ csrf_token() }}"},
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

						 // $('#tenant_contract_start_date').attr('min', data['contractValid'][0]);
						// $('#tenant_contract_start_date').attr('max', data['contractValid'][1]);
						  $('#effectiveDtVal').val(convertDate(data['contractValid'][1]));
						  
						}
						else{
							$('#unit_id').html('<option value="">No Available Units</option>');
						}
                      } 
                  });
              
           }

        
        /*$('#building_name').val(ui.item.value);*/
        
        }
    });
/************************************************************/
    $(document).on("change","#tenant_document_file_name, #tenant_visitingcard_file_name",function(){
        $("#tenant_document_file_name, #tenant_visitingcard_file_name").valid();
    });

    $("#sales_documentation_form").validate({
        rules: {
        'tenant_contract_effective_date': { greaterThanEqual: "#tenant_contract_start_date"},
        'tenant_contract_valid_to_date': { greaterThan: "#tenant_contract_start_date"},   
        'tenant_document_file_name':{
            extension: "Pdf|Doc|Docx|Jpeg|Jpg", 
            filesize: {{$upload_size}}
            },
        'tenant_visitingcard_file_name':{
            extension: "Pdf|Doc|Docx|Jpeg|Jpg", 
            filesize: {{$upload_size}}
            }            
        },
        messages: {
            'tenant_document_file_name':{
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
            },
            'tenant_visitingcard_file_name':{
                   extension: "Support Only Following File type : Pdf|Doc|Docx|Jpeg|Jpg",
                   filesize: "File Must Be Less Than {{$upload_size/1000000}}MB",
            }
        },
        submitHandler: function(form) {
			
			var tenant_contact_no = $("#tenant_contact_no").val();
		    var tenant_id = $("#tenant_id").val();
			var success = '';
			var tenant_type_id	 = $(".tenant_type_id").val();
			 // Check Commercial Reg. No unique 2 - Company
			if(tenant_type_id == 2 || tenant_type_id == 3 ||tenant_type_id == 4){
				var com_reg_no = $("#com_reg_no").val();
				$.ajax({
					method: "POST",
					url: "{{route('checkCommercialExist')}}",
					data: { com_reg_no: com_reg_no, tenant_id: tenant_id, 
					  "_token" : $('meta[name="csrf-token"]').attr('content')},
					  success: function(data){
					   //alert(data);
					   if(data > 0){
						$(".error3").css("display","block").css("color","red");
						$("#com_reg_no").focus();
						$("#submitBtn").prop('disabled',true);
					  }else{
					   $(".error3").css("display","none");
					   $("#submitBtn").prop('disabled',false);
					   $.ajax({
							method: "POST",
							url: "{{route('checkMobileExist')}}",
							data: { tenant_contact_no: tenant_contact_no, tenant_id: tenant_id, 
							  "_token" : $('meta[name="csrf-token"]').attr('content')},
							  success: function(data){
							   
							   if(data > 0){
								$(".error1").css("display","block").css("color","red");
								$(':submit', form).prop('disabled',true);
								$("#tenant_contact_no").focus();
								
							  }else{
							   $(".error1").css("display","none");
							   $(':submit', form).attr('disabled', 'disabled');
							   form.submit();
							  }

						   }
						 });
					 }

				   }
				});
			 }
			 // Check Residence ID No unique - 7 - Individual
			 if(tenant_type_id == 1){
				 var resident_id = $("#resident_id").val();
				 $.ajax({
					method: "POST",
					url: "{{route('checkResidenceExist')}}",
					data: { resident_id: resident_id, tenant_id: tenant_id, 
					  "_token" : $('meta[name="csrf-token"]').attr('content')},
					  success: function(data){
					   //alert(data);
					   if(data > 0){
						$(".error2").css("display","block").css("color","red");
						$("#resident_id").focus();
						$("#submitBtn").prop('disabled',true);
						
					  }else{
					   $(".error2").css("display","none");
					   $("#submitBtn").prop('disabled',false);
					  $.ajax({
							method: "POST",
							url: "{{route('checkMobileExist')}}",
							data: { tenant_contact_no: tenant_contact_no, tenant_id: tenant_id, 
							  "_token" : $('meta[name="csrf-token"]').attr('content')},
							  success: function(data){
							   
							   if(data > 0){
								$(".error1").css("display","block").css("color","red");
								$(':submit', form).prop('disabled',true);
								$("#tenant_contact_no").focus();
								
							  }else{
							   $(".error1").css("display","none");
							   $(':submit', form).attr('disabled', 'disabled');
							   form.submit();
							  }

						   }
						 });
					 }

				   }
				 });
			 }
			
			
        },
    }); 
    $.validator.addMethod('filesize', function(value, element, param) {
      
      return this.optional(element) || (element.files[0].size <= param) 
      });
    jQuery.validator.addMethod('maxDate', function (v, el, maxDate) {
    if (this.optional(el)) {
        return true;
    }
    
    var selectedDate = new Date($(el).val());
    maxDate = new Date(maxDate.setHours(0));
    maxDate = new Date(maxDate.setMinutes(0));
    maxDate = new Date(maxDate.setSeconds(0));
    maxDate = new Date(maxDate.setMilliseconds(0));

    return maxDate <= selectedDate;
    }, 'Date is greater than {0}.');

    jQuery.validator.addMethod("greaterThan", 
        function(value, element, params) {

            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) >= new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) >= Number($(params).val())); 
        },'Must be greater than Start Date.');
     
     jQuery.validator.addMethod("greaterThanEqual", 
        function(value, element, params) {

            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) >= new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val()) 
                || (Number(value) >= Number($(params).val())); 
        },'Must be greater than Start Date.');
    
  });

	function tenantTypeField(val){

		
		var Tenant_type = val;
		if(Tenant_type == 1){

			$('#row_comp').hide();
			$('#row_ind').show(); 
			$("#resident_id").show().prop('required',true);
		}else{

			$('#row_comp').show();
			$('#row_ind').hide(); 
			$("#resident_id").hide().prop('required',false);
		}


	}


  $(document).on('change',".unit_ids", function(){
    id = $("#unit_id").val();
       $.ajax({
              type: "POST",
              url: "{{url('/tenantContract/unitDetail')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                //var result = $.parseJSON(data);
                $('#unit_type').val(data);
              } 
          });
    });
   /*****Contract Value calculation with rent, Start date and Valid date*****/
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
    /**********************************************/    
    $(document).on('click', '.remove_button_doc', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();

        var doc_id =$(this).siblings('input').val();

        if(confirm('Are you sure you want to delete this Visiting Card Upload?')) {
             $.ajax({
              headers : {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
              url: '{{ url('tenants') }}' + '/' + doc_id,
              type: "DELETE",
              success: function(data)
                  {
                    //var result = $.parseJSON(data);
                     $("#divDoc"+doc_id).remove();
                  } 
          });
            
      }
            
          

    });
    $(document).on('click', '.remove_button', function(e){
        e.preventDefault();
        /*$(this).parent('div .row').remove(); //Remove field html*/
        //var doc_id  = $(this).parent('div .row').remove();

        var doc_id =$(this).siblings('input').val();

        //$(this).closest('.rows').remove();
        if(confirm('Are you sure you want to delete this ID Upload')) {
             $.ajax({
                  url: '{{ url('deleteDocument') }}' + '/' + doc_id,
                  type: "GET",
                  data: {  "_token": "{{ csrf_token() }}"},
                  success: function(data)
                  {
                    //var result = $.parseJSON(data);
                     $("#div"+doc_id).remove();
                  } 
              });
              
            
            // Remove the file preview.
           // _this.removeFile(file);
          }
       

    });
    $(document).on('change','.tenant_id',function(){
        var id = $("#tenant_id").val();
        $.ajax({
              type: "POST",
              url: "{{url('/tenants/tenantDetail')}}",
              data: {"id":id,"_token": "{{ csrf_token() }}"},
              cache: false,
              dataType: "json",
              success: function(data)
              {
                $('#tenant_company_name').val(data.tenant_company_name);
                $('#tenant_name').val(data.tenant_name);
                $('#nationality').val(data.nationalities_id);
              } 
          });
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
/****************************partial comment ***************************************/
$('input[id="pdc2"]').on('change', function() {
   // $('textarea[name="partial_comment"]').attr('disabled', false).focus();
   $('#partial_comment').show();
});
$('input[id="pdc1"]').on('change', function() {
  // $('textarea[name="partial_comment"]').attr('disabled', true);
  $('#partial_comment').hide();
});

$(document).ready(function(){
   var pdc1 = $("input[type=radio][id='pdc1']:checked").val();
   var pdc2 = $("input[type=radio][id='pdc2']:checked").val();
   if(pdc1 == undefined && pdc2 != undefined){
    // $('textarea[name="partial_comment"]').attr('disabled', false);
    $('#partial_comment').show();
   }else{
     //$('textarea[name="partial_comment"]').attr('disabled', true);
     $('#partial_comment').hide();
   }
});
</script>
