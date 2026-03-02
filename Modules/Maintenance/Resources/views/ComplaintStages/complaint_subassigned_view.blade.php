@extends('layouts.plms-app')
 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Sub Assigned View</div>
        </div>
        {{ Breadcrumbs::render('complaintSubAssigned.view',$ComplaintEnquiries,$ComplaintEnquiries->sub_assigned_to) }}
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
                  <div class="col-md-6">{{$ComplaintEnquiries->complaint_no??''}}<span></span></div>
                </div>
            </div>
          	<div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Category  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6">{{$ComplaintEnquiries->category_name??''}}<span></span></div>
                </div>
          	</div>

            
            <div class="col-md-6 p-t-10">
              <div class="row">
                <div class="col-md-5"><b> Complaint Date   </b></div>
                <div class="col-md-1 s-clm">:</div>
                <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_date->format('d/m/Y')}}</span></div>
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
                  <div class="col-md-5"><b>Building</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->building->building_name}}</span></div>
                </div>
          </div>
          @if($ComplaintEnquiries->unit_id)
          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->Unit->unit_code}}</span></div>
                </div>
         	 </div> 
           @endif
           @if($ComplaintEnquiries->tenant_id)
           <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Tenant </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_name}}</span></div>
            </div>
          </div>
          @endif
           @if($ComplaintEnquiries->tenant_id)
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Tenant Mobile No</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_contact_no}}</span></div>
            </div>
          </div>
          @endif
           @if($ComplaintEnquiries->tenant_id)
         	<div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Resident Card ID </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->resident_id}}</span></div>
            </div>
          </div> 
          @endif
           @if($ComplaintEnquiries->tenant_id)
          <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Occupant Name </b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>@if($ComplaintEnquiries->occupant_id){{$ComplaintEnquiries->occupant->occupant_name}}@endif</span></div>
            </div>
          </div> 
          @endif
          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->location->locations_name}}</span></div>
                </div>
          </div>
           <div class="col-md-6 p-t-10">
            <div class="row">
              <div class="col-md-5"><b>Way No</b></div>
              <div class="col-md-1 s-clm">:</div>
              <div class="col-md-6"><span>{{$ComplaintEnquiries->way_no??''}}</span></div>
            </div>
          </div>
          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Description </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$notes}}</span></div>
                </div>
          </div>	


      @if(count($ComplaintEnquiries->preferredTime) > 0)
          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Preferred Time </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->preferredTime->implode('pre_time',', ')}}</span></div>
                </div>
          </div>
     @endif  


          <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Assigned To</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                    @if(isset($assigned->sub_assigned_to))
                      {{$assigned->subAssignedPerson->employee->employee_name ?? $assigned->subAssignedPerson->username}}
                    @endif</span></div>
                </div>
          </div>
          </div>
            </form>
        </div>
    </div>
</div>
<div class="row ">
<div class="col-sm-12 ">
	
  @can('generate_service_report')
  @if($service_report == null)
   <a title="Check In" href="{{route('checkIn',[$ComplaintEnquiries->id,$assigned->sub_assigned_to])}}" class="btn btn-circle btn-primary float-right" >Check In</a>
  @else
    <a title="Service Report" href="{{route('technicianServiceReport',[$ComplaintEnquiries->id,$assigned->sub_assigned_to])}}" class="btn btn-circle btn-primary float-right" >Service Report</a>
  @endif
  @endcan
  </div>
</div>

  <!-- ticket tabkle-->
  <div class="row margin-20">
    <div class="col-sm-12">
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
                    @forelse ($tickets as $ticket)
                    <tr>                        
                        <td>{{$ticket->complaint_ticket_no}}</td>
                        <td>{{$ticket->work->works_code}}</td>                            
                        <td>{{$ticket->checklist_desc}}</td>                       
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
    <!--ends -->
@endsection
@section('scripts')
@include('maintenance::complaint_js')
<script type="text/javascript">
$(document).ready(function(){

 
/**************************************************************************/
    
});
  
</script>
@endsection
