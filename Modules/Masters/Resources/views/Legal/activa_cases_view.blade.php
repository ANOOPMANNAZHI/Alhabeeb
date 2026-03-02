@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Accepted Legal Case Details</div>
    </div>
    {{ Breadcrumbs::render('activeCasesShow',$activeCase) }} 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
     <!--Property Section  ends -->
     <div class="clearfix"></div>
     <!--Agreement Section starts -->
     <div class="dataSearchBox">
      <div class="row">
        <div class="card-body row">
          @if(isset($activeCase->tenantContract->tenant_contract_no))
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Agreement No :  </b><span>{{$activeCase->tenantContract->tenant_contract_no}}</span></h5>
           </div>
         </div>
         @endif
         @if(isset($activeCase->tenantContract->created_at)) 
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Agreement Date :  </b><span>{{$activeCase->tenantContract->created_at->format('d/m/Y')}}</span></h5>
         </div>
       </div>
       @endif
       @if(isset($activeCase->workFlowProcess->work_flow_processes_name))
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Legal Status :  </b>
           <span>{{$activeCase->workFlowProcess->work_flow_processes_name}}</span>
         </h5>
       </div>
     </div> 
     @endif
   </div>
   <!-- ends-->
 </div>
</div>
<!--Agreement Section ends -->
<div class="clearfix"></div>
<!--Building Details starts -->
<div class="dataSearchBox">
 <div class="card-head">
  <header>Building Details</header>
</div>
<div class="row">
  <div class="card-body row">
    @if(isset($activeCase->building_id))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Building Name :  </b><span>{{$activeCase->building->building_name}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($activeCase->building_id))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Building Code :  </b><span>{{$activeCase->building->building_code}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($activeCase->unit_id))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit No :  </b><span>{{$activeCase->unit->unit_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->unit_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Code :  </b><span>{{$activeCase->unit->unit_code}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$activeCase->tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$activeCase->tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->unit_usage))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$activeCase->tenantContract->unit_usage}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Name :  </b><span>{{$activeCase->tenantContract->occupant->occupant_name}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Mob No :  </b><span>{{$activeCase->tenantContract->occupant->occupant_primary_contact_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->occupant_id))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Occupant Email :  </b><span>{{$activeCase->tenantContract->occupant->occupant_email}}</span></h5>
 </div>
</div> 
@endif
</div>
<!-- ends-->
<div class="clearfix"></div>
<!--Contract Details starts -->
<div class="dataSearchBox">
 <div class="card-head">
  <header>Contract Details</header>
</div>
<div class="row">
  <div class="card-body row">
    @if(isset($activeCase->tenantContract->tenant_contract_start_date))
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Start Date :  </b><span>{{$activeCase->tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   @endif
   @if(isset($activeCase->tenantContract->tenant_contract_effective_date))
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Effective Date :  </b><span>{{$activeCase->tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
   </div>
 </div>
 @endif
 @if(isset($activeCase->tenantContract->tenant_contract_valid_to_date))
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$activeCase->tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_contract_duration_countdown))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Duration :  </b><span>
    @php 
    $duration = explode('-',$activeCase->tenantContract->tenant_contract_duration_countdown)
    @endphp


    {{$duration[0]}} Year
    {{$duration[1]}} Month
    {{$duration[2]}} Days


  </span></h5>
</div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_contract_value))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{ number_format($activeCase->tenantContract->tenant_contract_value,3) }} OMR</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_contract_rent))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent(PM) :  </b><span>{{ number_format($activeCase->tenantContract->tenant_contract_rent,3) }} OMR</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->vaccant_date))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Vacant Since :  </b><span>{{ number_format($activeCase->tenantContract->vaccant_date,3) }} OMR</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->last_rent))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent Paid by Prevoius Tenant :  </b><span>{{ number_format($activeCase->tenantContract->last_rent,3) }} OMR</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_contract_muncipality_agr_no))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$activeCase->tenantContract->tenant_contract_muncipality_agr_no}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_contract_electric_water))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$activeCase->tenantContract->tenant_contract_electric_water}}</span></h5>
 </div>
</div> 
@endif
@if(isset($activeCase->tenantContract->tenant_contract_registered_in))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Registered In :  </b><span>{{$activeCase->tenantContract->tenant_contract_registered_in}}</span></h5>
 </div>
</div>
@endif
@if(isset($activeCase->tenantContract->tenant_contract_payment_type))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Payment Term :  </b><span>{{$activeCase->tenantContract->TenantContractPaymentName}}</span></h5>
 </div>
</div>
@endif
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>PDC  :  </b><span>{{($activeCase->tenantContract->pdc_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Invoice Generate:  </b><span>{{($activeCase->tenantContract->invoice_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Registered In Municipality :  </b><span>{{($activeCase->tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
 </div>
</div> 
</div>
<!-- ends-->
<div class="clearfix"></div>
<!--Payment Details starts -->
<div class="dataSearchBox">
 <div class="card-head">
  <header>Payment Details</header>
</div>
<div class="row">
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Deposit Rent Amount :  </b><span>{{isset($activeCase->tenantContract->tenant_contract_deposit_amt)?number_format($activeCase->tenantContract->tenant_contract_deposit_amt, 3)."OMR":"NA"}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Guarantee Cheque Amount:  </b><span>{{isset($activeCase->tenantContract->tenant_contract_guarantee_cheque_details)?$activeCase->tenantContract->tenant_contract_guarantee_cheque_details:"NA"}} </span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt No:  </b><span>{{$activeCase->tenantContract->tenant_contract_receipt_no ?? 'NA' }}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt date:  </b><span>{{isset($activeCase->tenantContract->tenant_contract_receipt_date)?$activeCase->tenantContract->tenant_contract_receipt_date->format('d/m/Y'):"NA"}}</span></h5>
      </div>
    </div>

    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Receipt Amount:  </b><span>{{isset($activeCase->tenantContract->tenant_contract_receipt_amt)?number_format($activeCase->tenantContract->tenant_contract_receipt_amt, 3)."OMR":"NA"}} </span></h5>
      </div>
    </div>
  </div>
  <!-- ends-->
  <div class="clearfix"></div>
  <!--Document Upload starts -->
  <div class="dataSearchBox">
   <div class="card-head">
    <header>Document Upload</header>
  </div>
  <div class="row">
    <div class="card-body row">
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
          <h5 class="details"><b>Document :  </b><span>
            @if(!empty($activeCase->tenantContract->tenantDocument)) 
            @foreach ($activeCase->tenantContract->tenantDocument  as $doc) 
            <a href="{{ route('tenantContractDownload',[$doc->id,'tenantContract'])}}">
              {{$doc->tenant_documents_name}}  
            </a>
            @endforeach    

            @endif
          </span></h5>
        </div>
      </div>
    </div>
    <!-- ends-->
  </div>
</div>
<!--Document Details ends -->
<div class="clearfix"></div>
</div> 
<!-- ends-->
<div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>Note</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Note</th>
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
<!-- ends-->         
</div>
</div>
@endsection
@section('scripts')

@endsection