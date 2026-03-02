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
            <div class="page-title"> Documentation</div>
        </div>
        {{ Breadcrumbs::render('inprogressList') }}
    </div>
</div>
<div class="row">
<div class="col">
<div class="card card-box salesLeadBox">
    <div class="card-head">
        <div class="col"><h4>{{$details->salesEnquiry->sales_enquiry_name}}</h4></div>
    </div>
    <div class="card-body">
        <form id="sales_firstcall-form" action="{{route( 'firstCall')}}"" method="POST">
            {{csrf_field()}}
            <input type="hidden" name="enquiryid" value="{{$details->salesEnquiry->id}}">
            <input type="hidden" name="sales_id" value="{{$details->id}}">
            <input type="hidden" name="action_key" value="FC">
            <input type="hidden" name="workflow_id" value="102">
            @if($details->salesEnquiry->work_flow_process_code==102)
            <button type="submit" class="btn btn-circle btn-primary align-right">First Call</button>
            @endif
        </form>
        <div class="col-sm-6 p-0">
            <dl>
                <dt>Enquiry Owner</dt>
                <dd>Armin</dd>
                <dt>Email</dt>
                <dd><a href="mailto:{{$details->salesEnquiry->sales_email}}">{{$details->salesEnquiry->sales_email}}</a></dd>
                <dt>Phone</dt>
                <dd>{{$details->salesEnquiry->sales_mobile_no}}</dd>
                <dt>Enquiry Status</dt>
                <dd>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</dd>
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
        <a href="#" data-toggle="collapse" data-target="#show">Show Details</a>
        <div id="show" class="collapse">
            <div class="col">
                <div class="row">
                    <h3>Enquiry Information</h3>
                    <div class="w-100"></div>      
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Enquiry Owner</li><li>Sujesh</li>
                            <li class="bld">Enquiry Type</li>
                            <li>{{$details->salesEnquiry->tenantType->tenant_types_name}}</li>
                            <li class="bld">Sales Person</li><li>sandeep</li>
                            <li class="bld">Enquiry No</li>
                            <li>{{$details->salesEnquiry->sales_enquiry_no}}2</li>
                            <li class="bld">Referred By</li>
                            <li>{{$details->salesEnquiry->sales_referred_by}}</li>
                            <li class="bld">Price Range</li>
                            <li>{{$details->salesEnquiry->priceRanges->implode('price_ranges_name',', ')}}</li>
                            <li class="bld">Remark</li><li>{{$details->salesEnquiry->sales_note}}</li>
                            <li class="bld">Mode</li>
                            <li>{{$details->salesEnquiry->enquirySource->enquiry_sources_name}}</li>
                        </ul>
                    </div>
                    <div class="col leadInformation">
                        <ul>
                            <li class="bld">Unit Type</li>
                            <li>{{$details->salesEnquiry->unitTypes->implode('unit_types_name',', ')}}</li>
                            <li class="bld">Status</li>
                            <li>{{$details->salesEnquiry->workFlowProcess->work_flow_processes_name}}</li>
                            <li class="bld">Move in Date</li>
                            <li>{{$details->salesEnquiry->sales_move_in_date->format('d/m/Y')}}</li>
                            <li class="bld">Enquiry Date</li>
                            <li>{{$details->salesEnquiry->created_at->format('d/m/Y')}}</li>
                            <li class="bld">Company Name</li>
                            <li>{{$details->salesEnquiry->sales_company_name}}</li>
                            <li class="bld">Priority</li><li>lorem ipsum</li>
                            <li class="bld">Square Metre</li>
                            <li>{{$details->salesEnquiry->sales_size}}</li>
                            <li class="bld">Over Due Date</li><li>lorem ipsum</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @if($details->salesEnquiry->work_flow_process_code>=103)
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
                <button type="submit" class="btn btn-primary align-right addActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}">
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
                                <button type="submit" class="btn btn-tbl-edit btn-xs editActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$salesActivity->id}}">
                                    <i class="fa fa-pencil-square-o"></i> 
                                </button>
                                <a href="{{route('salesActivities.destroy',$salesActivity->id)}}" title="Close" class="btn btn-tbl-delete btn-xs delete_type">
                                    <i class="fa fa-times-circle" aria-hidden="true"></i>
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
                                <button type="submit" class="btn btn-tbl-view btn-xs viewActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$salesClosed->id}}">
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
    @if($details->salesEnquiry->work_flow_process_code==103)
    <div class="col p-0 brdr">
        <h4><strong>Documentation</strong></h4>
        <button type="submit" class="btn btn-primary align-right addActivity" data-toggle="modal" data-target="#myModal" data-id = "{{$details->id}}">
                    <i class="fa fa-plus"></i> NEW 
        </button>
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
@if($details->salesEnquiry->work_flow_process_code==104)
    <div class="card card-box salesSearchBox">
    <h4>Documentation</h4>
    <div class="dataSearchBox">
        <form id="sales_documentation-form" action="{{route( 'tenantContract.store')}}"" method="POST">
            {{csrf_field()}}
        <div class="row">
             <div class="col-sm-6">
                <div class="form-group">
                    <label>Building Name<small class="textRed">*</small></label>
                    <select class="form-control" name="sales_activity_type" required>
                        <option value="">Select Activity Type</option>                            
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Email')? 'selected' : '') : ''}} value="1" >Email</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Phone')? 'selected' : '') : ''}} value="2" >Phone</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Task')? 'selected' : '') : ''}} value="3" >Task</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Appointment')? 'selected' : '') : ''}} value="4" >Appointment</option>
                        
                    </select>
                </div> 
            </div>
           
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Unit No<small class="textRed">*</small></label>
                    <select class="form-control" name="sales_activity_type" required>
                        <option value="">Select Activity Type</option>                            
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Email')? 'selected' : '') : ''}} value="1" >Email</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Phone')? 'selected' : '') : ''}} value="2" >Phone</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Task')? 'selected' : '') : ''}} value="3" >Task</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Appointment')? 'selected' : '') : ''}} value="4" >Appointment</option>
                        
                    </select>
                </div> 
            </div>
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Duration Type<small class="textRed">*</small></label>
                    <select class="form-control" name="sales_activity_type" required>
                        <option value="">Select Activity Type</option>                            
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Email')? 'selected' : '') : ''}} value="1" >Email</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Phone')? 'selected' : '') : ''}} value="2" >Phone</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Task')? 'selected' : '') : ''}} value="3" >Task</option>
                        <option {{ isset($salesActivity)? ((old('sales_activity_type',$salesActivity->sales_activity_type) == 'Appointment')? 'selected' : '') : ''}} value="4" >Appointment</option>
                        
                    </select>
                </div> 
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Duration</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter Duration">
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Upload</label>
                    <input type="file" class="form-control" id="simpleFormEmail" >
                </div>
            </div>
            
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Rent per month</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter Rent">
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Start date</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter start date">
                </div>
            </div>            
             <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Effective date</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter Effective date">
                </div>
            </div>
            <div class="w-100"></div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">End date</label>
                    <input type="phone" class="form-control" id="simpleFormEmail" placeholder="Enter End date">
                </div>
            </div>
             
             <div class="col-sm-6">
                <div class="form-group">
                    <label for="simpleFormEmail">Payment Method</label>
                    <select class="form-control">
                    <option>Monthly </option>
                    <option>Bi-monthly </option>
                    <option>Quarterly</option>
                    <option>Half yearly </option>
                    <option>Yearly</option>
                </select>
                </div>
            </div>
        
            <div class="w-100"></div>
            



               <div class="col">
                <div class="w-100"></div>
                    <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
        </form>
    </div>
   <div class="table-wrap">
	  <div class="table-responsive1">
        <table class="table">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th>Building Name</th>
                    <th>Unit No</th>
                    <th>Duration</th>
                    <th>Occupant ID</th>
                    <th>Action</th>
                    
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>May-June</td>
                    <td>Attachment.doc</td>
                    <td>
                        <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>

                        </a>
                        <button class="btn btn-tbl-delete btn-xs">
                           <i class="fa fa-eye" aria-hidden="true"></i>

                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>May-June</td>
                    <td>Attachment.doc</td>
                    <td>
                        <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>

                        </a>
                        <button class="btn btn-tbl-delete btn-xs">
                           <i class="fa fa-eye" aria-hidden="true"></i>

                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>May-June</td>
                    <td>Attachment.doc</td>
                    <td>
                        <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>

                        </a>
                        <button class="btn btn-tbl-delete btn-xs">
                           <i class="fa fa-eye" aria-hidden="true"></i>

                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>May-June</td>
                    <td>Attachment.doc</td>
                    <td>
                        <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>

                        </a>
                        <button class="btn btn-tbl-delete btn-xs">
                           <i class="fa fa-eye" aria-hidden="true"></i>

                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Al Khaleefa</td>
                    <td>2462656</td>
                    <td>May-June</td>
                    <td>Attachment.doc</td>
                    <td>
                        <a href="edit_booking.html" class="btn btn-tbl-edit btn-xs">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>

                        </a>
                        <button class="btn btn-tbl-delete btn-xs">
                           <i class="fa fa-eye" aria-hidden="true"></i>

                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
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
    
});
</script>
@endsection
