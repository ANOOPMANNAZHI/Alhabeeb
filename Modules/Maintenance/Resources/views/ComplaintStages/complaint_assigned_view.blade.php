@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Ticket</div>
    </div>
    {{ Breadcrumbs::render('complaintAssigned.view',$ticket) }}
  </div>
</div>

<div class="row" style="margin-top: 15px;">
  <div class="col-sm-12">
    <div class="card-box">
     <div class="card-head">
      <h4>
        @if($ticket->assigned_to)
        @if($ticket->assigned_to_type == 0)
        @can('sub_assign')
        <button type="button" class="btn btn btn-circle btn-primary align-right SubAssign" title="Assign" data-toggle="modal" data-target="#myModal" datas-id="{{$ticket->id}}" datass-id="{{$ticket->work_flow_processes_code}}" data-id="{{$ticket->complaintEnquiry->id}}" data-backdrop="static" data-keyboard="false">Assign</button> 
        @endcan
        @else
        @can('generate_service_report')
        @if($service_report == null)
        <a title="Check In" href="{{route('checkInContractor',$ticket->id)}}" class="btn btn-circle btn-primary float-right" >Check In</a>
        @else
        <a href="{{route('contractorServiceReport',$ticket->id)}}" class="btn btn-circle btn-primary align-right">Service Report</a> 
        @endif
        @endcan
        @endif
        @endif
        <div class="clr"></div>
      </h4>
    </div>
    <form action="#" id="form_sample_2" class="form-horizontal">
      <div class="card-body row"> 


       <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Complaint No  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->complaint_no}}</span></div>
        </div>
      </div>

 <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Category </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->category_name}}</span></div>
        </div>
      </div>


      
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Complaint Date   </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->complaint_date->format('d/m/Y')}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b> Complainer Name  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->complainer_name}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Complainer Contact No</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{!! substr($ticket->complaintEnquiry->complaint_mob_no, 5) !!}</span></div>
        </div>
      </div>
       <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Building</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->building->building_name}}</span></div>
        </div>
      </div>
       @if(isset($ticket->complaintEnquiry->unit_id))
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Unit  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->Unit->unit_code}}</span></div>
        </div>
      </div>
       @endif
      @if(isset($ticket->complaintEnquiry->tenant_id))
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Tenant </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->tenant->tenant_name}}</span></div>
        </div>
      </div>
      @endif
       @if(isset($ticket->complaintEnquiry->tenant_id))
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Tenant Mobile No</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{!! substr($ticket->complaintEnquiry->tenant->tenant_contact_no, 5) !!}</span></div>
        </div>
      </div>
      @endif
       @if(isset($ticket->complaintEnquiry->tenant_id))
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Resident Card ID </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->tenant->resident_id}}</span></div>
        </div>
      </div> 
      @endif
       @if(isset($ticket->complaintEnquiry->tenant_id))
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Occupant Name </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>@if($ticket->complaintEnquiry->occupant_id){{$ticket->complaintEnquiry->occupant->occupant_name}}@endif</span></div>
        </div>
      </div> 
      @endif
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Location </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->location->locations_name}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Way No</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->way_no??''}}</span></div>
        </div>
      </div>
      
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Ticket No</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaint_ticket_no}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Ticket Status</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->ticket_status_name}}</span></div>
        </div>
      </div>
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Complaint Category</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->work->works_code}}</span></div>
        </div>
      </div>

      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Description</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->checklist_desc}}</span></div>
        </div>
      </div>
 @if(count($ticket->complaintEnquiry->preferredTime) > 0)
      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Preferred Time</b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>{{$ticket->complaintEnquiry->preferredTime->implode('pre_time',', ')}}</span></div>
        </div>
      </div>
@endif


      <div class="col-md-6 p-t-10">
        <div class="row">
          <div class="col-md-5"><b>Assigned To  </b></div>
          <div class="col-md-1 s-clm">:</div>
          <div class="col-md-6"><span>
            @if($ticket->assigned_to)
            @if($ticket->assigned_to_type == 0)
            {{$ticket->assignedPerson->employee->employee_name ?? $ticket->assignedPerson->username}}
            @else
            {{$ticket->assignedContractor->vendor_name}}
            @endif
            @endif


          </span></div>
        </div>
      </div> 
    </div>
  </form>
  <!--- Notes starts here--->
<div class="col-md-12">
        <div class="col p-0">
          <h4><strong></strong><div class="clr"></div></h4>

          <div class="table-responsive1">
            <table class="table" id="note_datatable">
              <thead>
                <tr style="background: #f5f5f5;">
                  <th>Note</th>
                  <th>Stage</th>                            
                  <th>Created By</th>
                  <th>Date & Time</th>
                </tr>
              </thead>
              <tbody>

               @forelse ($ServiceReportNotes as $note)

               <tr>
                <td>{{$note->desc}} </td>
                <td>{{$note->ServiceReportStageName}}</td>
                <td>{{$note->createdBy->username}} </td>
                <td>{{$note->created_at->format('d/m/Y h:i:s')}}</td>
              </tr>                     
              @empty
              <tr>
                <td colspan="4" >
                  <p>No Record</p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>

        </div>
      </div>

<!-- Notes ends here -->
</div>
</div>
</div>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
@include('maintenance::complaint_js')
@endsection
