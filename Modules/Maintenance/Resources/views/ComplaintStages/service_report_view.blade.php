@extends('layouts.plms-app')
@section('css')  
<!-- data tables -->
<!-- <link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}"> -->
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<style type="text/css">
/* Some CSS styling */
#sketchpadapp {
    /* Prevent nearby text being highlighted when accidentally dragging mouse outside confines of the canvas */
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.leftside {
    float:left;
    width:220px;
    height:285px;
    background-color:#def;
    padding:10px;
    border-radius:4px;
}
.rightside {
    float:left;
    margin-left:10px;
}
#sketchpad {
    float:left;
    border:2px solid #888;
    border-radius:4px;
    position:relative; /* Necessary for correct mouse co-ords in Firefox */
}
#clearbutton {
    font-size: 15px;
    padding: 10px;
    -webkit-appearance: none;
    background: #eee;
    border: 1px solid #888;
}

</style>
@endsection

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Service Report</div>
        </div>
        {{ Breadcrumbs::render('technicianServiceReport',$ticket,$ticket->sub_assign_to) }}
    </div>
</div>
<div class="row" >
    <div class="col-sm-12">
        <div class="card-box">
			@if($ticket->complaintServiceReport->complaint_assign_status == 3)
           <div class="card-head">
              <header>{{$ticket->complaintChecklist->complaintEnquiry->complainer_name}}</header>
            </div> 
               @endif
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
                  <div class="col-md-5"><b>Assigned to  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>
                    @if($ticket->complaintChecklist->sub_assigned_to)
                      {{$ticket->complaintChecklist->subAssignedPerson->username}}
                    @endif
                  </span></div>
                </div>
           </div> 
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Unit  </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->Unit->unit_code}}</span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Complaint Date</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->complaint_date->format('d/m/Y')}}</span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Location </b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->location->locations_name}}</span></div>
                </div>
           </div>
           <div class="col-md-6 p-t-10">
                <div class="row">
                  <div class="col-md-5"><b>Building</b></div>
                  <div class="col-md-1 s-clm">:</div>
                  <div class="col-md-6"><span>{{$ticket->complaintChecklist->complaintEnquiry->building->building_name}}</span></div>
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
		<div class="card card-box salesLeadBox">
		  <div class="card-head">
			<div class="col"><h4>Note</h4></div>
		  </div>
			<div class="card-body">
			   <div class="dataSearchBox">
				<div class="add-note-section">
					<div class="col-sm-12">
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
				</div>
            </div>
        </div>
      </div>
    </div>
    <!-- end note-->
   
     <!-- ticket tabkle-->
    <div class="col-md-12">
    <div class="card-box">
	 <div class="card-head">
		<div class="col"><h4>Item</h4></div>
	  </div>
      <div class="card-body">
      <table class="table display product-overview mb-30" id="">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                       
                    </tr>
                </thead>
                <tbody>
                
                 @forelse ($complaintServiceReportInv as $item)
              
                  <tr>
                      <td>{{$item->inventory->inventories_name}} </td>
                      <td>{{$item->quantity}}</td>
                     
                  </tr>                     
                  @empty
                  <tr>
                      <td colspan="2" >
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
    
@if($images->count()>0)
  <div class="col-sm-12">
    <div class="card-box">
      <div class="card-head">
        <header>Gallery</header>
      </div>
      <div class="card-body row">
    <div id="aniimated-thumbnials" class="list-unstyled  clearfix">
          @foreach ($images as $image) 
              <div class="balance m-b-20"> 
                <a href="{{asset('storage/app/'.$image->image_path_file_name)}}" data-sub-html="Images">
                 <img class="img-fluid img-thumbnail" src="{{asset('storage/app/'.$image->image_path_thumbnail)}}" alt="" title =""> </a> </div>
          @endforeach    
          </div>
      </div>
    </div>
  </div>
@endif
</div>

<div class="modal" id="myModal">

</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection

