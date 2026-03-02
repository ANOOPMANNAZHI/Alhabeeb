@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Legal Cases Approval View</div>
    </div>

    {{ Breadcrumbs::render('plmsApprovalShow',$plmsApproval) }} 

  </div>
</div>
<div class="row">
  <div class="col-sm-12">
  </div>
</div>
<div class="row">
  <div class="col">
    <div class="card card-box salesLeadBox">
     <div class="card-head">
      <h4>
        @can('plms_refer_back')
        @if($plmsApproval->tenantContract->status !=0)
        <button type="button" class="btn btn-circle btn-warning align-right referback" data-id="{{$plmsApproval->id}}"  data-key="RFRBK" data-flow-id="801"  data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Refer Back</button>
        @endif
        @endcan
        @can('plms_close')
        @if($plmsApproval->tenantContract->status !=0)
        <button type="button" class="btn btn-circle btn-danger align-right close_type" title="Close"  data-id="{{$plmsApproval->id}}" data-flow-id="801">Close</button>
        @endif
        @endcan
        @can('plms_approve')
        @if($plmsApproval->tenantContract->status !=0)
        <button type="button" class="btn btn-circle btn-primary align-right approve" data-id="{{$plmsApproval->id}}"  data-key="APRV" data-flow-id="801" data-status="0" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false">Approve</button>
        @endif
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
<div class="card card-box salesSearchBox">
  
  <div class="dataSearchBox">
    <div class="card-body row">
      @if(isset($plmsApproval->tenantContract->tenant_contract_no))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Agreement No :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_no}}</span></h5>
        </div>
      </div>
      @endif
      @if(isset($plmsApproval->tenantContract->created_at)) 
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Agreement Date  :  </b><span>{{$plmsApproval->tenantContract->created_at->format('d/m/Y')}}</span></h5>
        </div>
      </div>
      @endif
      @if(isset($plmsApproval->workFlowProcess->work_flow_processes_name))
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Legal Status :  </b>
           <span>{{$plmsApproval->workFlowProcess->work_flow_processes_name}}</span>
         </h5>
       </div>
     </div> 
     @endif
   </div>
 </div>
 <div class="sub-head">Building Details</div>
 <div class="dataSearchBox">    
  <div class="card-body row">

    @if(isset($plmsApproval->building_id))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Building Name :  </b><span>{{$plmsApproval->building->building_name}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($plmsApproval->building_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Building Code :  </b><span>{{$plmsApproval->building->building_code}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($plmsApproval->unit_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit No :  </b><span>{{$plmsApproval->unit->unit_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->unit_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Code :  </b><span>{{$plmsApproval->unit->unit_code}}</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$plmsApproval->tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$plmsApproval->tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->unit_usage))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$plmsApproval->tenantContract->unit_usage}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Name :  </b><span>{{$plmsApproval->tenantContract->occupant->occupant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Mob No :  </b><span>{{$plmsApproval->tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Email :  </b><span>{{$plmsApproval->tenantContract->occupant->occupant_email}}</span></h5>
 </div>
</div> 
@endif
</div>
</div>
<div class="sub-head">Contract Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">
    @if(isset($plmsApproval->tenantContract->tenant_contract_start_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Start Date :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($plmsApproval->tenantContract->tenant_contract_effective_date))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Effective Date :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($plmsApproval->tenantContract->tenant_contract_valid_to_date))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_duration_countdown))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Duration :  </b><span>
    @php 
    $duration = explode('-',$plmsApproval->tenantContract->tenant_contract_duration_countdown)
    @endphp


    {{$duration[0]}} Year
    {{$duration[1]}} Month
    {{$duration[2]}} Days


  </span></h5>
</div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_value))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{ numberFormat($plmsApproval->tenantContract->tenant_contract_value) }} OMR</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_rent))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent(PM) :  </b><span>{{ numberFormat($plmsApproval->tenantContract->tenant_contract_rent) }} OMR</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->vaccant_date))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Vacant Since :  </b><span>{{ numberFormat($plmsApproval->tenantContract->vaccant_date) }} OMR</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->last_rent))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent Paid by Prevoius Tenant :  </b><span>{{ numberFormat($plmsApproval->tenantContract->last_rent) }} OMR</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_muncipality_agr_no))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_muncipality_agr_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_electric_water))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$plmsApproval->tenantContract->tenant_contract_electric_water}}</span></h5>
 </div>
</div> 
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_registered_in))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Registered In :  </b><span>{{$plmsApproval->tenantContract->tenant_contract_registered_in}}</span></h5>
 </div>
</div>
@endif
@if(isset($plmsApproval->tenantContract->tenant_contract_payment_type))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Term :  </b><span>{{$plmsApproval->tenantContract->TenantContractPaymentName}}</span></h5>
 </div>
</div>
@endif
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>PDC  :  </b><span>{{($plmsApproval->tenantContract->pdc_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Invoice Generate:  </b><span>{{($plmsApproval->tenantContract->invoice_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Registered In Municipality :  </b><span>{{($plmsApproval->tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
 </div>
</div> 
</div>
</div>
<div class="sub-head">Payment Details</div>
<div class="dataSearchBox">    
  <div class="card-body row">

    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($plmsApproval->tenantContract->tenant_contract_deposit_amt)?numberFormat($plmsApproval->tenantContract->tenant_contract_deposit_amt)."OMR":"NA"}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($plmsApproval->tenantContract->tenant_contract_guarantee_cheque_details)?$plmsApproval->tenantContract->tenant_contract_guarantee_cheque_details:"NA"}} </span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt No:  </b><span>{{$plmsApproval->tenantContract->tenant_contract_receipt_no ?? 'NA' }}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt date:  </b><span>{{isset($plmsApproval->tenantContract->tenant_contract_receipt_date)?$plmsApproval->tenantContract->tenant_contract_receipt_date->format('d/m/Y'):"NA"}}</span></h5>
      </div>
    </div>

    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($plmsApproval->tenantContract->tenant_contract_receipt_amt)?numberFormat($plmsApproval->tenantContract->tenant_contract_receipt_amt)."OMR":"NA"}} </span></h5>
      </div>
    </div>

  </div>
</div>

<div class="sub-head">Document Upload   </div>
<div class="dataSearchBox">    
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Document :  </b></h5><span>
          @if(!empty($plmsApproval->legalDocument)) 
          @foreach ($plmsApproval->legalDocument as $doc) 
          <a href="{{asset('storage/app/'.$doc->legal_documents_file_name)}}" target="_blank">
             <i class="fa fa-file" aria-hidden="true"></i>&nbsp;
            {{$doc->legal_documents_name}}  
          </a>
          <br/>
          @endforeach    
          @endif
        </span>
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
  });
</script>
@endsection
