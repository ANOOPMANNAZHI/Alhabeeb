@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">{{$title}}</div>
        </div>
        {{ Breadcrumbs::render('leadAssign.revokeProcess',$routes,$details->id,$details->work_flow_processes_code,$title)}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col">
        <h4>
            {{$details->sales_enquiry_name}}
            @if($actionBtn)
				@if($details->work_flow_processes_code == 108 && auth()->user()->can('tenant_direct_revoke_contract_approve_reject'))
						               
						<button type="submit" id="reject" class="btn btn-circle btn-primary align-right reject" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" >Reject</button>
					  
						<button type="submit" id="accept" class="btn btn-circle btn-primary align-right accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" >Accept</button>
						<input type="hidden" name="enquiryid" value="{{$details->id}}" id="enquiryid">
					
				@endif 
                <input type="hidden" name="tenant_contract_id" value="{{$tenantContracts->id}}" id="tenant_contract_id"> 
				@if($details->work_flow_processes_code == 107 && auth()->user()->can('tenant_direct_revoke_contract_approve_reject'))
                    @if($tenantContracts->tennat_contract_direct_indirect_status == 1)
						<button type="submit" id="reject" class="btn btn-circle btn-primary align-right reject" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" >Reject</button>       
                    @else
						<button type="submit" class="btn btn-circle btn-primary align-right closed_flow" data-toggle="modal" data-target="#myModal" data-id = "CL">Close</button>
					@endif  
					<input type="hidden" name="occuipied_units" id="occuipied_units" value="{{getOccupiedUnits($tenantContracts->id)}}">

                    <button title = "Accept" type="submit" id="accept" class="btn btn-circle btn-primary align-right accept_direct" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-enquiryid="{{$tenantContracts->sale_enquiry_id}}" data-tenant_contract_id="{{$tenantContracts->id}}" data-workflow_id="{{$tenantContracts->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">Accept</button>
						<!--<button type="submit" id="accept" class="btn btn-circle btn-primary align-right accept_flow" data-toggle="modal" data-target="#myModal" data-id = "ACPT" >Accept</button> -->
						<input type="hidden" name="enquiryid" value="{{$details->id}}" id="enquiryid">
						<input type="hidden" name="sales_id" value="{{$details->id}}" id="sales_id">
						<input type="hidden" name="workflow_id" value="{{$details->work_flow_processes_code}}" id="workflow_id">
				@endif
			@endif
        </h4>
        </div>
    </div>

    <div class="card-body">
        
        @if($details->work_flow_processes_code >= 105)
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Tenant</dt>
                <dd>{{$tenantContracts->tenant->tenant_name}}</dd>
                <dt>Tenant Contact No</dt>
                <dd>{{$tenantContracts->tenant->tenant_contact_no?? 'NA'}}
                </dd>
                @if(isset($tenantContracts->tenant->tenant_contact_email))
                <dt>Email</dt>
                <dd><a href="mailto:{{$tenantContracts->tenant->tenant_contact_email}}">
                    {{$tenantContracts->tenant->tenant_contact_email?? 'NA'}}</a>
                </dd>
                @endif               
            </dl> 
        </div>
        @endif
        <div class="clearfix"></div>
        <div class="w-100 mt-4"></div>
        
       

        <!--
        <a class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
			<span id="enquiry_info">Show Enquiry Detail</span>
		</a> -->
        <div class="clearfix"></div>
        <div id="show" class="collapse">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li><li>{{$details->enquiryOwner->username??'NA'}}</li>
                            <li class="bld">Enquiry Type</li>
                            <li>@if($details->tenant_type_id){{$details->tenantType->tenant_types_name}}@else {{'NA'}}@endif</li>
                            
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->sales_referred_by??  'NA'}}</li>
                            <li class="bld">Price Range</li>
                            <li>@if($details->priceRanges->count()>0){{$details->priceRanges->implode('price_ranges_name',', ')?? 'NA'}}  @else {{'NA'}} @endif </li>
                            <li class="bld">Location</li>
                            <li>@if($details->locations->count()>0){{$details->locations->implode('locations_name',', ') ?? 'NA'}}@else {{'NA'}}@endif</li>
                            <li class="bld">Remark</li><li>{{$details->sales_note ??  'NA'}}</li>
                            <li class="bld">Alternative No</li><li>{{$details->alternative_no ??  'NA'}}</li>
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Unit Type</li>
                            <li>@if($details->unitTypes->count()>0){{$details->unitTypes->implode('unit_types_name',', ') ?? 'NA'}}@else {{'NA'}} @endif</li>
                            <li class="bld">Status</li>
                            <li>{{$details->workFlowProcess->work_flow_processes_name ??  'NA'}}</li>
                            <li class="bld">Move In Date</li>
                            <li>{{isset($details->sales_move_in_date)?$details->sales_move_in_date->format('m/Y'):''}}</li>
                            <li class="bld">Enquiry Date</li>
                            <li>{{$details->created_at->format('d/m/Y') ?? 'NA'}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{$details->sales_company_name ??  'NA'}}</li>
                            <li class="bld">Square Meter</li>
                            <li>{{$details->sales_size ??  'NA'}}</li>
                            <li class="bld">No Of Units</li>
                            <li>{{$details->sales_no_of_unit ??  'NA' }}</li>
                            <li class="bld">Source</li>
                            <li>{{$details->enquirySource->enquiry_sources_name ??  'NA'}}</li>
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 @if($details->work_flow_processes_code >= 107)
<div class="card card-box salesLeadBox">
    <div class="card-head">
            <div class="col"><h4>Tenant Contract</h4></div>
    </div>
 
    <div class="row">
    <div class="col">
      <div class=" salesSearchBox">
           
       <div class="dataSearchBox">
        <div class="card-body row">
			
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContracts->tenant_contract_no}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Agreement Date  :  </b><span>{{isset($tenantContracts->created_at)?$tenantContracts->created_at->format('d/m/Y'):""}}</span></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="sub-head">Building Details</div>
    <div class="dataSearchBox">    
        <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Building Name :  </b><span>{{$tenantContracts->building->building_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Building Code :  </b><span>{{$tenantContracts->building->building_code}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit No :  </b><span>{{$tenantContracts->unit->unit_no}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContracts->unit->unit_code}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContracts->tenant->tenant_name}}</span></h5>
                </div>
            </div> 
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContracts->tenant->tenant_code}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContracts->unit_usage}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Name :  </b><span>
                        @if($tenantContracts->occupant_id)
                        {{$tenantContracts->occupant->occupant_name??''}}
                        @else
                        {{$tenantContracts->tenant->tenant_name}}
                        @endif
                    </span></h5>
                </div>
            </div> 
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Mob No:  </b><span>
                        @if(isset($tenantContracts->occupant->occupant_primary_contact_no))
                        {{$tenantContracts->occupant->occupant_primary_contact_no?? "NA"}}
                        @else
                        {{$tenantContracts->tenant->tenant_contact_no}}
                        @endif
                        </span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Occupant Email:  </b><span>
                         @if(isset($tenantContracts->occupant->occupant_email))
                        {{$tenantContracts->occupant->occupant_email}}
                        @else
                        {{$tenantContracts->tenant->tenant_contact_email}}
                        @endif
                        </span></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="sub-head">Contract Details</div>
    <div class="dataSearchBox">    
        <div class="card-body row">
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Start Date :  </b><span>{{$tenantContracts->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContracts->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Valid To :  </b><span>{{$tenantContracts->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
                </div>
            </div>

            @if(isset($tenantContracts->tenant_contract_duration_countdown))
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                   <h5 class="details"><b>Duration :  </b><span>
                      @php 
                      $duration = explode('-',$tenantContracts->tenant_contract_duration_countdown)
                      @endphp


                      {{$duration[0]}} Year
                      {{$duration[1]}} Month
                      {{$duration[2]}} Days


                  </span></h5>
              </div>
          </div>
          @endif
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContracts->tenant_contract_value)?numberFormat($tenantContracts->tenant_contract_value):"NA"}} OMR</span></h5>
            </div>
        </div>
		<div class="col-lg-6 p-t-20">
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContracts->status_name}}</span></h5>
                </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Rent (P.M) :  </b><span>{{ numberFormat($tenantContracts->tenant_contract_rent)}} OMR</span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContracts->vaccant_date)?$tenantContracts->vaccant_date->format('d/m/Y'):""}} </span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Rent Paid by Previous Tenant :  </b><span>{{isset($tenantContracts->last_rent)? numberFormat($tenantContracts->last_rent)."   OMR":"NA"}} </span></h5>
            </div>
        </div>
        @if(isset($tenantContracts->tenant_contract_muncipality_agr_no))
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContracts->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
            </div>
        </div>
        @endif
        @if(isset($tenantContracts->tenant_contract_electric_water))
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$tenantContracts->tenant_contract_electric_water?? "NA"}}</span></h5>
            </div>
        </div>
        @endif
        @if($tenantContracts->tenant_contract_registered_in)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContracts->TenantContractRegisteredInName}}</span></h5>
            </div>
        </div>
        @endif
        @if($tenantContracts->tenant_contract_payment_type)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContracts->TenantContractPaymentName}}</span></h5>
            </div>
        </div>
        @endif

        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>PDC  :  </b><span>{{($tenantContracts->pdc_check ==null)?"No":"Yes"}} </span></h5>
            </div>
        </div>

        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Invoice Generate  :  </b><span>{{($tenantContracts->invoice_check ==null)?"No":"Yes"}} </span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Registered in Municipality  :  </b><span>{{($tenantContracts->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}} </span></h5>
            </div>
        </div>   
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Contract Registered Date  :  </b><span>{{isset($tenantContracts->tenant_contract_registered_date)?$tenantContracts->tenant_contract_registered_date->format('d/m/Y'):"NA"}} </span></h5>
            </div>
        </div>    
        @if($tenantContracts->status)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Status  :  </b><span>
                @switch($tenantContracts->status)
                    @case(2)
                        On Hold
                    @break
                    @case(3)
                        No Maintenance
                    @break
                    @case(4)
                        Blacklisted 
                    @break
                    @case(5)
                        Move to Legal
                    @break
                    @case(7)
                        Alert
                    @break
                    @case(8)
                        Observation
                    @break
                    @case(9)
                        Watch
                    @break
                    @default
                        No
                @endswitch
                
                </span></h5>
            </div>
        </div>
        @endif
        @if($tenantContracts->tenant_contract_note)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Remark :  </b><span>{{$tenantContracts->tenant_contract_note}} </span></h5>
            </div>
        </div>
        @endif
    </div>
</div>
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">    
    <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($tenantContracts->tenant_contract_deposit_amt)?numberFormat($tenantContracts->tenant_contract_deposit_amt)." OMR":"NA"}}</span></h5>
            </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($tenantContracts->tenant_contract_guarantee_cheque_details)?$tenantContracts->tenant_contract_guarantee_cheque_details:"NA"}} </span></h5>
            </div>
        </div>
    </div>
</div>
 @if(!empty($tenantContracts->tenantDocument)) 
<div class="sub-head">Document Upload </div>
<div class="dataSearchBox">    
    <div class="card-body row">
		 @foreach ($tenantContracts->tenantDocument  as $doc) 
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
                <h5 class="details"><b>Document :  </b><span>
                   <a target="_blank" href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}"> {{$doc->tenant_documents_name}} </a>
                </span></h5>
            </div>
        </div>
         <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
    
                  <a href="{{ route('tenantContractDownload',[$doc->id,'tenantContract'])}}">
                       <h5 class="details"><b>  Download  </b><span>
                  </a>
                 </span></h5>
            </div>
        </div>
         @endforeach    

    </div>
</div>
   @endif

   </div>
</div> 
</div>
</div>
@endif  

@if(count($revokeNote)>0)
<div class="card card-box salesLeadBox">
  <div class="card-head">
     <div class="col"><h4>Revoke Note</h4></div>
 </div>
 <div class="card-body">
     <div class="col">
        <div class="row">

            <div class="col leadInformation">
               <div class="table-responsive1">
                <table class="table">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Stage</th>
                            <th>Note</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>

                     @forelse ($revokeNote as $note)

                     @if($note->tenant_contract_revoke_note_desc!="")
                     <tr>
                        <td>Revoke </td>
                        <td>{{$note->tenant_contract_revoke_note_desc}}</td>
                        <td>{{$note->CreatedUser->username??''}} </td>
                        <td>{{$note->created_at->format('d/m/Y')}} </td>
                    </tr> 
                    @endif                       
                    @empty
                    <tr>
                        <td colspan="4" align="center">
                            No Record
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
</div>

</div>
</div>     
@endif
<div class="card card-box salesLeadBox">
		<div class="card-head">
			<div class="col"><h4>Note</h4></div>
		</div>
		 <div class="card-body">
		 <div class="col">
                <div class="row">
          
                    <div class="col leadInformation">
                         <div class="table-responsive1">
                            <table class="table">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Stage</th>
                                        <th>Note</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
								
                                   @forelse ($allNotes as $note)
                                 
                                   @if($note->sales_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->sales_notes}}</td>
                                        <td>{{$note->createdBy->username}} </td>
                                        <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
                                    </tr> 
                                    @endif                       
                                    @empty
                                    <tr>
                                        <td colspan="4" align="center">
                                        No Record
                                       </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
       
		</div>
	</div>

    </div> 
                   
</div>

</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>        <!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
<div class="modal" id="myModal1">
    
</div>
@endsection
@section('scripts')
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script> 
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    $("#sales_note_modal").validate();
    @if(count($salesNotes)>0)
    $('#sale_note_datatable').DataTable({
        "bPaginate": false,
        "bInfo" : false,
        "ordering": false
    });
    @endif
     jQuery('.confirmation').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        var action_key = $(this).attr('data-id');
        var workflow_id = $("#workflow_id").val();
        var enquiryid = $("#enquiryid").val();
       // if (confirm('Please confirm contract details before moving to approval?')) {
            /*jQuery("#sales_approve-form").attr('action', action);
            jQuery("#sales_approve-form").submit();*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal1").html(response); 
                },
            });
        /*} else {
            return false;
        }*/
    });

   
    jQuery('.delete_type').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Close this Activity?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
    });
     

    $('.viewDocumentation').on('click', function(e) {        

        var tenant_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: "{{ url('/') }}/tenantContract/"+tenant_id+"", // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
	///****************Accept Direct Contract *************/

    $(document).on('click','.accept_direct', function(e) { 
       

        var action_key = $(this).attr('data-id');
        var enquiryid = $(this).attr('data-enquiryid');
        var tenant_contract_id = $(this).attr('data-tenant_contract_id');
        var sales_lead_note_name = 'Contract Rejected being Unit Occupied';
        var occuipied_units = $("#occuipied_units").val();
        /*if (confirm('Do you want to Accept this Enquiry?')) {*/
         //   alert(occuipied_units);
        if(occuipied_units > 0){
           alert("Created Contract Unit Already Occupied..!Contract Rejected !");

            $.ajax({
            method: 'POST', 
            url: "{{route('directContractApprovalAcceptReject')}}",
            data: {'action_key' : 'RJCT','tenant_contract_id' : tenant_contract_id,'enquiryid' : enquiryid,'sales_lead_note_name' : sales_lead_note_name,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                  location.reload();
            },
        });
         //  return false;
        }else{
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('noteDirectModal')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
        }
        
    });
	///****************Accept Direct Contract *************/
    $('.editDocumentation').on('click', function(e) {        

        var tenantContract_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: "{{ url('/') }}/tenantContract/"+tenantContract_id+"/edit", // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    $('.accept').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var tenant_contract_id = $("#tenant_contract_id").val();
      
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
             @if(in_array($tenantContracts->tenant_contract_is_revoke , [1,2]))
                url: "{{route('noteModal')}}", 
            @else 
                url: "{{route('noteDirectModal')}}", 
            @endif
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
       
        
         
    });
    $('.reject').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var tenant_contract_id = $("#tenant_contract_id").val();
        
        /*if (confirm('Do you want to Reject this Enquiry?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            @if(in_array($tenantContracts->tenant_contract_is_revoke , [1,2]))
                url: "{{route('noteModal')}}", 
            @else 
                url: "{{route('noteDirectModal')}}", 
            @endif
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'tenant_contract_id' : tenant_contract_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal1").html(response); 
            },
        });
        return true;
        /*}else {
            return false;
        }*/
        
         
    });
   //Flow start
   $('.accept_flow').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Accept this Enquiry?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        /*}else {
            return false;
        }*/
        
         
    });


 $('.closed_flow').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else {
            return false;
        }*/
        
         
    });
   
   //End flow
    $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    });
    $("#myModal1").on("hidden.bs.modal", function(){
        $("#myModal1").html("");
        $(this).removeData('bs.modal');
    });
    
    $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});
});
	

/****************Accept Direct Contract *************/
</script>
@endsection
