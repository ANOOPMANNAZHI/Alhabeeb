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
        <div class="col">
        <h4>
            {{$details->salesEnquiry->sales_enquiry_name}}
            @if($details->salesEnquiry->work_flow_processes_code == 105 || $details->salesEnquiry->work_flow_processes_code == 107)
            <button type="submit" class="btn btn-circle btn-primary align-right accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-backdrop="static" data-keyboard="false">Accept</button>
            <button type="submit" class="btn btn-circle btn-primary align-right reject" data-toggle="modal" data-target="#myModal" data-id = "RJCT" data-backdrop="static" data-keyboard="false">Reject</button>
            <button type="submit" class="btn btn-circle btn-primary align-right closed" data-toggle="modal" data-target="#myModal" data-id = "CL" data-backdrop="static" data-keyboard="false">Close</button>
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}" id="enquiryid">
            <input type="hidden" name="sales_id" value="{{$details->id}}" id="sales_id">
            <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}" id="workflow_id">
            @elseif($details->salesEnquiry->work_flow_processes_code == 106)            
            <form id="sales_approve-form" action="{{route( 'moveNextStage')}}" method="POST">
                {{csrf_field()}}
                <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
                <input type="hidden" name="sales_id" value="{{$details->id}}">
                <input type="hidden" name="action_key" value="MVN">
                <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}">               
                <button type="submit" class="btn btn-circle btn-primary align-right move-button">Move To Approval</button>                
            </form>
            @endif
        </h4>
        </div>
    </div>
    <div class="card-body">
        
        @if($details->salesEnquiry->work_flow_processes_code >= 105)
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Tenant Code</dt>
                <dd>{{$tenant->tenant_code}}</dd>
                <dt>Tenant Name</dt>
                <dd>{{$tenant->tenant_name}}</dd>
                <dt>Tenant Type</dt>
                <dd>{{$tenant->tenantType->tenant_types_name ?? 'NA'}}</dd>
                <dt>Tenant Status</dt>
                <dd>@if($tenant->tenant_status == 1){{'Active'}}@else {{'Inactive'}}@endif</dd>
                
            </dl> 
        </div>
        @endif
         <div class="clearfix"></div>
        <div class="w-100 mt-4"></div>
       
        <!-- <a  class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
			<span id="enquiry_info">Show Enquiry Detail</span>
		</a> -->
        <div class="clearfix"></div>
        
        <div id="show" class="">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li><li>{{$details->salesEnquiry->enquiryOwner->employee->employee_name ?? $details->salesEnquiry->enquiryOwner->username}}</li>
                            <li class="bld">Enquiry Type</li>
                            <li>{{$details->salesEnquiry->tenantType->tenant_types_name ?? 'NA'}}</li>
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->salesEnquiry->sales_referred_by ??  'NA'}}</li>
                            <li class="bld">Price Range</li>
                            <li>@if($details->salesEnquiry->priceRanges->count()>0){{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')?? 'NA'}}  @else {{'NA'}} @endif </li>
                            <li class="bld">Location</li>
                            <li>@if($details->salesEnquiry->locations->count()>0){{$details->salesEnquiry->locations->implode('locations_name',', ') ?? 'NA'}}@else {{'NA'}}@endif</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note ??  'NA'}}</li>
                            <li class="bld">Mode</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name ??  'NA'}}</li>
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
                            <li class="bld">Alternative No</li><li>{{$details->salesEnquiry->alternative_no ??  'NA'}}</li>

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
                                        <!--<td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}} </td>-->
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
<div class="card card-box salesLeadBox">
    <div class="card-body">
        @if($details->salesEnquiry->work_flow_processes_code >= 105 && $details->salesEnquiry->work_flow_processes_code <= 106)
        <div class="clr"></div><h4>Documentation</h4>
        
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
                    @forelse ($tenantContracts as $tenantContract)
                    <tr>
                        <td>{{$tenantContract->building->building_name}}</td>
                        <td>{{$tenantContract->unit->unit_code}}</td>
                        <td>{{$tenantContract->tenant_contract_duration}} {{$tenantContract->tenant_contract_duration_type}}</td>
                        <td> @if(!empty($tenantContract->tenantDocument->tenant_documents_name)) {{$tenantContract->tenantDocument->tenant_documents_name}}  @endif</td>
                        <td>
                           <!--  <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}">
                            <i class="fa fa-pencil-square-o"></i> 
                            </button>  -->
                            <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-eye"></i>
                            </button>
                            @if($details->salesEnquiry->work_flow_processes_code >= 106 && $details->salesEnquiry->work_flow_processes_code <= 107)
                            <a href="{{route('contractCreation',[$tenantContract->id,$details->salesEnquiry->work_flow_processes_code])}}" title="Contract" class="btn btn-tbl-general btn-xs">
                                    <i class="fa fa-pie-chart" aria-hidden="true"></i>
                            </a>
                            @endif

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                   
                </tbody>
            </table>
        </div>
        <div class="clearfix"></div>
        @elseif($details->salesEnquiry->work_flow_processes_code == 108)
        <div class="clr"></div><h4>Tenant Contract</h4>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Contract No</th>
                        <th>Tenant Name</th>
                        <th>Municipality Agr No</th>
                        <th>Rent Amount</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenantContracts as $tenantContract)
                    <tr>
                        <td>{{$tenantContract->tenant_contract_no}}</td>
                        <td>{{$tenantContract->tenant->tenant_name}}</td>
                        <td>{{$tenantContract->tenant_contract_muncipality_agr_no}}</td>
                        <td>{{number_format($tenantContract->tenant_contract_rent,3,",","")}} OMR</td>
                        <td>
                           <!--  <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}">
                            <i class="fa fa-pencil-square-o"></i> 
                            </button>  -->
                            <button type="submit" title="View" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-eye"></i>
                            </button>                            

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                   
                </tbody>
            </table>
        </div>
        @endif
       
        @if($details->salesEnquiry->work_flow_processes_code >= 103)
        <div class="add-note-section">
            <!-- <form id="sales_note-form" action="{{route( 'leadAssign.storeSalesNote')}}"" method="POST">
            {{csrf_field()}}
              <div class="col-sm-12">
                <div class="form-group">
                    <label for="simpleFormEmail">Notes</label>
                    <textarea class="form-control" rows="2" required name="sales_notes" placeholder="Enter Description"></textarea>
                    <input type="hidden" name="sales_id" value="{{$details->id}}">
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
        
      @endif
    
    
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
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $("#leade_search").validate();
    $("#sales_note_modal").validate();
    @if(count($salesNotes)>0)
    $('#note_datatable').DataTable({
        "bPaginate": false,
        "bInfo" : false,
        "ordering": false
    });
    @endif

   
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
            url: '../../tenantContract/'+tenant_id+'', // This is the url we gave in the route
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
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
         if (confirm('Do you want to Accept this Enquiry?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        }else {
            return false;
        }
        
         
    });
    $('.reject').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        if (confirm('Do you want to Reject this Enquiry?')) {
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        }else {
            return false;
        }
        
         
    });
    $('.closed').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        if (confirm('Do you want to Close this Enquiry?')) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        }else {
            return false;
        }
        
         
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
