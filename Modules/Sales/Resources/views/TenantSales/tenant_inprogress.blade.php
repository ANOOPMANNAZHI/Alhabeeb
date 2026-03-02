@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- //code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css -->
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">

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
                <div class="col"><h4>{{$details->salesEnquiry->sales_enquiry_name}}
                    <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}" id="enquiryid">
                    <input type="hidden" name="sales_id" value="{{$details->id}}" id="sales_id">
                    <input type="hidden" name="action_key" value="MVN" id="action_key">
                    <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}" id="workflow_id">
                    @if($details->salesEnquiry->work_flow_processes_code <=102)
        <!-- <form id="sales_firstcall-form" action="{{route( 'moveNextStage')}}"" method="POST">
        {{csrf_field()}} -->
        
        
            <!-- <button type="submit" class="btn btn-circle btn-primary align-right move-button">First Callsdsdd</button>

            
        </form> -->
        
        <button type="submit" class="btn btn-circle btn-primary align-right firstCall" data-toggle="modal" data-target="#myModal" data-id = "MVN" title="FirstCall" datas-id ="" datas-enid=""  data-backdrop="static" data-keyboard="false">First Call</button>
        @elseif($details->salesEnquiry->work_flow_processes_code ==103)
        
        
            <!-- <form id="sales_documentaion-form" action="{{route( 'moveNextStage')}}"" method="POST">
                {{csrf_field()}}
                <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
                <input type="hidden" name="sales_id" value="{{$details->id}}">
                <input type="hidden" name="action_key" value="MVN">
                <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}">
               
                <button type="submit" class="btn btn-circle btn-primary align-right move-button">Move To Documentation</button>
                
            </form> move-button-->
            <button type="submit" class="btn btn-circle btn-primary align-right  move-to-next" data-toggle="modal" data-target="#myModal" data-id = "MVN" title="Move To Documentation" datas-id ="" datas-enid="" data-backdrop="static" data-keyboard="false">Move To Documentation</button>
            @endif
            @if($tenantContracts->count() > 0)
            
            <!-- <form id="sales_approve-form" action="{{route( 'moveNextStage')}}"" method="POST">
                {{csrf_field()}}
                <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
                <input type="hidden" name="sales_id" value="{{$details->id}}">
                <input type="hidden" name="action_key" value="MVN">
                <input type="hidden" name="workflow_id" value="{{$details->salesEnquiry->work_flow_processes_code}}">
               
                <button type="submit" class="btn btn-circle btn-primary align-right move-button">Move To Approval</button>
                
            </form> -->
            @can('assign_tenant_enquiry')
			<!-- <button title="Assign"  type="button" class="btn btn-circle btn-primary re_Assign align-right reassign" data-toggle="modal" data-target="#myModal" data-id = "{{$details->salesEnquiry->id}}" datas-id= "{{$details->salesEnquiry->work_flow_processes_code}}">
				   Reassign
             </button> -->
             @endcan
             <button type="submit" class="btn btn-circle btn-primary align-right move-to-approve" data-toggle="modal" data-target="#myModal" data-id = "MVN" title="Move To Approval" datas-id ="" datas-enid=""  data-backdrop="static" data-keyboard="false">Move To Approval</button>
             
             
             @endif
             @can('assign_tenant_enquiry')
             <button title="Assign"  type="button" class="btn btn-circle btn-primary re_Assign align-right {{($details->salesEnquiry->work_flow_processes_code ==101)?'assignLead':'reassign'}}" data-toggle="modal" data-target="#myModal" data-id = "{{$details->salesEnquiry->id}}" datas-id= "{{$details->salesEnquiry->work_flow_processes_code}}" data-backdrop="static" data-keyboard="false">
                {{($details->salesEnquiry->work_flow_processes_code ==101)?'Assign':'Reassign'}}
            </button>
            @endcan
        </h4></div>
    </div>
    <div class="card-body">
        
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry Owner</dt>
                <?php if(isset($details->salesEnquiry->enquiryOwner->employee->employee_name)){?>
                    <dd>{{$details->salesEnquiry->enquiryOwner->employee->employee_name ?? $details->salesEnquiry->enquiryOwner->username}}</dd>
                <?php }?>
                <?php if(!isset($details->salesEnquiry->enquiryOwner->employee->employee_name)){?>
                    <dd>WhatsApp Enquiry</dd>
                <?php }?>

                <dt>Email</dt>
                <dd><a href="mailto:{{$details->salesEnquiry->sales_email}}">{{$details->salesEnquiry->sales_email??'NA'}}</a></dd>
                <dt>Phone</dt>
                <dd>@if($details->salesEnquiry->sales_mobile_no)  {{$details->salesEnquiry->sales_mobile_no}} @else NA @endif</dd>
                <!-- <dt>Enquiry Status</dt>
                <dd>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</dd> -->
                
            </dl> 
        </div>
        
        <div class="clearfix"></div>
        @if($details->salesEnquiry->work_flow_processes_code <= 102)
        <div class="col nxtaction mt-4">
            <h6>Next Action</h6>
            <div id="flag">{{date('M d')}}</div> <h5><strong>Call {{$details->salesEnquiry->sales_enquiry_name}}</strong></h5>
        </div>
        @endif
        @if(($salesActivitieslatest) && ($details->salesEnquiry->work_flow_processes_code==103 || $details->salesEnquiry->work_flow_processes_code==104))
        <div class="col nxtaction mt-4">
            
            <h6>Next Action</h6>
            <div id="flag">{{$salesActivitieslatest->sales_activities_due_date->format('M d')}}</div> <h5><strong> {{$salesActivitieslatest->sales_activities_name}}</strong></h5>
            
        </div>
        
        @endif
        
        <div class="w-100 mt-4"></div>
        
        <!-- <a  class="btn btn-circle btn-success align-right" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
				<span id="enquiry_info">Show Enquiry Details</span>
          </a> -->
          <div class="clearfix"></div>
          <div id="show" class="">
            <div class="col">
                <div class="row">

                    <h3>Enquiry Information</h3>
                    @can('edit_tenant_enquiry')
                    @hasanyrole('sales_person|sales_coordinator|sales_head')
                    @if($details->salesEnquiry->work_flow_processes_code < 104)
                    <a title="Edit" href="{{route('enquiry.edit',$details->salesEnquiry->id)}}" class="btn btn-tbl-edit btn-xs advanceEdit" >
                        <i class="fa fa-pencil"></i>
                    </a>
                    @endif
                    @endhasanyrole
                    @endcan
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li>

                            <?php if(isset($details->salesEnquiry->enquiryOwner->employee->employee_name)){?>
                               <li>{{$details->salesEnquiry->enquiryOwner->employee->employee_name?? $details->salesEnquiry->enquiryOwner->username}}</li>
                            <?php }?>
                            <?php if(!isset($details->salesEnquiry->enquiryOwner->employee->employee_name)){?>
                                <!-- <dd>WhatsApp Enquiry</dd> -->
                                <li>WhatsApp Enquiry</li>
                            <?php }?>


                            


                            <li class="bld">Enquiry Type</li>
                            <li>@if($details->salesEnquiry->tenant_type_id){{$details->salesEnquiry->tenantType->tenant_types_name}}@else {{'NA'}} @endif</li>
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no ??  'NA'}}</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->salesEnquiry->sales_referred_by??  'NA'}}</li>
                            <li class="bld">Price Range</li>
                            <li>{{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')?? 'NA'}}</li>
                            <li class="bld">Location</li>
                            <li>{{$details->salesEnquiry->locations->implode('locations_name',', ') ?? 'NA'}}</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note ??  'NA'}}</li>
                            <li class="bld">Alternative No</li><li>{{$details->salesEnquiry->alternative_no ??  'NA'}}</li>
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Unit Type</li>
                            <li>{{$details->salesEnquiry->unitTypes->implode('unit_types_name',', ')}}</li>
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
        <div class="clearfix"></div>
        
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
                <div class="table-wrap">
                  <div class="table-responsive1">
                    <div class="table">
                        <table class="table" >
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

</div>
</div>
@if($details->salesEnquiry->work_flow_processes_code == 104)
<div class="card card-box salesSearchBox">
    <h4>Documentation (Add Documentation Against Each Unit)
  
    @if($tenantContracts->isEmpty())
        <button type="submit" class="btn btn-primary align-right add_documentation" data-toggle="modal" data-target="#myModal1" data-id = "{{$details->id}}" data-backdrop="static" data-keyboard="false">Create Document
            <i class="fa fa-plus"></i> 
        </button>
    @endif
        <div class="clr"></div></h4>
        <div class="table-wrap">
          <div class="table-responsive1">
             
            <table class="table" >
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Building Name</th>
                        <th>Unit No</th>
                        <th>Unit Usage</th>
                        
                        <th>Duration</th>
                        
                        <th>Occupant ID</th>
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
                   <td>{{$tenantContract->building->building_name ?? 'NA'}}</td>
                   <td>{{$tenantContract->unit->unit_code ?? 'NA'}}</td>
                   <td>{{$tenantContract->unit_usage?? 'NA'}}</td>
                   <td>
                    @if(isset($tenantContract->tenant_contract_duration_countdown))
                    {{$count[0]? $count[0].'y ':''}} {{$count[1]? $count[1].'m ':''}} {{$count[2]? $count[2].'d ':''}} 
                    @endif
                </td>
                <td>@foreach($tenantContract->tenantDocument as $documents)
                    
                    @if(!empty($documents->tenant_documents_name)) <a target="_blank" href="{{asset('storage/app/'.$documents->tenant_documents_file_name)}}">{{$documents->tenant_documents_name}}</a>  @endif
                    @endforeach
                </td>
                
                <td>
                    <button type="submit" class="btn btn-tbl-edit btn-xs editDocumentation" data-toggle="modal" data-target="#myModal1" data-id = "{{$tenantContract->id}}" title="Edit" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-pencil"></i> 
                    </button> 
                    <button type="submit" class="btn btn-tbl-view btn-xs viewDocumentation" data-toggle="modal" data-target="#myModal2" data-id = "{{$tenantContract->id}}" title="View" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-eye"></i>
                    </button>
                    <a href="{{route('contractDestroy',$tenantContract->id)}}" title="Delete" class="btn btn-tbl-delete btn-xs delete_doc">
                        <i class="fa fa-trash-o "></i>
                    </a>
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
</div></div>
<div class="clearfix"></div>

</div>
@endif

<!-- 103 condition -->
<div class="card card-box salesLeadBox">
    <div class="card-body">
        
        <div class="add-note-section">
            <form id="sales_note-form" action="{{route( 'leadAssign.storeSalesNote')}}" class="sales_note_form" method="POST">
                {{csrf_field()}}
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="simpleFormEmail">Note</label>
                        <textarea class="form-control" rows="2" id="sales_notes" required name="sales_notes" placeholder="Enter Note" maxlength="200"></textarea>
                        <input type="hidden" name="sales_id" value="{{$details->id}}">
                        <input type="hidden" name="workflowId" value="{{$details->salesEnquiry->work_flow_processes_code}}">
                    </div>
                </div>
                <div class="col-sm-2"><button type="submit" id="Note" class="btn btn-primary Note" >Save</button></div>
            </form>
            <div class="col-md-12">
                <div class="col p-0">
                    <h4><strong></strong><div class="clr"></div></h4>
                    
                    <div class="table-wrap">
                      <div class="table-responsive1">
                        <div class="table">
                            <table class="table" id="note_datatable">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th>Progress</th>
                                        <th>Date & Time</th>
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
    </div>
</div>
<div class="w-100 mt-5 mb-5"></div>

<div class="col p-0">
    <h4><strong>Open Activity</strong> 
        <button type="submit" class="btn btn-primary align-right addActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}" data-backdrop="static" data-keyboard="false">Add
            <i class="fa fa-plus"></i> 
        </button>

        <div class="clr"></div></h4>
        <div class="table-wrap">
          <div class="table-responsive1">
            <div class="table">
                <table class="table" id="open_datatable">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th>Subject</th>
                            <th>Activity Type</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Time</th>
                            <th>Note</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesActivities as $salesActivity)
                        <tr>
                            <td>{{$salesActivity->sales_activities_name}}</td>
                            <td>{{$salesActivity->sales_activity_type}}</td>
                            <td>{{$salesActivity->sales_activities_status}}</td>
                            <td>{{$salesActivity->sales_activities_due_date->format('d/m/Y')}}</td>
                            <td>@if($salesActivity->sales_activities_time) 
                                {{date("g:i a", strtotime($salesActivity->sales_activities_time))}}
                                @else
                                
                                @endif</td>
                                <td>{{$salesActivity->sales_activities_note}}</td>
                                <td>{{$salesActivity->createdBy->employee->employee_name ?? $salesActivity->createdBy->username}} </td>
                                <td>
                                    <button type="submit" class="btn btn-tbl-edit btn-xs editActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$salesActivity->id}}" title="Edit" data-backdrop="static" data-keyboard="false">
                                        <i class="fa fa-pencil"></i> 
                                    </button>
                                    <a href="{{route('salesActivities.destroy',$salesActivity->id)}}" title="Close" class="btn btn-tbl-delete btn-xs delete_type">
                                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                                    </a>
                                    <button type="submit" class="btn btn-tbl-view btn-xs viewActivity" data-toggle="modal" data-target="#myModal2" data-id = "{{$salesActivity->id}}" title="View" data-backdrop="static" data-keyboard="false">
                                        <i class="fa fa-eye"></i>
                                    </button>                       
                                <!-- <button class="btn btn-tbl-delete btn-xs" title="Close">
                                   <i class="fa fa-pie-chart" aria-hidden="true"></i>

                               </button> -->
                           </td>
                       </tr>
                       @empty
                       <tr>
                        <td colspan="8" align="center">
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

<div class="col p-0 brdr">
    <h4><strong>Closed Activity</strong></h4>
    <div class="table-wrap">
      <div class="table-responsive1">
        <div class="table">
            <table class="table" id="clsxx_datatable">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th>Subject</th>
                        <th>Activity Type</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Time</th>
                        <th>Note</th>
                        <th>Created By</th>
                        <th>Action</th>
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
                        <td>{{$salesClosed->updatedBy->employee->employee_name ?? $salesClosed->createdBy->username}} </td>
                        <td>
                            <button type="submit" class="btn btn-tbl-view btn-xs viewActivity" data-toggle="modal" data-target="#myModal2" data-id = "{{$salesClosed->id}}" data-backdrop="static" data-keyboard="false">
                                <i class="fa fa-eye"></i>
                            </button>
                            
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" align="center">
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
<!-- end 103 condition -->
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
<div class="modal " id="myModal2">
    
</div>

<div class="modal" id="edit_enquiry">
    
</div>
@endsection
@section('scripts')
<!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/jquery-ui.js') }} "></script>
<script>

    $(document).ready(function() {
    /*$("#sales_note-form").on('submit',function(e){
        e.preventDefault();
        $("#Note").attr("disabled", true);
        return true;
        var el = $(this);
        el.prop('disabled', true);
        setTimeout(function(){el.prop('disabled', false); }, 3000);
    });*/


    $(".sales_note_form").validate({
      submitHandler: function(form) {
          $('.Note').prop('disabled', true);
          form.submit();
      }
  });
    
    jQuery('.delete_doc').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Document?Please Confirm it..')){
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        }else {
            return false;
        }
    });
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
    $("#sales_note-form").validate();
    $('.assignLead').on('click', function(e) {        
        
       
        var vals = $(this).attr('data-id');
        var workflow = $(this).attr('datas-id');
        $('input:hidden[name=enquiryId]').val(vals);
        $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../../leadAssign/assignModal/'+vals+'/'+workflow+'/view', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
        return true;
        
        
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
        /*if (confirm('Do you want to Close this Activity?')) {*/
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        /*} else {
            return false;
        }*/
    });
    $('.viewActivity').on('click', function(event) {        
        event.preventDefault();
        var activity_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../salesActivities/'+activity_id+'', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                
                $("#myModal2").html(response); 
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
                $("#myModal1").html(response); 
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
                $("#myModal1").html(response); 
            },
        });
        return true;
        
        
    });
    $('.viewDocumentation').on('click', function(e) {        

        var tenantContract_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../tenantContract/'+tenantContract_id+'', // This is the url we gave in the route
            //data: {'id' : activity_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal2").html(response); 
            },
        });
        return true;
        
        
    }); 
    $('.firstCall').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $("#workflow_id").val();
        var enquiryid = $("#enquiryid").val();
        //alert(workflow_id);
        //if (confirm('Do you want to Close this Enquiry?')) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else {
            return false;
        }*/
        
        
    });
    $('.move-to-next').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $("#workflow_id").val();
        var enquiryid = $("#enquiryid").val();
        //alert(workflow_id);
        //if (confirm('Do you want to Close this Enquiry?')) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else {
            return false;
        }*/
        
        
    });
    $('.move-to-approve').on('click', function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $("#workflow_id").val();
        var enquiryid = $("#enquiryid").val();
        //alert(workflow_id);
        //if (confirm('Do you want to Close this Enquiry?')) {
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else {
            return false;
        }*/
        
        
    });
    // Edit Enquiry
    
    $('#edit_enquiry_popup').on('click', function(e) {        

        var sales_enquiry_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../../enquiryEditPopup/'+sales_enquiry_id+'', // This is the url we gave in the route
            //data: {'id' : sales_enquiry_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#edit_enquiry").html(response); 
            },
        });
        return true;
        
        
    });
    
    $("#myModal").on("hidden.bs.modal", function(){
        $("#myModal").html("");
        $(this).removeData('bs.modal');
    });
    $("#myModal2").on("hidden.bs.modal", function(){
        $("#myModal2").html("");
        $(this).removeData('bs.modal');
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
