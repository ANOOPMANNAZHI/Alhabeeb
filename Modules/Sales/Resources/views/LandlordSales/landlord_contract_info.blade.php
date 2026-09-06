@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Contract Detail</div>
        </div>
         {{Breadcrumbs::render('landlordContract.show',$id)}}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
		
        <div class="col">
			 	@can('move_to_approval_key')
				 <form id="sales_firstcall-form" action="{{route('nextStageAction')}}"" method="POST">
					{{csrf_field()}}
					<input type="hidden" name="enquiryid" value="{{$landlordContractInfo->sale_enquiry_id}}">
				  
					<input type="hidden" name="action_key" value="MVN">
					<input type="hidden" name="workflow_id" value="{{$sales_enquiry->work_flow_processes_code}}">
				   
					@if($sales_enquiry->work_flow_processes_code == 202)
					<button type="submit" class="btn btn-circle btn-primary align-right"> Move To Approval</button>
					@endif
				</form>
				@endcan
        </div>
    </div>
    <div class="card-body">
        
        <div class="col-sm-6 p-0">
            <dl>
               
                <dt>Enquiry No</dt>
                <dd>{{$sales_enquiry->sales_enquiry_no?? 'NA'}}</dd>
                <dd>Enquiry Date</dd>
                <dd>{{$sales_enquiry->created_at->format('d/m/Y')?? 'NA'}}</dd>
                <dt>Enquiry Owner</dt>
                <dd>{{$sales_enquiry->sales_enquiry_name?? 'NA'}}</dd>
                <dt>Email</dt>
                <dd>{{$sales_enquiry->sales_email?? 'NA'}}</dd>
                <dt>Phone</dt>
                <dd>{{$sales_enquiry->sales_mobile_no?? 'NA'}}</dd>
                <dd>Call Center Executive</dd>
                <dd>{{$sales_enquiry->employee->employee_name?? 'NA'}}</dd>
               <!--<dt>Enquiry Status</dt>
                <dd>{{$sales_enquiry->workFlowProcess->work_flow_processes_name?? 'NA'}}</dd> -->
     
            </dl> 
        </div>
        <div class="clearfix"></div>
        @if($sales_enquiry->work_flow_process_code==202)
        <div class="col nxtaction mt-4">
            <h6>Next Action</h6>
            <div id="flag">{{date('M d')}}</div> <h5><strong>Call {{$sales_enquiry->sales_enquiry_name}}</strong></h5>
        </div>
        @endif
       <div class="w-100 mt-4"></div> 
        <!-- <a  class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
			<span id="enquiry_info">Show Enquiry Details</span>
		</a> -->
        <div class="clearfix"></div>
         <div id="show" class="">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3> 
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            
                            <li class="bld">Sales Executive</li>
                            <li>{{$sales_enquiry->salesEnquiryUser->employee->employee_name}}</li>
                            <li class="bld">Enquiry No</li>
                            <li>{{$sales_enquiry->sales_enquiry_no}}2</li>
                            <li class="bld">Referred By</li>
                            <li>{{$sales_enquiry->sales_referred_by?? 'NA'}}</li>
                            <li class="bld">Remark</li><li>
                                {{isset($landlordContractInfo->salesEnquiry->sales_note)?$landlordContractInfo->salesEnquiry->sales_note:'NA'}}</li>
                            <li class="bld">Mode</li>
                            <li>@if($sales_enquiry->sales_mode_id){{$sales_enquiry->enquirySource->enquiry_sources_name}}@else NA @endif</li>
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                         
                            <li class="bld">Status</li>
                            <li>{{$sales_enquiry->workFlowProcess->work_flow_processes_name?? 'NA'}}</li>
                            <li class="bld">Enquiry Date</li>
                            <li>{{$sales_enquiry->created_at->format('d/m/Y')?? 'NA'}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{($sales_enquiry->sales_company_name)?$sales_enquiry->sales_company_name:'NA'}}</li>
                            <li class="bld">Square Metre</li>
                            <li>{{$sales_enquiry->sales_size?? 'NA'}}</li>
                            <!--<li class="bld">Over Due Date</li><li>lorem ipsum</li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    
    
    </div> 
                   
</div>
        
        
        
        @if($sales_enquiry->work_flow_process_code >= 102)
        <div class="add-note-section">
            <form id="sales_note-form" action="{{route( 'leadAssign.storeSalesNote')}}"" method="POST">
            {{csrf_field()}}
              <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Notes</label>
                    <textarea class="form-control" rows="2" required name="sales_notes" placeholder="Enter Description"></textarea>
                    <input type="hidden" name="sales_id" value="{{$details->id}}">
                </div>
            </div>
            <div class="col-sm-2"><button type="submit" class="btn btn-primary">SAVE</button></div>
            </form>
            <div class="col-md-12">
                <div class="col p-0">
            <h4><strong></strong><div class="clr"></div></h4>
           
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Progress</th>
                            <th>Date & time</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse ($salesNotes as $salesNote)
                        <tr>
                            <td>{{$salesNote->sales_notes_note}} </td>
                            <td class="d-t">{{$salesNote->created_at->format('d/m/Y h:m A')}}</td>
                           
                        </tr>                        
                        @empty
                        <tr>
                            <td colspan="2" align="center">
                            <p>No records</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
            </div>
        </div>
        <div class="w-100 mt-5 mb-5"></div>
         
      @endif
    @if($sales_enquiry->work_flow_process_code ==103)
    <div class="col p-0 brdr">
        <h4><strong>Documentation</strong></h4>
        
        <form id="sales_firstcall-form" action="{{route( 'documentationStage')}}"" method="POST">
            {{csrf_field()}}
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
            <input type="hidden" name="sales_id" value="{{$details->id}}">
            <input type="hidden" name="action_key" value="DM">
            <input type="hidden" name="workflow_id" value="103">
           
            <button type="submit" class="btn btn-circle btn-primary align-right">Documentation</button>
            
        </form>
    </div>
    @endif
    
    </div> 
                   
</div>
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col"><h4> Contract Details</h4></div>
    </div>
    <div class="card-body row">
        @if($landlordContractInfo->landlord_contract_no)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Agreement No :  </b><span>{{$landlordContractInfo->landlord_contract_no}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->created_at)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Agreement Date  :  </b><span>{{$landlordContractInfo->created_at->format('d/m/Y')}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->building_id)
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Building Name :  </b><span>{{$landlordContractInfo->buildingInfo->building_name}}</span></h5>
            </div>
          </div> 
        @endif
        @if($landlordContractInfo->vendor_id)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Landlord Name :  </b><span>{{$landlordContractInfo->vendorInfo->vendor_name}}</span></h5>
            </div>
        </div>
        @endif
        
        @if($landlordContractInfo->landlord_contract_duration)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Duration Type :  </b>
               <span>
               @if($landlordContractInfo->landlord_duration == 1)
						Open
				@else
						Perpetual
				@endif
               </span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->management_id)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Management Type :  </b><span>{{$landlordContractInfo->managementTypeInfo->management_types_name}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_management_fee)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Management Fee :  </b>
                <span>
               @if($landlordContractInfo->management_method==1)
					Percentage
				@elseif($landlordContractInfo->management_method==2)
					Amount
				@endif
               </span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_amt)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Contract Amount :  </b><span>{{$landlordContractInfo->landlord_contract_amt}} OMR</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_payment_type)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Payment Term :  </b><span>{{$landlordContractInfo->paymentMethodInfo->payment_method_code}}</span></h5>
            </div>
        </div>
        @endif
         @if($landlordContractInfo->management_fee_type)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Management Fees Type :  </b>
               <span>
                @if($landlordContractInfo->management_fee_type==1)
					Management Fees
				@elseif($landlordContractInfo->management_fee_type==2)
					Maintenance
				@elseif($landlordContractInfo->management_fee_type==3)
					Legal
				@elseif($landlordContractInfo->management_fee_type==4)
					Caretakers Fee
				@endif
               </span></h5>
            </div>
        </div>
        @endif
         @if($landlordContractInfo->management_fee_type)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Percentage Type:  </b>
               <span>
               @if($landlordContractInfo->landlord_contract_percentage==1)
					Rent Income
				@elseif($landlordContractInfo->landlord_contract_percentage==2)
					Rent Collection
				@else
				  NA
				@endif
               </span></h5>
            </div>
        </div>
        @endif
         @if($landlordContractInfo->landlord_contract_management_fee)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>{{($landlordContractInfo->management_method==1)?'Management Value':'Management Amount' }} :  </b>
               <span>
               {{$landlordContractInfo->landlord_contract_management_fee??'0'}} OMR
               </span></h5>
            </div>
        </div>
        @endif
         @if($landlordContractInfo->landlord_contract_cleaning_charge)
        <div class="col-lg-6 p-t-20">
            <div class = "txt-full-width">
               <h5 class="details"><b>{{($landlordContractInfo->cleaning_charge_method==1)?'Cleaning Value':'Cleaning Amount' }} :  </b>
               <span>
               {{ ($landlordContractInfo->cleaning_charge_method==1) ? $landlordContractInfo->landlord_contract_cleaning_charge.' %' : numberFormat($landlordContractInfo->landlord_contract_cleaning_charge).' OMR' }}
               </span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_valid_from_date)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Valid From :  </b><span>{{$landlordContractInfo->landlord_contract_valid_from_date->format('d/m/Y')}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_valid_to_date)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Valid To :  </b><span>{{$landlordContractInfo->landlord_contract_valid_to_date->format('d/m/Y')}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_free_lease_period)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Free Lease Period :  </b><span>{{$landlordContractInfo->landlord_free_lease_period}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_agreement_amt)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Agreement Amount :  </b><span>{{$landlordContractInfo->landlord_contract_agreement_amt}} OMR</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_contract_address)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Contract Address :  </b><span>{{$landlordContractInfo->landlord_contract_address}}</span></h5>
            </div>
        </div>
        @endif
        @if($landlordContractInfo->landlord_marketing_executive)
        <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
               <h5 class="details"><b>Marketing Executive :  </b><span>{{$landlordContractInfo->marketExecutiveEmployeeInfo->employee_name}}</span></h5>
            </div>
        </div>
        @endif
               
               
    </div>
</div>
@if(count($allNotes))
      <div class="card card-box salesLeadBox">
		  <div class="card-head">
			<div class="col"><h4>Enquiry Stage Note</h4></div>
		  </div>
    
        <div class="card-body ">
                         <div class="table-responsive1">
                            <table class="table" id="note_datatable">
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
                                        <td>{{$note->createdBy->employee->employee_name ??$note->createdBy->username }} </td>
                                        <td>{{$note->created_at->format('d/m/Y h:i:s')}} </td>
                                    </tr> 
                                    @endif                       
                                    @empty
                                    <tr>
                                        <td colspan="4" align="center">
                                        <p>No Record</p>
                                       </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                
		 </div>
     <!-- Enquiry Stage Note ends -->
   </div>
   @endif
</div>
</div>
</div>
</div>
        <!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    $("#form_sample_2").validate();
    

    $('.addActivity').on('click', function(e) {        

        var sales_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../salesActivities/activities/'+sales_id+'', // This is the url we gave in the route
            //data: {'id' : sales_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    $('.editActivity').on('click', function(e) {        

        var activity_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../salesActivities/'+activity_id+'/edit', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
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
    $('.viewActivity').on('click', function(e) {        

        var activity_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../salesActivities/'+activity_id+'', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    }); 

    $('.add_documentation').on('click', function(e) {        

        var sales_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../tenant/documentation/'+sales_id+'', // This is the url we gave in the route
            //data: {'id' : sales_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    $('.editDocumentation').on('click', function(e) {        

        var tenant_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../tenant/'+tenant_id+'/edit', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    $('.viewDocumentation').on('click', function(e) {        

        var tenant_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../tenant/'+tenant_id+'', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    }); 
    
    $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});
    
});
</script>
@endsection
