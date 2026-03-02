@extends('layouts.plms-app')
@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
@endsection

@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Service Report</div>
    </div>
    {{ Breadcrumbs::render('complaintReviewView',$ticket->complaintChecklist->complaintEnquiry) }}
  </div>
</div>

<div class="clearfix" ></div>

<div class="row" >
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <h4>
          @if($workOrderDetail->complaint_assign_status < 3)
          <a href="{{route('ServiceReportStatusLandlord',[$ticket->complaint_service_report_id,5])}}" title="Send For Landlord Approval" class="btn btn-circle btn-primary align-right send_landlord_approval">
            Send For Landlord Approval
          </a>
          @endif
          <!-- resh -->
          @if($ticket->complaintChecklist->work_flow_processes_code == 703)
          @if($workOrderDetail->complaint_assign_status < 6 )
         
         <button title="Approve" type="button" class="btn btn-circle btn-primary align-right service_report_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->complaint_service_report_id}}" data-status="1" data-backdrop="static" data-keyboard="false">@if($workOrderDetail->complaint_assign_status < 3) Approve  @else Approve  on behalf of landlord @endif</button>

         <button title="Reject" type="button" class="btn btn-circle btn-danger align-right service_report_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->complaint_service_report_id}}" data-status="6" data-backdrop="static" data-keyboard="false">@if($workOrderDetail->complaint_assign_status < 3) Reject  @else Reject  on behalf of landlord  @endif</button>
          @endif 
          @endif

          @if($ticket->complaintChecklist->work_flow_processes_code == 702)
          @if($workOrderDetail->complaint_assign_status < 6 )
          <button title="Reject" type="button" class="btn btn-circle btn-danger align-right service_report_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->complaint_service_report_id}}" data-status="6" data-backdrop="static" data-keyboard="false">@if($workOrderDetail->complaint_assign_status < 3) Reject @else Reject on behalf of landlord  @endif</button>

         <button title="Approve" type="button" class="btn btn-circle btn-primary align-right service_report_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->complaint_service_report_id}}" data-status="1" data-backdrop="static" data-keyboard="false">@if($workOrderDetail->complaint_assign_status < 3) Approve @else Approve on behalf of landlord @endif</button>
          @endif 
          @endif 
          <!-- resh -->
          <div class="clr"></div>
        </h4>
      </div>
      <form action="#" id="form_sample_2" class="form-horizontal">
        <div class="card-body row"> 
          
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> Complaint No  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_no}}</span></div>
            </div>
          </div>


          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> Category </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->category_name}}</span></div>
            </div>
          </div>



          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> Complaint Date </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_date->format('d/m/Y')}}</span></div>
            </div>
          </div>
           <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b> Complainer Name  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complainer_name}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Complainer Contact No</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_mob_no}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Building</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->building->building_name}}</span></div>
            </div>
          </div>
          @if(isset($ticket->complaintChecklist->complaintEnquiry->unit_id))
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Unit  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->Unit->unit_code}}</span></div>
            </div>
          </div>
          @endif
          
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Location </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->location->locations_name}}</span></div>
            </div>
          </div>
          
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Status</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintServiceReport->ServiceReportStatusName}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Description</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ticket->complaintServiceReport->complaint_assign_note}}</span></div>

            </div>
          </div>


      @if(count($ticket->complaintChecklist->complaintEnquiry->preferredTime) > 0)
          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Preferred Time </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->preferredTime->implode('pre_time',', ')}}</span></div>
                </div>
          </div>
     @endif  



          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Assigned to  </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>
                @if($ticket->complaintChecklist->work_flow_processes_code == 702)
                @if($ticket->complaintChecklist->assigned_to)
                @if($ticket->complaintChecklist->assigned_to_type == 0)
                {{'Technical Head'}}
                @else
                {{'Sub Contractor'}}
                @endif
                @endif
                @else
                @if($ticket->complaintChecklist->sub_assigned_to)
                {{$ticket->complaintChecklist->assignedPerson->employee->employee_name ?? $ticket->complaintChecklist->assignedPerson->username}}
                @endif
                @endif
              </span></div>
            </div>
          </div> 
        </div>
      </form>
    </div>
  </div>
  
  <!-- ticket tabkle-->
  <div class="col-md-12">
    <div class="card-box">
      <div class="card-head">
        <header>Tickets</header>
      </div>
      
      <div class="card-body">
        <table class="table display product-overview mb-30" id="">
          <thead>
            <tr>
              <th>Ticket</th>
              <th>Category</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($checklists as $checklist)
            
            <tr>
              <td>{{$checklist->complaint_ticket_no}}</td>
              <td>{{$checklist->work->works_code}} </td>
              <td>{{$checklist->checklist_desc}}</td>

            </tr>                     
            @empty
            <tr>
              <td colspan="3" >
                <p>No Record</p>
              </td>
            </tr>
            @endforelse    
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!--ends -->
  <!-- start note -->
  <div class="col-sm-12">
    <div class="card-box">
      <div class="add-note-section">
        <form autocomplete="off" action="{{route('storeServiceReportNote')}}" method="POST" id="service_report_note_form" class="form-horizontal sales_note_modal" enctype="multipart/form-data" data-toggle="validator">
          {{csrf_field()}}
          <input type="hidden" name="service_report_id" id="service_report_id" value="{{$ticket->complaint_service_report_id}}">
          <div class="col-sm-12">
            <div class="form-group">
              <label for="simpleFormEmail">Note</label>
              <textarea class="form-control" rows="2" required name="report_notes" placeholder="Enter Note"></textarea>
            </div>
          </div>
          <div class="col-sm-2"><button type="submit" class="btn btn-primary close_note">Save</button></div>
        </form>
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
                  <td>{{$note->ServiceReportStageName}} </td>
                  <td>{{$note->createdBy->employee->employee_name ?? $note->createdBy->username}} </td>
                  <td>{{$note->created_at->format('d/m/Y h:i:s')}}</td>
                </tr>                     
                @empty
                <tr>
                  <td colspan="3" >
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
<!-- end note-->
<!--complaint category -->

<!-- ends-->
<!-- ticket tabkle-->

<!--ends -->
<!-- tenant name-->

<!--ends -->
</div>

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<!-- @include('maintenance::complaint_js') -->
<script>
  $(document).ready(function() {

    /***********************************************************/
    $(".sales_note_modal").validate({
    submitHandler: function(form) {
          $('.close_note').prop('disabled', true);
          form.submit();
     }
   });
    /***********************************************************/
    $("#service_report_note_form").validate();
    /***********************************************************/
    $("#myModal").on("hidden.bs.modal", function(){
      $("#myModal").html("");
      $(this).removeData('bs.modal');
    });

    /***********************************************************/
      $(document).on('click', '.service_report_modal',function(e) {        
            var complaint_service_report_id = $(this).attr('data-id');
            var status = $(this).attr('data-status');

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('ServiceReportStatusLandlordModal')}}", // This is the url we gave in the route
                data: {'complaint_service_report_id' : complaint_service_report_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
    });
    /***********************************************************/
  });
</script>
@endsection