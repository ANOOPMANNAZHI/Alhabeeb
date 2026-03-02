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
            <div class="page-title">{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</div>
        </div>
        {{ Breadcrumbs::render('leadAssign.nextStage',$details,$details->salesEnquiry->work_flow_processes_code,$routes) }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col"><h4>{{$details->salesEnquiry->sales_enquiry_name}}</h4></div>
    </div>
    <div class="card-body">
        
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry Owner</dt>
                <dd>{{$details->salesEnquiry->enquiryOwner->username??'NA'}}</dd>
                <dt>Email</dt>
                <dd><a href="mailto:{{$details->salesEnquiry->sales_email}}">{{$details->salesEnquiry->sales_email??'NA'}}</a></dd>
                <dt>Phone</dt>
                <dd>@if($details->salesEnquiry->sales_mobile_no)  {{$details->salesEnquiry->sales_mobile_no}} @else NA @endif</dd>
               <!--  <dt>Enquiry Status</dt>
                <dd>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</dd> -->
                
            </dl> 
        </div>
        <div class="clearfix"></div>
       
        <div class="w-100 mt-4"></div>
        <!-- <a  class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
			<span id="enquiry_info">Show Enquiry Detail</span></a> -->
        <div class="clearfix"></div>
       
        <div id="show" class="">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li><li>{{$details->salesEnquiry->enquiryOwner->employee->employee_name ??  $details->salesEnquiry->enquiryOwner->username}}</li>
                            <li class="bld">Enquiry Type</li>
                            <li>@if($details->salesEnquiry->tenant_type_id){{$details->salesEnquiry->tenantType->tenant_types_name}}@else {{'NA'}} @endif</li>
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->salesEnquiry->sales_referred_by??  'NA'}}</li>
                            <li class="bld">Price Range</li>
                            <li>@if($details->salesEnquiry->priceRanges->count()>0){{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')?? 'NA'}}  @else {{'NA'}} @endif </li>
                            <li class="bld">Location</li>
                            <li>@if($details->salesEnquiry->locations->count()>0){{$details->salesEnquiry->locations->implode('locations_name',', ') ?? 'NA'}}@else {{'NA'}}@endif</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note ??  'NA'}}</li>
                            <li class="bld">Alternative No</li><li>{{$details->salesEnquiry->alternative_no ??  'NA'}}</li>
                            
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Unit Type</li>
                            <li>@if($details->salesEnquiry->unitTypes->count()>0){{$details->salesEnquiry->unitTypes->implode('unit_types_name',', ') ?? 'NA'}}@else {{'NA'}} @endif</li>
                            <li class="bld">Status</li>
                            <li>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name ??  'NA'}}</li>
                            <li class="bld">Move In Date</li>
                            <li>{{$details->salesEnquiry->sales_move_in_date->format('m/Y')}}</li>
                            <li class="bld">Enquiry Date</li>
                            <li>{{$details->salesEnquiry->created_at->format('d/m/Y')}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{$details->salesEnquiry->sales_company_name ??  'NA'}}</li>
                            <li class="bld">Square Meter</li>
                            <li>{{$details->salesEnquiry->sales_size ??  'NA'}}</li>
                            <li class="bld">No Of Units</li>
                            <li>{{$details->salesEnquiry->sales_no_of_unit ??  'NA' }}</li>
                            @if($details->salesEnquiry->sales_mode_id)
                            <li class="bld">Source</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name ??  'NA'}}</li>
                            @endif
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card card-box salesLeadBox">
		<div class="card-head">
			<div class="col"><h4>Enquiry Stage Note</h4></div>
		</div>
		 <div class="card-body">
		 <div class="col">
                <div class="row">
          
                    <div class="col leadInformation">
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
                                       <!-- <td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}} </td>-->
									   <td>{{$note->updatedBy->employee->employee_name ?? $note->updatedBy->username}} </td>
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
    <!-- 103 condition -->
    <div class="card card-box salesLeadBox">
    <div class="card-body">
        
        <div class="add-note-section">
            <!-- <form id="sales_note-form" action="{{route( 'leadAssign.storeSalesNote')}}"" method="POST">
            {{csrf_field()}}
              <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Notes</label>
                    <textarea class="form-control" rows="2" required name="sales_notes" placeholder="Enter Description"></textarea>
                    <input type="hidden" name="sales_id" value="{{$details->id}}">
                    <input type="hidden" name="workflowId" value="{{$details->salesEnquiry->work_flow_processes_code}}">
                </div>
            </div>
            <div class="col-sm-2"><button type="submit" class="btn btn-primary">SAVE</button></div>
            </form> -->
            <div class="col-md-12">
                <div class="col p-0">
            <h4><strong>Sales Note</strong><div class="clr"></div></h4>
           
            <div class="table-responsive1">
                <table class="table" id="note_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Progress</th>
                            <th>Date & time</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse ($salesNotes as $salesNote)
                        <tr>
                            <td>{{$salesNote->sales_notes_note}} </td>
                            <td class="d-t">{{$salesNote->created_at->format('d/m/Y h:m A')}}</td>
                            <td>{{$salesNote->createdBy->employee->employee_name ?? $salesNote->createdBy->username}} </td>
                           
                        </tr>                        
                        @empty
                        <tr>
                            <td colspan="3" align="center">
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
        <div class="w-100 mt-5 mb-5"></div>
        
        <div class="col p-0">
          <h4><strong>Open Activity</strong> 
                <!--   <button type="submit" class="btn btn-primary align-right addActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-plus"></i> Add 
                </button>-->

            <div class="clr"></div></h4> 
           
            <div class="table-responsive1">
                <table class="table" id="open_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Subject</th>
                            <th>Activity Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Created By</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesActivities as $salesActivity)
                        <tr>
                            <td>{{$salesActivity->sales_activities_name}}</td>
                            <td>{{$salesActivity->sales_activity_type}}</td>
                            <td>{{$salesActivity->sales_activities_status}}</td>
                            <td>{{$salesActivity->sales_activities_due_date->format('d/m/Y')}}</td>
                            <td>{{$salesActivity->sales_activities_time}}</td>
                            <td>{{$salesActivity->sales_activities_note}}</td>
                            <td>{{$salesActivity->createdBy->employee->employee_name ?? $salesActivity->createdBy->username}} </td>
                            
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" align="center">
                            <p>No Record</p>
                           </td>
                        </tr>
                        @endforelse                        
                       
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col p-0 brdr">
            <h4><strong>Closed Activity</strong></h4>
            <div class="table-responsive1">
                <table class="table" id="clsxx_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Subject</th>
                            <th>Activity Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Created By</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesClosedActivities as $salesClosed)
                        <tr>
                            <td>{{$salesClosed->sales_activities_name}}</td>
                            <td>{{$salesClosed->sales_activity_type}}</td>
                            <td>{{$salesClosed->sales_activities_status}}</td>
                            <td>{{$salesClosed->sales_activities_due_date->format('d/m/Y')}}</td>
                            <td>{{$salesClosed->sales_activities_time}}</td>
                            <td>{{$salesClosed->sales_activities_note}}</td>
                            <td>{{$salesClosed->updatedBy->employee->employee_name ?? $salesClosed->updatedBy->username}} </td>
                            
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" align="center">
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
<!-- end 103 condition -->
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
	@if(count($salesNotes)>0)
    $('#note_datatable').DataTable({
        "bPaginate": false,
        "bInfo" : false,
        "ordering": false
    });
	@endif
	@if(count($salesActivities)>0)
		 $('#open_datatable').DataTable({
			"bPaginate": false,
			"bInfo" : false,
			"ordering": false
		});
	@endif
	 @if(count($salesClosedActivities)>0)
	   $('#close_datatable').DataTable({
			"bPaginate": false,
			"bInfo" : false,
			"ordering": false
		});
	 @endif
    $("#leade_search").validate();
    $("#form_sample_2").validate();
    $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});

});
</script>
@endsection
