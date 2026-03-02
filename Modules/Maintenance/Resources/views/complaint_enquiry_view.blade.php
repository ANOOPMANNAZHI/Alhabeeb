@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">View Complaint</div>
    </div>
    {{ Breadcrumbs::render('complaint.show') }}
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">

      <div class="card-head">
        <h4>
         @can('complaint_enquiries_edit') 
         @if($complaintEnquiry->complaint_status == 0)
         <a title="Edit" href="{{route('complaint.edit',$complaintEnquiry->id)}}" class="btn btn-circle btn-primary  align-right" title="Edit">
           Edit
         </a>  
         @endif                                                
         @endcan 
         <div class="clr"></div>
       </h4>
     </div>
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
            <form action="#" id="form_sample_2" class="form-horizontal">
              <div class="card-body row"> 


               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Complaint No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->complaint_no}}</span></div>
                </div>
              </div> 

               <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b> Category  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span> {{$complaintEnquiry->category_name}} </span></div>
                </div>
              </div>

              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complainer Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->complainer_name}}</span></div>
                </div>
              </div> 
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complainer Mobile No  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->complaint_mob_no}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->location->locations_name}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Building</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->building->building_name}}</span></div>
                </div>
              </div>
              @if(isset($complaintEnquiry->unit_id))
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->Unit->unit_code}}</span></div>
                </div>
              </div>
              @endif
              @if(count($complaintEnquiry->preferredTime) > 0)
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Preferred Time </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$complaintEnquiry->preferredTime->implode('pre_time',', ')}}</span></div>
                </div>
              </div>
              @endif
         	 <!-- <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complainer Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
         	 </div>
         	 <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complainer Name  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span></span></div>
                </div>
              </div> -->      
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
            Tickets
          </h4>
          <div class="table-wrap">
            <div class="table-responsive">   
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                  <tr>
                    <th>Ticket</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Supervisor</th>
                    <th>Technician</th>
                    <th>Work Order Des</th>
                    <th>Ticket Status</th>
                    <th>Action</th>

                  </tr>
                </thead>
                <tbody>
                  @forelse ($sub_complaints as $sub_complaint)

                  @php
                  if(!$sub_complaint->assigned_to &&   !auth()->user()->hasRole(['maintenance_coordinator']) && auth()->user()->hasRole(['maintenance_supervisor','maintenance_engineer']))
                  continue; 

                  if($sub_complaint->assigned_to &&   !auth()->user()->hasRole(['maintenance_coordinator']) && auth()->user()->hasRole(['maintenance_supervisor','maintenance_engineer'])){

                  if($sub_complaint->assigned_to != auth()->user()->id) 
                  continue; 

                }
                if($sub_complaint->sub_assigned_to  && auth()->user()->hasRole('technician')  && ($sub_complaint->sub_assigned_to != auth()->user()->id) ){
                continue; 
              }

              @endphp

              <tr>
                <td>{{$sub_complaint->complaint_ticket_no}}</td>
                <td>{{$sub_complaint->work->works_code}}</td>                            
                <td>{{$sub_complaint->checklist_desc}}</td>
                <td>
                 @if(empty($sub_complaint->assigned_to_type))
                 @if($sub_complaint->assigned_to)
                 {{$sub_complaint->assignedPerson->employee->employee_name ?? $sub_complaint->assignedPerson->username}}
                 @endif
                 @else
                 @if($sub_complaint->assigned_to)
                 {{$sub_complaint->assignedContractor->vendor_name}}
                 @endif

                 @endif
               </td>
               <td>@if($sub_complaint->sub_assigned_to){{$sub_complaint->subAssignedPerson->employee->employee_name ?? $sub_complaint->subAssignedPerson->username}}@endif</td>
               <td>@if($sub_complaint->complaintServiceReport)
                {{$sub_complaint->complaintServiceReport->complaintServiceReport->complaint_assign_note}}
                @endif</td>
                <td><span class="label {{$sub_complaint->ticket_status_class}} label-mini">{{$sub_complaint->TicketStatusName}}</span></td>
                <td>

                 @if( auth()->user()->can('complaint_enquiries_edit') && $sub_complaint->ticket_status < 1 && !auth()->user()->hasRole('technician'))                        
                 <button type="button" class="btn btn-tbl-edit btn-xs checklistEdit" dataa-id="{{$sub_complaint->id}}" title="Edit" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">
                  <i class="fa fa-pencil"></i>
                </button>  
                @endif                                                 

                @can('service_report_view') 
                @if($sub_complaint->complaintServiceReport)
                <a title="View Service Report" class="btn btn-tbl-view btn-xs view_close_detail" data-toggle="modal" data-target="#myModal" data-id="{{$sub_complaint->id}}" title="View" href="{{route('closedDetailedView',[$sub_complaint->id,'closedDetailedView'])}}" data-backdrop="static" data-keyboard="false">
                  <i class="fa fa-cog"></i>
                </a>@endif
                @endcan
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
