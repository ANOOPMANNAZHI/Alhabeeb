@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">

@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">
				@if($details->salesEnquiry->work_flow_processes_code == 106) 
				Final Documentation
				@else
				{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}
				@endif
			</div>
        </div>
        @if($details->salesEnquiry->work_flow_processes_code == 106) 
			{{ Breadcrumbs::render('contractCreation',$details,$details->salesEnquiry->work_flow_processes_code,$routes) }}
		@else
			{{ Breadcrumbs::render('stageApprove',$details,$details->salesEnquiry->work_flow_processes_code,$routes) }}
			
		@endif
			
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
                @if($details->salesEnquiry->work_flow_processes_code !=105)
                 @can('assign_tenant_enquiry')
				<button title="Assign"  type="button" class="btn btn-circle btn-primary re_Assign align-right reassign" data-toggle="modal" data-target="#myModal" data-id = "{{$details->salesEnquiry->id}}" datas-id= "{{$details->salesEnquiry->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">
					   Reassign
				</button>
				@endcan
                @endif
                @if($tenantContracts->count() == 1)
                <button type="submit" class="btn btn-circle btn-primary align-right accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" data-backdrop="static" data-keyboard="false">Accept</button>
                    @if($direct_indirect_status == false)
                    <button type="submit" class="btn btn-circle btn-primary align-right reject" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" data-backdrop="static" data-keyboard="false">Refer Back</button>
                    @endif
                @else                    
                <button type="submit" id="reject" class="btn btn-circle btn-primary align-right reject" data-toggle="modal" data-target="#myModal1" data-id = "RJCT" style="display: none;" data-backdrop="static" data-keyboard="false">Reject</button>
              
                <button type="submit" id="accept" class="btn btn-circle btn-primary align-right accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" style="display: none;" data-backdrop="static" data-keyboard="false">Accept</button>
                @endif
            <button type="submit" class="btn btn-circle btn-primary align-right closed" data-toggle="modal" data-target="#myModal" data-id = "CL" data-backdrop="static" data-keyboard="false">Reject</button>
           
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}" id="enquiryid">
            <input type="hidden" name="sales_id" value="{{$details->id}}" id="sales_id">
            <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}" id="workflow_id">
            @elseif($details->salesEnquiry->work_flow_processes_code == 106)            
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}" id="enquiryid">
            <input type="hidden" name="sales_id" value="{{$details->id}}" id="sales_id">
            <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}" id="workflow_id"> 
            @if($details->salesEnquiry->work_flow_processes_code !=105)
            @can('assign_tenant_enquiry')
				<button title="Assign"  type="button" class="btn btn-circle btn-primary re_Assign align-right reassign" data-toggle="modal" data-target="#myModal" data-id = "{{$details->salesEnquiry->id}}" datas-id= "{{$details->salesEnquiry->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">
					   Reassign
				</button>
			@endcan
            @endif
            <input type="hidden" name="occuipied_units" id="occuipied_units" value="{{$occuipied_units}}">
            @can('final_documentation_accept')
            <button type="submit" class="btn btn-circle btn-primary align-right accept" data-toggle="modal" data-target="#myModal" data-id = "ACPT" title="Accept" datas-id ="" datas-enid="" data-backdrop="static" data-keyboard="false">Accept</button>
            @endcan    

            @can('final_documentation_renegotiate')
            <button type="submit" class="btn btn-circle btn-primary align-right renegotiate" data-toggle="modal" data-target="#myModal" data-id = "RNEG" title="Renegotiate" datas-id ="" datas-enid="" data-backdrop="static" data-keyboard="false">Renegotiate</button>
            @endcan
            @endif
        </h4>
        </div>
    </div>
    <div class="card-body">
        
        @if($details->salesEnquiry->work_flow_processes_code >= 105)
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry Owner</dt>
                <dd>{{$details->salesEnquiry->enquiryOwner->employee->employee_name ?? $details->salesEnquiry->enquiryOwner->username}}</dd>
                <dt>Email</dt>
                <dd><a href="mailto:{{$details->salesEnquiry->sales_email}}">{{$details->salesEnquiry->sales_email?? 'NA'}}</a></dd>
                <dt>Phone</dt>
                <dd>{{$details->salesEnquiry->sales_mobile_no}}</dd>
                <!-- <dt>Enquiry Status</dt>
                <dd>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</dd> -->
                
               
            </dl> 
        </div>
        @endif
        <div class="clearfix"></div>
        <div class="w-100 mt-4"></div>
        <!-- <a class="btn btn-circle btn-success align-right href="#" data-toggle="collapse" data-target="#show">
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
                            <li>@if($details->salesEnquiry->tenant_type_id){{$details->salesEnquiry->tenantType->tenant_types_name}}@else {{'NA'}}@endif</li>
                            
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
							<li class="bld">Sales Person</li>
                            <li>{{$details->salesEnquiry->assignedPerson->employee->employee_name ?? 'NA'}} </li>
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
                            <li>{{$details->salesEnquiry->created_at->format('d/m/Y') ?? 'NA'}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{$details->salesEnquiry->sales_company_name ??  'NA'}}</li>
                            <li class="bld">Square Meter</li>
                            <li>{{$details->salesEnquiry->sales_size ??  'NA'}}</li>
                            <li class="bld">No Of Units</li>
                            <li>{{$details->salesEnquiry->sales_no_of_unit ??  'NA' }}</li>
                            <li class="bld">Source</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name ??  'NA'}}</li>
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card card-box salesLeadBox">
    <div class="card-body">
        @if($details->salesEnquiry->work_flow_processes_code >= 105 && $details->salesEnquiry->work_flow_processes_code <= 106)
        <div class="clr"></div><h4>Documentation @if($details->salesEnquiry->work_flow_processes_code == 106 ) {{'(Add Contract Details In Each Document)'}}@endif</h4>
        
        <div class="table-responsive1">
            <table class="table">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Building Name</th>
                        <th>Unit No</th>
                        <th>Unit Usage</th>
                        <th>Duration</th>
                        <th>Occupant ID</th>
						 <th>Created By</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenantContracts as $tenantContract)
                    <tr>
						@php 
						if(isset($tenantContract->tenant_contract_duration_countdown)){
							$duration = $tenantContract->tenant_contract_duration_countdown;
							$count = explode('-',$duration); 
						}
						@endphp
                        <td>{{$tenantContract->building->building_name}}</td>
                        <td>{{$tenantContract->unit->unit_code}}</td>
                        <td>{{$tenantContract->unit_usage}}</td>
                        <td>
						@if(isset($tenantContract->tenant_contract_duration_countdown))
						{{$count[0]? $count[0].'y ':''}} {{$count[1]? $count[1].'m ':''}} {{$count[2]? $count[2].'d ':''}} 
						@endif
						</td>
                        <td>@foreach($tenantContract->tenantDocument as $documents)
                        
                            @if(!empty($documents->tenant_documents_name)) <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">{{$documents->tenant_documents_name}}</a> <br/> @endif
                            @endforeach
                        </td>
						<td>{{$tenantContract->createdBy->employee->employee_name}}</td>
                        <td>
                            
                            <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}" title="View" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-eye"></i>
                            </button>
                            @if($details->salesEnquiry->work_flow_processes_code != 106)
                            <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}" title="Edit" data-backdrop="static" data-keyboard="false">
                            <i class="fa fa-pencil"></i> 
                            </button>
                            @if($tenantContracts->count()>1)
                            <input id="toggle-event" class="toggle-event" type="checkbox" data-toggle="toggle" data-size="mini" data-onstyle="info" data-offstyle="danger" data-on="Accept" data-off="Reject" value="{{$tenantContract->id}}" @if($tenantContract->accept_reject_status==1) {{'checked'}} @endif>
                            @endif
                            @endif 
                            @if($details->salesEnquiry->work_flow_processes_code >= 106 && $details->salesEnquiry->work_flow_processes_code <= 107 )
                            <!-- && $tenantContract->tenant_contract_muncipality_agr_no =="" -->
                            <a href="{{route('contractCreation',[$tenantContract->id,$details->salesEnquiry->work_flow_processes_code])}}" title="Contract" class="btn btn-tbl-general btn-xs">
                                    <i class="fa fa-pie-chart" aria-hidden="true"></i>
                            </a>
                            @endif

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" align="center">
                        <p>No Record</p>
                       </td>
                    </tr>
                    @endforelse
                   
                </tbody>
            </table>
        </div>
        <div class="clearfix"></div>
        @elseif($details->salesEnquiry->work_flow_processes_code >= 107)
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
                        <td>{{number_format($tenantContract->tenant_contract_rent,3)}} OMR</td>
                        <td>
                           <!--  <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}">
                            <i class="fa fa-pencil-square-o"></i> 
                            </button>  -->
                            <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal" data-id = "{{$tenantContract->id}}" title="View" data-backdrop="static" data-keyboard="false">
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
        <div class="clearfix"></div>
        
        @if($details->salesEnquiry->work_flow_processes_code >= 103 && count($salesNotes)>0)
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
           
            <div class="">
                <table class="table" id="sale_note_datatable">
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
        
      @endif
    
    
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
                                       <!-- <td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}} </td>-->
										<td>{{$note->updatedBy->employee->employee_name ?? $note->updatedBy->username}} </td>
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
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>
$(function() {
    var ar_status = <?php echo json_encode($ar_status)?>;
    if(ar_status== false){
        $("#accept").show();
        $("#reject").hide(); 
    }else{
        $("#reject").show(); 
        $("#accept").hide();
    }
    $('.toggle-event').change(function() {
        var checked = $(this).prop('checked');
        var value = $(this).val();
        var enquiryid = $("#enquiryid").val();
        if(checked == false){
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('accept-reject')}}", // This is the url we gave in the route
                data: {'id' : value,'status' : 2,'enqId':enquiryid,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){// What to do if we succeed
                    if(response=='false'){
                        $("#reject").hide(); 
                        $("#accept").show();
                    }else {
                        $("#reject").show();
                        $("#accept").hide();
                    }
                },
            });
        }else{
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('accept-reject')}}", // This is the url we gave in the route
                data: {'id' : value,'status' : 1,'enqId':enquiryid,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    if(response == 'false'){
                        $("#reject").hide(); 
                        $("#accept").show();
                    }else {
                        $("#reject").show();
                        $("#accept").hide();
                    }
                        
                    
                    
                },
            });
        }
        
    })
  })
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
            url: '../../tenantContract/'+tenant_id+'', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response); 
            },
        });
        return true;
        
         
    });
    $('.editDocumentation').on('click', function(e) {        

        var tenantContract_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the route
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
        var occuipied_units = $("#occuipied_units").val();
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
    $('.renegotiate').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        var occuipied_units = $("#occuipied_units").val();
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
   $('.reassign').on('click', function(e) {        

        /*if (confirm("Are you sure you want to Re-assign ?")) {*/
            var vals = $(this).attr('data-id');
            var workflow = $(this).attr('datas-id');
            $('input:hidden[name=enquiryId]').val(vals);
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../../leadAssign/re-assign/'+vals+'/'+workflow+'/view', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*} else {
            return false;
        }*/  
         
    });
    $('.reject').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Reject this Enquiry?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
            data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal1").html(response); 
            },
        });
        return true;
        /*}else {
            return false;
        }*/
        
         
    });
    $('.closed').on('click', function(e) {        

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
    $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    });
    $("#myModal1").on("hidden.bs.modal", function(){
        $("#myModal1").html("");
        $(this).removeData('bs.modal');
    });
    /****************************************************************************88888*/  
    $("#show").on("hide.bs.collapse", function(){
			$("#enquiry_info").html('Show Enquiry Details');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquiry_info").html('Hide Enquiry Details');
	});
});
</script>
@endsection
