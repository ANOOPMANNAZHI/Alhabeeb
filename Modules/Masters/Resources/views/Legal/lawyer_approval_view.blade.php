@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Case Details</div>
    </div>

    {{ Breadcrumbs::render('lawyerApprovalShow',$lawyerApproval) }} 

  </div>
</div>
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <h4>
     @can('lawyer_close')
     <button type="button" class="btn btn-circle btn-danger align-right close_type" title="Close"  data-id="{{$lawyerApproval->id}}" data-flow-id="801">Close</button>
     @endcan
     @can('lawyer_refer_back')
     <button type="button" class="btn btn-circle btn-warning align-right referback" data-id="{{$lawyerApproval->id}}" title="Refer Back" alt="Refer Back" data-key="RFRBK" data-flow-id="801"  data-status="1" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Refer Back</button>
     @endcan
     @can('lawyer_approve')
     <button type="button" class="btn btn-circle btn-primary align-right approve" data-id="{{$lawyerApproval->id}}" title="Approve" alt="Approve" data-key="APRV" data-flow-id="802" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Approve</button>
     @endcan
     <div class="clr"></div>
   </h4>
 </div>
 <div class="card-head">
  <div class="col"><h4>Stage Note</h4></div>
</div>
<div class="card-body">
 <div class="col">
  <div class="row">

    <div class="col leadInformation">
     <div class="table-responsive1">
      <table class="table" >
        <thead>
          <tr style="background: #f5f5f5;">
            <th>Stage Note</th>
            <th>Stage</th>
            <th>User</th>
            <th>Date Time</th>
          </tr>
        </thead>
        <tbody>

          @forelse ($notes as $note)

          @if($note->note!="") 
          <tr>
            <td> {{$note->note}}</td>
            <td>{{$note->workFlowProcess->work_flow_processes_name}}</td>
            <td>{{$note->createdBy->username}}</td>
            <td>{{$note->created_at->format('d/m/Y h:m A')}}</td>
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
<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
     
      <div class="sub-head">Building Details</div>
      <div class="dataSearchBox">    
        <div class="card-body row">

          @if(isset($lawyerApproval->building_id))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Building Name :  </b><span>{{$lawyerApproval->building->building_name}}</span></h5>
           </div>
         </div> 
         @endif
         @if(isset($lawyerApproval->building_id))
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Code :  </b><span>{{$lawyerApproval->building->building_code}}</span></h5>
         </div>
       </div>
       @endif
       @if(isset($lawyerApproval->building_id))
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Landlord :  </b><span>{{$lawyerApproval->building->vendor->vendor_name}}</span></h5>
       </div>
     </div>
     @endif
     @if(isset($lawyerApproval->building_id))
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>ARE :  </b><span>{{$lawyerApproval->building->vendor->vendor_name}}</span></h5>
     </div>
   </div>
   @endif
   @if(isset($lawyerApproval->building_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Way No :  </b><span>{{$lawyerApproval->building->plot_no}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($lawyerApproval->building_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Block No :  </b><span>{{$lawyerApproval->building->block_number}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Plot No :  </b><span>{{$lawyerApproval->building->plot_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Location :  </b><span>{{$lawyerApproval->building->location->locations_name}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->building_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Location No :  </b><span>{{$lawyerApproval->building->location->locations_code}}</span></h5>
 </div>
</div>
@endif
</div>
</div>
<div class="sub-head">Unit Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">
    @if(isset($lawyerApproval->unit_id))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Unit No :  </b><span>{{$lawyerApproval->unit->unit_no}}</span></h5>
     </div>
   </div>
   @endif
   @if(isset($lawyerApproval->unit_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Unit Type :  </b><span>{{$lawyerApproval->unit->unit->unit_types_name}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($lawyerApproval->unit_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Floor No :  </b><span>{{$lawyerApproval->unit->unit_floor}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->tenantContract->unit_usage))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$lawyerApproval->tenantContract->unit_usage}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$lawyerApproval->tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($lawyerApproval->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$lawyerApproval->tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Name :  </b><span>{{$lawyerApproval->tenantContract->occupant->occupant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($lawyerApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Mob No :  </b><span>{{$lawyerApproval->tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Email :  </b><span>{{$lawyerApproval->tenantContract->occupant->occupant_email}}</span></h5>
 </div>
</div> 
@endif
</div>
</div>
<div class="sub-head">Current Contract Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">

    @if(isset($lawyerApproval->tenantContract->tenant_contract_start_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Start Date :  </b><span>{{$lawyerApproval->tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($lawyerApproval->tenantContract->tenant_contract_effective_date))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Effective Date :  </b><span>{{$lawyerApproval->tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
   </div>
 </div>
 @endif 
 @if(isset($lawyerApproval->tenantContract->tenant_contract_valid_to_date))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>End Date :  </b><span>{{$lawyerApproval->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if(isset($lawyerApproval->tenantContract->tenant_contract_value))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{ numberFormat($lawyerApproval->tenantContract->tenant_contract_value) }} OMR</span></h5>
 </div>
</div> 
@endif

<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rented From :  </b><span>{{ $dmy }}</span></h5>
 </div>
</div> 

@if(isset($paidDays))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Over Due Rent Period :  </b><span>{{$overDueFrom->format('d/m/Y')}}, {{date('d/m/Y')}} ,{{ $paidDays }} Days</span></h5>
 </div>
</div> 
@endif
@if(isset($rentAmount))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Over Due Rent Amount:  </b><span>{{ numberFormat($rentAmount) }} OMR</span></h5>
 </div>
</div> 
@endif

</div>
</div>
</div>    

@if($oldContractList)
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Old Contract Details</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Old Contract No</th>
              <th>From Date</th>
              <th>To Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

           @forelse ($oldContractList as $oldContract)

           @if($oldContract->tenant_contract_no!="") 
           <tr>
            <td>{{$oldContract->tenant_contract_no}} </td>
            <td>{{$oldContract->tenant_contract_start_date->format('d/m/Y')}}</td>
            <td>{{$oldContract->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
            <td>
              <a href="{{route('legaltenantContractShow',$oldContract->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                <i class="fa fa-eye "></i>
              </a>
              <a href="{{route('legalPdcShow',$oldContract->id)}}" title="PDC" class="btn btn-tbl-general btn-xs">
                <i class="fa fa-pie-chart "></i>
              </a>
            </td>
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
@endif

</div> 

<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
<script>
  $(document).ready(function() {

    $('.approve').on('click', function(e) {        

      var action_key =  $(this).attr('data-key');
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      var are_status = $(this).attr('data-status');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       

        });
    /**********************************************************************************/
    $('.referback').on('click', function(e) {        

      var action_key =  $(this).attr('data-key');
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      var are_status = $(this).attr('data-status');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('plmsApproveReferBack')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'legal_id' : legal_id,'are_status' : are_status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
             },
           });
        return true;
        /*}else {
            return false;
          } */       
          
        });
    /**********************************************************************************/
    $('.close_type').on('click', function(e) {        
      var legal_id = $(this).attr('data-id');
      var process_flow = $(this).attr('data-flow-id');
      /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('legalCaseClose')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'legal_id' : legal_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
               // $("#myModal").html(response);
               // alert(response);
               // window.location.href = response;
               location.reload();
             },
           });
        return true;
        /*}else {
            return false;
          } */       

        });
    /**********************************************************************************/
  });
</script>
@endsection
