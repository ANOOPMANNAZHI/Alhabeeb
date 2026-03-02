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
            <div class="page-title">Landlord @if($stage == 205)Loss @else 
            @endif Enquiry</div>
        </div>
        @if($stage==205)
			{{Breadcrumbs::render('landlordLeadAssign.nextStage',$details->salesEnquiry->id,$stage)}}
		@endif
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col"><h4>{{$details->salesEnquiry->sales_enquiry_name}}</h4></div>
    </div>

    <div class="card-body">
         <form id="sales_firstcall-form" action="{{route('nextStageAction')}}"" method="POST">
            {{csrf_field()}}
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
            <input type="hidden" name="sales_id" value="{{$details->id}}">
            <input type="hidden" name="action_key" value="ACPT">
            <input type="hidden" name="workflow_id" value="201">
            @if($details->salesEnquiry->work_flow_processes_code == 201)
           <!--  <button type="submit" class="btn btn-circle btn-primary align-right">Accept</button> -->
            @can('close_landlord_enquiry')
            <button type="button" class="btn btn-circle btn-primary align-right closed" data-toggle="modal" data-target="#myModal" data-id = "CL" data-backdrop="static" data-keyboard="false">Close</button>

            <button type="button" class="btn btn-circle btn-primary align-right accepted" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-backdrop="static" data-keyboard="false">Accept</button>


            <input type="hidden" name="enquiryid" id="enquiryid" value="{{$details->salesEnquiry->id}}">
            <input type="hidden" name="sales_id" id="sales_id" value="{{$details->id}}">
            <input type="hidden" name="workflow_id" id="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}">
            @endcan
            @endif
            
        </form>
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry No</dt>
                <dd>{{$details->salesEnquiry->sales_enquiry_no??'NA'}}</dd>
                <dt>Customer Name</dt>
                <dd>{{$details->salesEnquiry->sales_enquiry_name??'NA'}}</dd>
                 @if(!empty($details->salesEnquiry->sales_email ))
                <dt>Email</dt>
                <dd>{{$details->salesEnquiry->sales_email??'NA'}}</dd>
                @endif
                <dt>Phone</dt>
                <dd>{{$details->salesEnquiry->sales_mobile_no??'NA'}}</dd>
                <!--<dt>Enquiry Status</dt>
                <dd>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name??'NA'}}</dd> -->
            </dl> 
        </div>
        <div class="clearfix"></div>
        @if($details->salesEnquiry->work_flow_process_code==102)
        <div class="col nxtaction mt-4">
            <h6>Next Action</h6>
            <div id="flag">{{date('M d')}}</div> <h5><strong>Call {{$details->salesEnquiry->sales_enquiry_name}}</strong></h5>
        </div>
        @endif
        <div class="w-100 mt-4"></div>
       <!--  <a  class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
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
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{($details->salesEnquiry->sales_referred_by)? $details->salesEnquiry->sales_referred_by:'-'}}</li>
                            <li class="bld">Price Range</li>
                            <li>@if($details->salesEnquiry->priceRanges->count()>0){{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')}}@else {{'NA'}} @endif</li>
                            <li class="bld">Building Type</li>
                            <li>{{$details->salesEnquiry->buildingType->building_types_name ??  'NA'}}</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note ??  'NA'}}</li>
                            @if($details->salesEnquiry->sales_mode_id)
                            <li class="bld">Mode</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name ??  'NA'}}</li>
                            @endif
                            <li class="bld"> Predefined Areas</li>
                            <li>@if($details->salesEnquiry->locations->count()>0){{$details->salesEnquiry->locations->implode('locations_name',', ')}}@else {{'NA'}}@endif</li> 
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>                            
                           
                            <li class="bld">Enquiry Date</li>
                            <li>{{$details->salesEnquiry->created_at->format('d/m/Y') ??  'NA'}}</li> 
                             <li class="bld">Status</li>
                            <li>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name ??  'NA'}}</li>     
                            <li class="bld">Customer Address </li>
                            <li>{{$details->salesEnquiry->sales_contact_address ??  'NA'}}</li>                        
                            <li class="bld">Square Metre</li>
                            <li>{{$details->salesEnquiry->sales_size ??  'NA'}}</li>                            
                            <li class="bld">No. Of Units</li>
                            <li>{{$details->salesEnquiry->sales_no_of_unit ??  'NA'}}</li>                            
                                                       
                                                      
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
       </div>
</div>
<!-- NOte STARTS-->
 <div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Note</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Note</th>
              <th>User</th>
              <th>Stage</th>
              <th>Date Time</th>
            </tr>
          </thead>
          <tbody> 
          @forelse ($salesNoteList as $note)

            @if($note->sales_notes!="") 
            <tr>
              <td>{{$note->sales_notes}}</td>
              <td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}}</td>
              <td>{{$note->workFlowProcess->work_flow_processes_name}}</td>
              <td>{{$note->created_at->format('d/m/Y h:m A')}}</td>
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
<!-- nOTE ENDS-->

<!-- ends-->   
@if($landlordContractInfo)        
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
        @if($landlordContractInfo->management_id==2 || $landlordContractInfo->management_id==3 )
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
@endif
        @if($details->salesEnquiry->work_flow_process_code >= 103)
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
        
        <div class="col p-0">
            <h4><strong>Open Activities</strong> 
                <button type="submit" class="btn btn-primary align-right addActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-plus"></i> Add 
                </button>

            <div class="clr"></div></h4>
           
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Subject</th>
                            <th>Activity Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesActivities as $salesActivity)
                        <tr>
                            <td>{{$salesActivity->sales_activities_name}}</td>
                            <td>{{$salesActivity->sales_activity_type}}</td>
                            <td>{{$salesActivity->sales_activities_status}}</td>
                            <td>{{$salesActivity->sales_activities_due_date->format('M d Y')}}</td>
                            <td>{{$salesActivity->sales_activities_note}}</td>
                            <td>
                                <button type="submit" class="btn btn-tbl-edit btn-xs editActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$salesActivity->id}}" data-backdrop="static" data-keyboard="false">
                                    <i class="fa fa-pencil-square-o"></i> 
                                </button>
                                <a href="{{route('salesActivities.destroy',$salesActivity->id)}}" title="Close" class="btn btn-tbl-delete btn-xs delete_type">
                                    <i class="fa fa-pie-chart" aria-hidden="true"></i>
                                </a>                       
                                <!-- <button class="btn btn-tbl-delete btn-xs" title="Close">
                                   <i class="fa fa-pie-chart" aria-hidden="true"></i>

                                </button> -->
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" align="center">
                            <p>No records</p>
                           </td>
                        </tr>
                        @endforelse                        
                       
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col p-0 brdr">
            <h4><strong>Closed Activities</strong></h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Subject</th>
                            <th>Activity Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesClosedActivities as $salesClosed)
                        <tr>
                            <td>{{$salesClosed->sales_activities_name}}</td>
                            <td>{{$salesClosed->sales_activity_type}}</td>
                            <td>{{$salesClosed->sales_activities_status}}</td>
                            <td>{{$salesClosed->sales_activities_due_date->format('M d Y')}}</td>
                            <td>{{$salesClosed->sales_activities_note}}</td>
                            <td>
                                <button type="submit" class="btn btn-tbl-view btn-xs viewActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$salesClosed->id}}" data-backdrop="static" data-keyboard="false">
                                    <i class="fa fa-eye"></i>
                                </button>
                                
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" align="center">
                            <p>No records</p>
                           </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
      @endif
    @if($details->salesEnquiry->work_flow_process_code ==103)
    <div class="col p-0 brdr">
        <h4><strong>Documentation</strong></h4>
        
        <form id="sales_firstcall-form" action="{{route( 'documentationStage')}}"" method="POST">
            {{csrf_field()}}
            <input type="hidden" name="enquiryid" id="enquiryid" value="{{$details->salesEnquiry->id}}">
            <input type="hidden" name="sales_id" id="sales_id" value="{{$details->id}}">
            <input type="hidden" name="action_key" id="action_key" value="DM">
            <input type="hidden" name="workflow_id"  id="workflow_id" value="103">
           
            <button type="submit" class="btn btn-circle btn-primary align-right">Documentation</button>
            
        </form>
    </div>
    @endif
    
    </div> 
                   
</div>
@if($details->salesEnquiry->work_flow_process_code == 104)
    <div class="card card-box salesSearchBox">
    <h4>Documentation
    <button type="submit" class="btn btn-primary align-right add_documentation" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}" data-backdrop="static" data-keyboard="false">
    <i class="fa fa-plus"></i> NEW 
        </button>
        <div class="clr"></div></h4>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th>Building Name</th>
                    <th>Unit No</th>
                    <th>Duration</th>
                    <th>Attachment</th>
                    <th>Action</th>
                    
                </tr>
            </thead>
            <tbody>
                @forelse ($tenants as $tenant)
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>{{$tenant->tenantContract->tenant_contract_duration}} {{$tenant->tenantContract->tenant_contract_duration_type}}</td>
                    <td>{{$tenant->tenantDocument->tenant_documents_name}}</td>
                    <td>
                        <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenant->id}}" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-pencil-square-o"></i> 
                        </button> 
                        <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenant->id}}" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-eye"></i>
                        </button>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" align="center">
                    <p>No records</p>
                   </td>
                </tr>
                @endforelse
               
            </tbody>
        </table>
    </div>
     <div class="clearfix"></div>
                         
    </div>
    @endif
</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>        <!-- The Modal -->
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    $("#form_sample_2").validate();
    $('.closed').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccept')}}", // This is the url we gave in the route
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
    /*********************************************Accept*****************************************/
    $('.accepted').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccept')}}", // This is the url we gave in the route
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

    
     $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});
    
});
</script>
@endsection
