@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Closed Complaint</div>
    </div>
    {{ Breadcrumbs::render('complaintCompletelyClosedView',$ComplaintEnquiries) }}
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <form action="#" id="form_sample_2" class="form-horizontal">
        <div class="card-body row"> 
          
          
         <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Complaint No  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_no??''}}</span></div>
          </div>
        </div>


        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Category  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->category_name??''}}</span></div>
          </div>
        </div>

        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Building  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->building->building_name??''}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complaint Date</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_date->format('d/m/Y')??''}}</span></div>
          </div>
        </div>

        @if(isset($ComplaintEnquiries->unit_id))
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Unit  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->Unit->unit_code??''}}</span></div>
          </div>
        </div>
        @endif
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Registered Mobile No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_mob_no??''}}</span></div>
          </div>
        </div>
        @if(isset($ComplaintEnquiries->tenant_id))
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Tenant </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_name??''}}</span></div>
          </div>
        </div>
        @endif
        @if(isset($ComplaintEnquiries->tenant_id))
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Resident Card ID</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->resident_id??''}}</span></div>
          </div>
        </div>
         @endif
        @if(isset($ComplaintEnquiries->tenant_id))
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Occupant Name</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>@if($ComplaintEnquiries->occupant_id){{$ComplaintEnquiries->occupant->occupant_name}}@endif</span></div>
          </div>
        </div>
         @endif
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complainer Name</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complainer_name??''}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Location</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->location->locations_name??''}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complainer Mobile No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_mob_no??''}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Way No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->way_no??''}}</span></div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
      <h4>
        Ticket
      </h4>
      
      <table class="table display product-overview mb-30" id="dtBasicExample">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Category</th>
            <th>Description</th>
            <th>Assigned</th>
            <th>Sub Assigned</th>
            <th>Action</th>
            
          </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $ticket)
            <tr>
                <td>{{$ticket->complaint_ticket_no}}</td>
                <td>{{$ticket->work->works_code}}</td>                            
                <td>{{$ticket->checklist_desc}}</td>
                <td>@if($ticket->assigned_to)
                  @if($ticket->assigned_to_type == 0)
                    {{$ticket->assignedPerson->employee->employee_name ?? $ticket->assignedPerson->username}}
                  @else
                     {{$ticket->assignedContractor->vendor_name}}
                  @endif
                @endif
                </td>
                <td>@if($ticket->sub_assigned_to){{$ticket->subAssignedPerson->employee->employee_name ?? $ticket->subAssignedPerson->username}}@endif</td>
                <td>
                  @if($ticket->complaintServiceReport)
                  <button type="submit" class="btn btn-tbl-view btn-xs view_close_detail" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->id}}" title="View" data-backdrop="static" data-keyboard="false">
                      <i class="fa fa-eye"></i>
                  </button>
                 @endif
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

<!-- complaint notes starts-->
        <div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
      <h4>
        Note
      </h4>
              <table class="table" id="note_datatable">
                <thead>
                  <tr style="background: #f5f5f5;">
                    <th>Note</th>
                    <th>Ticket</th>                          
                    <th>Stage</th>                          
                    <th>Created By</th>
                    <th>Date & Time</th>
                  </tr>
                </thead>
                <tbody>
                  
                 @forelse ($ComplaintEnquiries->ComplaintProcessNote as $note)
                 
                 <tr>
                  <td>{{$note->complaint_processes_note}} </td>
                  <td>{{$note->complaintChecklist->complaint_ticket_no}} </td>
                  <td>{{$note->workFlowProcess->work_flow_processes_name}} </td>
                  <td>{{$note->updatedBy->employee->employee_name ?? ($note->updatedBy->username ?? '')}} </td>
                  <td>{{$note->updated_at->format('d/m/Y h:i:s')}}</td>
                </tr>                     
                @empty
                <tr>
                  <td colspan="5" >
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
<!-- complaint notes ends-->
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
@include('maintenance::complaint_js')
<script type="text/javascript">
$(document).ready(function(){
  $('.view_close_detail').on('click', function(e) {        

        var ticket_id = $(this).attr('data-id');
        $.ajax({
            method: 'GET', // Type of response and matches what we said in the route
            url: '../complaintStage/'+ticket_id+'/closedDetailedView', // This is the url we gave in the route
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