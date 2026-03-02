@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">View Tenant Renewal Due</div>
    </div>
    {{ Breadcrumbs::render('tenantRenewal.show',$tenantContract) }}
  </div>
</div>
<div class="row">
	<div class="col">
		 <div class="card card-box salesSearchBox">
			 <div class="row">
				<div class="col-sm-6">
       			 <h4>	
				 {{$tenantContract->tenant_contract_no}}	
				</h4>
				</div>
			 @if(url()->current() ==  route('tenantContract.underRenewalView',$tenantContract->id)) 
               
               <div class="col-sm-6">               	 

			  @can('under_renewal_normal_renewal')
			  <button data-toggle="modal" data-target="#myModal"  href="" class="btn btn-circle btn-warning align-right normal_renewal"  data-tenant_contract_id="{{$tenantContract->id}}" data-backdrop="static" data-keyboard="false" id="normal"  data_type_of_the_contract="normal"  title="Normal Renewal">
			<!--   <a data-toggle="modal" data-target="#myModal"  href="{{route('tenantRenewalStage',[$tenantContract->id,301,1,0])}}" class="btn btn-circle btn-warning align-right" title="Normal Renewal"> -->
				Normal Renewal
			  </button>
			  @endcan		 


			  @can('under_renewal_send_for_approval')
				<button  href="" data-toggle="modal" data-target="#myModal"   id="send_for_approval" data-backdrop="static" data-keyboard="false" data_type_of_the_contract="send_for_approval"  data-tenant_contract_id="{{$tenantContract->id}}" class="btn btn-circle btn-primary align-right  send_for_approval" title="Send for Approval">
					Send for Approval
				</button>
			  @endcan	

             </div> 
			@else

			<div class="col-sm-6">
                 @can('renewal_due_accept')

				  	@if(count($arNotes) < 0)
					  <button  data-toggle="modal" data-target="#myModalNote" data-backdrop="static" data-keyboard="false"  href="{{route('tenantRenewalStageDue',[$tenantContract->id,304,2,0])}}" class="btn btn-circle btn-primary align-right due-for-renewal" title="Accept">
						Renewing
					  </button>
					@elseif(count($arNotes) > 0)
					   <button  disabled onclick="alert('Waiting for the Approval')"  class="btn btn-circle btn-primary align-right" title="Waiting for the Approval...">
						Renewing
					  </button>
					 @else
					 <button  data-toggle="modal" data-target="#myModalNote" data-backdrop="static" data-keyboard="false"  href="{{route('tenantRenewalStageDue',[$tenantContract->id,304,2,0])}}" class="btn btn-circle btn-primary align-right due-for-renewal" title="Accept">
							Renewing
					</button>


				    @endif

				    
					    

				  @endcan
				  <button data-toggle="modal" data-target="#myModalNote" data-backdrop="static" data-keyboard="false" href="{{route('tenantRenewalStageDue',[$tenantContract->id,501,7,0])}}" title="Terminate" class="btn btn-circle btn-danger align-right  due-for-renewal">
					Vacating
				  </button> 
				</div>

            @endif 


			</div>



 <div class="dataSearchBox">   

<!----    Discussion Form                        ------ -->
@if(count($tenantContract->discussion) > 0)
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-yellow">
            	<div class="ribbon"><span>Discussion</span></div>
               Renewal/ Vacating Discussion Forum </header>
            <div class="panel-body light-green">
                <table class="table display product-overview mb-30">
                <thead class="background-red">
                    <tr>                       
                        <th> Discussion Type</th>
                        <th>Comment</th>
                        <th>Comment By </th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody class="background-white">
                    @foreach($tenantContract->discussion  as $discussion)
                     <tr>                       
                        <td>{{$discussion->category->category}}</td>
                        <td>{{$discussion->discussion}}</td>
                        <td>{{($discussion->user->user_type == 'admin') ? ucwords($discussion->user->username) : ucwords($discussion->user->employee->employee_name) }}</td>
                        <td>{{$discussion->created_at->format('d/m/Y')}}</td>              
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

 
<!---- End    Discussion Form                        ------ -->
@if(count($arNotes) > 0)
<div class="row">
<!--------     -->
 <div class="col-lg-12  ">
    <div class="card-body">      
     <h4><strong>Suggested Contract Type </strong><div class="clr"></div></h4>
       <div class="table-responsive1" style="background: #f7f7f7;">
                <table class="table" id="note_datatable">
                  <thead>
                    <tr >
                      <th>Contract Type</th>
                      <th>Comment</th>
                      <th>Date & Time</th>
                      <th>Commented By</th>
                    </tr>
                  </thead>
                  <tbody>
                   @foreach ($arNotes as $arNote)
                   <tr>
                   <td>{{$arNote->renewalType->type}} </td>
                   <td>{{$arNote->renewal_notes_note}} </td>
                   <td class="d-t">{{$arNote->created_at->format('d/m/Y h:m A')}}</td>
                   <td>{{(!empty($arNote->createdBy->employee->employee_name))? $arNote->createdBy->employee->employee_name : $arNote->createdBy->username}} </td>
                 </tr>
                @endforeach
                </tbody>
            </table>  
    </div>
  </div>
</div> 
</div>
@endif



			 
				<div class="card-body row">
					@if($tenantContract->building_id)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
					</div>
					</div> 
					@endif
					@if($tenantContract->building_id)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
					</div>
					</div> 
					@endif
					@if($tenantContract->unit_id)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
					</div>
					</div>
					@endif
					@if($tenantContract->unit_id)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
					</div>
					</div>
					@endif
					@if($tenantContract->tenant->tenant_name)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name}}</span></h5>
					</div>
					</div> 
					@endif
					@if($tenantContract->tenant->tenant_code)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
					</div>
					</div>
					@endif
					@if($tenantContract->unit_usage)
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
					</div>
					</div>
					@endif
					@if(isset($tenantContract->occupant_id))
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Occupant Name :  </b><span>{{$tenantContract->occupant->occupant_name}}</span></h5>
					</div>
					</div> 
					@endif
					@if(isset($tenantContract->occupant->occupant_primary_contact_no))
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Occupant Mob No:  </b><span>{{$tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
					</div>
					</div>
					@endif

					@if(isset($tenantContract->occupant->occupant_email))
					<div class="col-lg-6 p-t-20"> 
					<div class = "txt-full-width">
					<h5 class="details"><b>Occupant Email:  </b><span>{{$tenantContract->occupant->occupant_email ?? "NA"}}</span></h5>
					</div>
					</div>
					@endif
				</div>
			</div>
			<!-- Status Ribbon Starts -->
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-blue">
                <div class="ribbon"><span>Status</span></div>
               Status </header>
            <div class="panel-body light-green">
            <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->tenant_status_name}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContract->tenant_contract_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->unit->vacant_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
                    @if(!empty($tenantContract->unit->key)){{$tenantContract->unit->key->status_name}}
                    @else
                    NA
                    @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remaining days to Expiry :  </b><span>{{$remainingDays}} Days</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent paid Up To:  </b><span>
                     @if(!empty($tenantContract->tenant_contract_last_paid_date))
                     {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
                     @else
                     NA
                     @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> </b><span></span></h5>
                </div>
            </div>
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->
			<div class="sub-head">Contract Details </div>
			<div class="dataSearchBox">    
			<div class="card-body row">
				@if($tenantContract->tenant_contract_start_date)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
				 </div>
				</div>
				@endif
				@if($tenantContract->tenant_contract_effective_date)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
				 </div>
				</div>
				@endif
				@if($tenantContract->tenant_contract_valid_to_date)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Valid To :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
				 </div>
				</div>
				@endif
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Duration  :  </b>
					 <span>
					  @php
					  $duration = $tenantContract->tenant_contract_duration_countdown;
					  $count = explode('-',$duration);
					  @endphp
					  @if(!empty($duration))
					  {{$count[0]}} Year {{$count[1]}}Month {{$count[2]}}Day
					  @endif
					</span></h5>
				  </div>
				</div>
				@if(isset($tenantContract->tenant_contract_rent) && isset($tenantContract->tenant_contract_duration))
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Contract Value :  </b><span>{{numberFormat($tenantContract->tenant_contract_value) }} OMR</span></h5>
				 </div>
				</div>
				@endif
				@if($tenantContract->tenant_contract_rent)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Rent (PM) :  </b><span>{{ numberFormat($tenantContract->tenant_contract_rent)}} OMR</span></h5>
				 </div>
				</div>
				@endif
	
			@if(isset($tenantContract->tenant_contract_muncipality_agr_no))
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
				 </div>
				</div>
				@endif
				@if(isset($tenantContract->tenant_contract_electric_water))
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Deposit Electric/Water :  </b><span>{{$tenantContract->tenant_contract_electric_water}}</span></h5>
				 </div>
				</div>
				@endif
				@if($tenantContract->tenant_contract_registered_in)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
				 </div>
				</div>
				@endif
				@if($tenantContract->tenant_contract_registered_date)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Contract Registered Date :  </b><span>{{$tenantContract->tenant_contract_registered_date->format('d/m/Y')}}</span></h5>
				 </div>
				</div>
				@endif
				@if(isset($tenantContract->tenant_contract_muncipality_agr_no))
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
				 </div>
				</div>
				@endif
				@if(isset($tenantContract->tenant_contract_electric_water))
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Deposit Electric/Water :  </b><span>{{$tenantContract->tenant_contract_electric_water}}</span></h5>
				 </div>
				</div>
				@endif
				
		</div>
		</div>   	
		<div class="sub-head">Payment Details </div>
			<div class="dataSearchBox">    
			<div class="card-body row">
	
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt). ' OMR' : ''}}</span></h5>
				 </div>
				</div>
		
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?$tenantContract->tenant_contract_guarantee_cheque_details:''}}</span></h5>
				 </div>
				</div>

	
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Receipt No :  </b><span>{{isset($tenantContract->tenant_contract_receipt_no)?$tenantContract->tenant_contract_receipt_no:''}}</span></h5>
				 </div>
				</div>
			
				@if($tenantContract->tenant_contract_receipt_date)
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Receipt Date :  </b><span>{{$tenantContract->tenant_contract_receipt_date->format('d/m/Y')}}</span></h5>
				 </div>
				</div>
				@endif
				
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Receipt Amount :  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?numberFormat($tenantContract->tenant_contract_receipt_amt).'OMR':''}}</span></h5>
				 </div>
				</div>
							
				<div class="col-lg-6 p-t-20"> 
				  <div class = "txt-full-width">
				   <h5 class="details"><b>Last Rent Paid :  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?numberFormat($tenantContract->tenant_contract_receipt_amt). 'OMR':'' }} </span></h5>
				 </div>
				</div>
		
   

			</div>






		</div>
		</div>
@if(!empty($termination))
<div class="sub-head">Termination Refer Back Note </div>
<div class="dataSearchBox">    
<div class="card-body row">

<div class="col-lg-12 p-t-20">
<div class = "txt-full-width">
<h5 class="details"><b>Note :  </b><span>{{isset($termination)? $termination->termination_notes : ''}}</span></h5>
</div>
</div>
</div>
</div>
@endif	



<div class="row">
    <div class="col-sm-12">
        <div class="card card-box salesLeadBox">
        <div class="card-head">
            <div class="col"><h4>Renewal Stage Note</h4></div>
        </div>
         <div class="card-body">
         <div class="col">
                <div class="row">
          
                    <div class="col leadInformation">
                         <div class="table-responsive1">
                            <table class="table" >
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Stage</th>
                                        <th>Note</th>
                                        <th>Commented By</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>

                                  @forelse ($stageNotes as $note)
                                 
                                   @if($note->renewal_notes!="")
                                    <tr>
                                        <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                                        <td>{{$note->renewal_notes}}</td>
                                        <td>{{($note->updated_by > 0)? ( (!empty($note->updatedByUser->employee->employee_name))? $note->updatedByUser->employee->employee_name :   $note->updatedByUser->username ) : ((!empty($note->createdBy->employee->employee_name))? $note->createdBy->employee->employee_name :   $note->createdBy->username )}} 

                                        {{--(!empty($note->updated_by))? $note->updatedByUser :$note->createdBy->username --}}

                                        </td>
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
            </div>
       
        </div>
    </div>
    </div>
</div>


</div>
<div class="modal" id="myModal">

</div>


<div class="modal" id="myModalNote">
<div class="modal-dialog assign">
    <div class="modal-content">  
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title"> Note</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>    
    <!-- Modal body -->
    <div class="modal-body">
    
   <div class="row">
    <div class="col">
        <div class="card card-box salesSearchBox">
        <form action="" autocomplete="off" method="POST" id="due_renewal_note_modal" class="form-horizontal"  data-toggle="validator">
        {{csrf_field()}}        
        <div class="dataSearchBox ">            
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormCode">Note <small class="textRed">*</small></label>
                        <div class="p-relative">
                          <i class="fa fa-file-text-o icn-add" aria-hidden="true"></i>
                          <textarea required class="form-control"  id="renewal_notes"  placeholder="Enter Note" name="renewal_notes"   data-rule-maxlength="200" data-msg-maxlength="Only allows 200 Characters"></textarea>
                        </div>
                    </div>
                  </div>                  
               <div class="w-100"></div>
                <div class="col">
                   <div class="w-100"></div>
                      <button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button>                      
                   </div>                   
              </div>        
        </div>
        <div class="col-sm-12 text-right">
        
    </div>
        <div class="clearfix"></div>
        </form>
            
        </div>
    </div>
</div> 

    </div>   
  

    </div>
</div>
</div>

@endsection
@section('scripts')
<script> 

$(document).ready(function(){

 
var type_of_the_contract = 'normal'
 $(document).on('click','.normal_renewal,.send_for_approval', function(e){

 	$("#myModal").html('');
        
         type_of_the_contract =  $(this).attr('data_type_of_the_contract');        
   
        $.ajax({
            method: 'POST',  
            url: "{{route('tenantContractRenewalType',[$tenantContract->id])}}",  
            data: {'type':type_of_the_contract,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){  
                $("#myModal").html(response);
                 
           },
       });
    }) 

 $(document).on('click','.due-for-renewal', function(e){

 	    e.preventDefault();
        var href = $(this).attr('href');
            
        $('#due_renewal_note_modal').attr('action',href);
       // alert($('#due_renewal_note_modal').attr('action'))
      
    })

    });
</script>
@endsection