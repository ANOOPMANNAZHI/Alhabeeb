@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Closed Ticket</div>
    </div>
    {{ Breadcrumbs::render('complaintClosedView',$ComplaintEnquiries) }}
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <h4>
          @if(auth()->user()->hasRole(['maintenance_coordinator','super_admin']) && auth()->user()->can('close') && $ComplaintEnquiries->complaint_status != 2)
          <button title="Close" type="button" class="btn btn-circle btn-primary align-right complaint_close_modal" data-toggle="modal" data-target="#myModal" data-id = "{{$ComplaintEnquiries->id}}" datas-id="" data-backdrop="static" data-keyboard="false">
            Close
          </button>
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
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_no??''}}</span></div>
          </div>
        </div> 
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Category </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->category_name??''}}</span></div>
          </div>
        </div>
        
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complaint Date</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_date->format('d/m/Y')??''}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b> Complainer Name  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complainer_name}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Complainer Contact No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_mob_no}}</span></div>
          </div>
        </div>
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Building  </b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->building->building_name??''}}</span></div>
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
            <div class="col-md-5"><b>Tenant Mobile No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_contact_no??''}}</span></div>
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
            <div class="col-md-5"><b>Location</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->location->locations_name??''}}</span></div>
          </div>
        </div>

        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Way No</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->way_no??''}}</span></div>
          </div>
        </div>

        @if(count($ComplaintEnquiries->preferredTime) > 0)
        <div class="col-md-6 p-t-10">
          <div class="row">
            <div class="col-md-5"><b>Preferred Time</b></div>
            <div class="col-md-1 s-clm">:</div>
            <div class="col-md-6"><span>{{$ComplaintEnquiries->preferredTime->implode('pre_time',', ')}}</span></div>
          </div>
        </div>
        @endif



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

          @php
          if(!$ticket->assigned_to &&   !auth()->user()->hasRole(['maintenance_coordinator']) && auth()->user()->hasRole(['maintenance_supervisor','maintenance_engineer']))
          continue; 

          if($ticket->assigned_to &&   !auth()->user()->hasRole(['maintenance_coordinator']) && auth()->user()->hasRole(['maintenance_supervisor','maintenance_engineer'])){

          if($ticket->assigned_to != auth()->user()->id) 
          continue; 

        }
        if($ticket->sub_assigned_to  && auth()->user()->hasRole('technician')  && ($ticket->sub_assigned_to != auth()->user()->id) ){
        continue; 
      }

      @endphp
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
          @can('service_report_view') 
          <button type="submit" class="btn btn-tbl-view btn-xs view_close_detail" data-toggle="modal" data-target="#myModal" data-id = "{{$ticket->id}}" title="View" data-backdrop="static" data-keyboard="false">
            <i class="fa fa-eye"></i>
          </button>
          @endcan
          @can('generate_service_report')              
          <a title="Edit Service Report" class="btn btn-tbl-view btn-xs"  href="{{route('technicianServiceReportClosed',[$ComplaintEnquiries->id, ($ticket->sub_assigned_to)?? 0,'serviceReportTechnician'])}}" >
            <i class="fa fa-cog"></i>
          </a>
          @endcan
                  <!-- <a href="{{route('closedDetailedView',$ticket->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                      <i class="fa fa-eye "></i>
                    </a> -->
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
