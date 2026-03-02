@extends('layouts.plms-app')


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Complaint View</div>
    </div>
    {{ Breadcrumbs::render('complaintUnassigned.view',$ComplaintEnquiries) }}
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
            <!-- <div class="card-head">
              <header>View Building Type</header>
            </div> -->
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
                  <div class="col-md-5"><b>Complaint Date </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->complaint_date->format('d/m/Y')??''}}</span></div>
                </div>
              </div>
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complainer Name</b></div>
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
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->building->building_name}}</span></div>
                </div>
              </div> 
              @if(isset($ComplaintEnquiries->unit_id))
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->Unit->unit_code}}</span></div>
                </div>
              </div>
              @endif
              @if(isset($ComplaintEnquiries->tenant_id))
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Tenant </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_name}}</span></div>
                </div>
              </div>
              @endif
              @if(isset($ComplaintEnquiries->tenant_id))
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Tenant Mobile No</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->tenant_contact_no}}</span></div>
                </div>
              </div>
              @endif
              @if(isset($ComplaintEnquiries->tenant_id))
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Resident Card ID </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->tenant->resident_id}}</span></div>
                </div>
              </div> 
              @endif
               @if(isset($ComplaintEnquiries->tenant_id))
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
                  <div class="col-md-5"><b>Location</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->location->locations_name}}</span></div>
                </div>
              </div>
              
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Way No</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->way_no}}</span></div>
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
              <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Created By</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ComplaintEnquiries->createdBy->employee->employee_name}}</span></div>
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
            Tickets
            @can('assign')
            <button type="button" class="btn btn-circle btn-primary groupAssign align-right"  data-toggle="modal" data-target="#myModal" data-id="{{$ComplaintEnquiries->id}}" data-backdrop="static" data-keyboard="false">Group Assign</button>
            @endcan
          </h4>
          
          <table class="table display product-overview mb-30" id="dtBasicExample">
            <thead>
              <tr>
                <th><input type = "checkbox" id = "master" 
                 class = "mdl-switch__input "></th>
                 <th>Ticket</th>
                 <th>Category</th>
                 <th>Description</th>
                 <th>Assigned To</th>
                 <th>Ticket Status</th>
                 <th>Action</th>
                 
               </tr>
             </thead>
             <tbody>
              @forelse ($tickets as $ticket)
              <tr>
                <td>
                  <input type = "checkbox" id = "switch-2" class = "mdl-switch__input sub_chk" name="groupAssign[]" value="{{$ticket->id}}" datas-id="702">
                </td>
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
                <td><span class="label {{$ticket->ticket_status_class }} label-mini">{{$ticket->ticket_status_name }}</span></td>
                <td>
                  @can('complaint_enquiries_edit') 
                  @if($ticket->ticket_status < 1 )
                  <button type="button" class="btn btn-tbl-edit btn-xs checklistEdit" dataa-id="{{$ticket->id}}" title="Edit" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-pencil"></i>
                  </button>  
                  @endif                                                 
                  @endcan
				  @can('assign')  
                  @if($ticket->ticket_status !=4)
                  <button type="button" class="btn btn-tbl-general btn-xs assignLead"  data-toggle="modal" data-target="#myModal" data-id="{{$ComplaintEnquiries->id}}" datas-id = "{{$ticket->id}}" datass-id="702" title="Assign" data-backdrop="static" data-keyboard="false">  <i class="fa fa-user" aria-hidden="true"></i></button>
                  @endif
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
  <div class="modal" id="myModal">

  </div>
  @endsection
  @section('scripts')
  @include('maintenance::complaint_js')
  <script type="text/javascript">
    $(document).ready(function() {

      $('.groupAssign').on('click', function(e) { 

        var allVals = []; 
        var workflow;  
        $(".sub_chk:checked").each(function() {  
          allVals.push($(this).attr('value'));
          workflow =$(this).attr('datas-id');
        });  

        if(allVals.length <=0)  
        {  
          alert("Please Select Atleast One Ticket..!");  
          return false;
        }  else {  

          var complaint_id = $(this).attr('data-id');
          
            //alert(allVals); return false;
            $('input:hidden[name=enquiryIds]').val(allVals);

            $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('groupAssignModal')}}", // This is the url we gave in the route
                    data: {'complaint_id' : complaint_id,'workflow' : workflow,'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    success: function(response){ // What to do if we succeed
                      $("#myModal").html(response); 
                    },
                  });
            return true;  
          }  
        });
      /**********************************************************************************/
      
    });
  </script>
  @endsection
