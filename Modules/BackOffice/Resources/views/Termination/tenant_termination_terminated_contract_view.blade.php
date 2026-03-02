@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Terminated Contract View</div>
    </div>
    {{ Breadcrumbs::render('tenantTerminatedContractView',$termination) }} 
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">

      <div class="card-head">
		  <h4>
            @if($termination->termination_type_status == 3 && empty($termination->tenantContract->tenant_penalty_invoice_amt))
                  <button title="Penalty" type="button" class="btn btn-circle btn-primary  align-right Penalty" data-toggle="modal" data-target="#myModal" data-id="{{$termination->id}}"  data-backdrop="static" data-keyboard="false">
                  Penalty
                  </button>
			  @endif
			</h4>

      </div>
           <!--Property Section  ends -->
     <div class="clearfix"></div>
     <!--Agreement Section starts -->
     <div class="card card-box salesSearchBox ">
     <div class="dataSearchBox">
      
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
           </div>
         </div> 
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Agreement Date :  </b><span>{{isset($tenantContract->created_at)?$tenantContract->created_at->format('d/m/Y'):""}}</span></h5>
         </div>
       </div> 
     </div>
     <!-- ends-->
   </div>
 </div>
 <!--Agreement Section ends -->


 <!-- Status Ribbon Starts -->
<div class="row">
  
    <div class="col-sm-12">
        <div class="panel">
            <header class="panel-heading panel-heading-blue">
                <div class="ribbon"><span>Status</span></div>
               Status </header>
            <div class="panel-body light-green">
            <div class="card-body row">

            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Tenant Status :  </b><span>{{$tenantContract->tenant->tenant_status_name}}</span></h5>
                </div>
            </div>
             <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Contract Status :  </b><span>{{$tenantContract->tenant_contract_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Municipality Registration :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Unit Status :  </b><span>{{$tenantContract->unit->vacant_status_name}}</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Key Status :  </b><span>
                    @if(!empty($tenantContract->unit->key)){{$tenantContract->unit->key->status_name}}
                    @else
                    NA
                    @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Remaining days to Expiry :  </b><span>{{$remainingDays}} Days</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b>Rent paid Up To:  </b><span>
                     @if(!empty($tenantContract->tenant_contract_last_paid_date))
                     {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
                     @else
                     NA
                     @endif</span></h5>
                </div>
            </div>
            <div class="col-lg-6 p-t-20"> 
                <div class = "txt-full-width">
                    <h5 class="details"><b> </b><span></span></h5>
                </div>
            </div>
            </div>

            </div>
        </div>
    </div>
</div>
<!-- Status Ribbon Ends -->


 <!--Building Details starts -->
<div class="card card-box salesSearchBox ">
 <div class="dataSearchBox">
   <div class="card-head">
    <header>Building Details</header>
  </div>

    <div class="card-body row">
      <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Name :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
       </div>
     </div> 
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Building Code :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
     </div>
   </div>
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Unit No :  </b><span>{{$tenantContract->unit->unit_no}}</span></h5>
   </div>
 </div>
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Code :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Name :  </b><span>{{$tenantContract->tenant->tenant_name}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Tenant Code :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Unit Usage :  </b><span>{{$tenantContract->unit_usage}}</span></h5>
 </div>
</div>
 @if($tenantContract->occupant_id)
  <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
          <h5 class="details"><b>Occupant Name :  </b><span>{{$tenantContract->occupant->occupant_name}}</span></h5>
      </div>
  </div> 
  <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
          <h5 class="details"><b>Occupant Mob No:  </b><span>{{$tenantContract->occupant->occupant_primary_contact_no?? "NA"}}</span></h5>
      </div>
  </div>
  <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
          <h5 class="details"><b>Occupant Email:  </b><span>{{$tenantContract->occupant->occupant_email ?? "NA"}}</span></h5>
      </div>
  </div>
  @endif 
</div>
</div>
</div>
<!-- ends-->
<div class="clearfix"></div>
<!--Contract Details starts -->
<div class="card card-box salesSearchBox ">
<div class="dataSearchBox">
 <div class="card-head">
  <header>Contract Details</header>
</div>
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Effective Date :  </b><span>{{$tenantContract->tenant_contract_effective_date->format('d/m/Y')}}</span></h5>
   </div>
 </div>
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Valid To :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
 </div>
</div>
@if(isset($tenantContract->tenant_contract_duration_countdown))
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
           <h5 class="details"><b>Duration :  </b><span>
            @php 
                  $duration = explode('-',$tenantContract->tenant_contract_duration_countdown)
              @endphp
             

                  {{$duration[0]}} Year
                  {{$duration[1]}} Month
                  {{$duration[2]}} Days

             
          </span></h5>
  </div>
</div>
@endif 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Contract Value :  </b><span>{{isset($tenantContract->tenant_contract_value)? numberFormat($tenantContract->tenant_contract_value)." OMR":"NA"}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent(PM) :  </b><span>{{isset($tenantContract->tenant_contract_value)? numberFormat($tenantContract->tenant_contract_rent)." OMR":"NA"}}</span></h5>

 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Vacant Since :  </b><span>{{isset($tenantContract->vaccant_date)?numberFormat($tenantContract->vaccant_date):"NA"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Rent Paid by Prevoius Tenant :  </b><span>{{isset($tenantContract->last_rent)? numberFormat($tenantContract->last_rent)." OMR":"NA"}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
      <h5 class="details"><b>Last Paid Rent:  </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? numberFormat($tenantContract->tenant_contract_last_paid_amt)." OMR":"NA"}} </span></h5>

  </div>
</div>
@if(isset($tenantContract->tenant_contract_muncipality_agr_no))
<div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
        <h5 class="details"><b>Municipality Agreement No :  </b><span>{{$tenantContract->tenant_contract_muncipality_agr_no?? "NA"}}</span></h5>
    </div>
</div>
@endif
@if(isset($tenantContract->tenant_contract_electric_water))
<div class="col-lg-6 p-t-20"> 
<div class = "txt-full-width">
  <h5 class="details"><b>Deposit Electric/Water:  </b><span>{{$tenantContract->tenant_contract_electric_water?? "NA"}}</span></h5>
</div>
</div>
@endif
@if($tenantContract->tenant_contract_registered_in)
<div class="col-lg-6 p-t-20"> 
<div class = "txt-full-width">
  <h5 class="details"><b>Contract Registered In :  </b><span>{{$tenantContract->TenantContractRegisteredInName}}</span></h5>
</div>
</div>
@endif
@if($tenantContract->tenant_contract_payment_type)
<div class="col-lg-6 p-t-20"> 
<div class = "txt-full-width">
  <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractPaymentName}}</span></h5>
</div>
</div>
@endif
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>PDC  :  </b><span>{{($tenantContract->pdc_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Invoice Generate:  </b><span>{{($tenantContract->invoice_check ==null)?"No":"Yes"}}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Registered In Municipality :  </b><span>{{($tenantContract->tenant_contract_is_reg_municipality==NULL)?"No":"Yes"}}</span></h5>
 </div>
</div> 
</div>
</div>
</div>
<!-- ends-->
<div class="clearfix"></div>
<!--Payment Details starts -->
<div class="card card-box salesSearchBox ">
<div class="dataSearchBox">
 <div class="card-head">
  <header>Payment Details</header>
</div>
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Deposit Amount :  </b><span>{{isset($tenantContract->tenant_contract_deposit_amt)?numberFormat($tenantContract->tenant_contract_deposit_amt)." OMR":"NA"}}</span></h5>
     </div>
   </div> 
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
     <h5 class="details"><b>Guarantee Check Amount :  </b><span>{{isset($tenantContract->tenant_contract_guarantee_cheque_details)?numberFormat($tenantContract->tenant_contract_guarantee_cheque_details)." OMR":"NA"}}</span></h5>
   </div>
 </div>
 <div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt No :  </b><span>{{$tenantContract->tenant_contract_receipt_no ?? 'NA' }}</span></h5>
 </div>
</div>
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt Date :  </b><span>{{isset($tenantContract->tenant_contract_receipt_date)?$tenantContract->tenant_contract_receipt_date->format('d/m/Y'):"NA"}}</span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Receipt Amount :  </b><span>{{isset($tenantContract->tenant_contract_receipt_amt)?numberFormat($tenantContract->tenant_contract_receipt_amt)." OMR":"NA"}} </span></h5>
 </div>
</div> 
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Last Rent Paid :  </b><span>{{$tenantContract->building->building_name}}</span></h5>
 </div>
</div>
@if($tenantContract->tenant_penalty_start_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Penalty Start Date:  </b><span>{{$tenantContract->tenant_penalty_start_date->format('d/m/Y')?? 'NA'}}</span></h5>
 </div>
</div>
@endif
@if($tenantContract->tenant_penalty_valid_to_date)
<div class="col-lg-6 p-t-20"> 
  <div class = "txt-full-width">
   <h5 class="details"><b>Penalty To Date :  </b><span>{{$tenantContract->tenant_penalty_valid_to_date->format('d/m/Y')?? 'NA'}}</span></h5>
 </div>
</div>
@endif
</div>
</div>
</div>
<!-- ends-->
<div class="clearfix"></div>
<!--Document Upload starts -->
@if($tenantContract->tenant_penalty_invoice_amt)
<div class="card card-box salesSearchBox ">
<div class="dataSearchBox">    
  <div class="card-head">
	<header>Penality Details</header>
  </div>  
  <div class="card-body row">
       <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Penality Start:  </b><span>{{$tenantContract->tenant_penalty_start_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Penality End:  </b><span>{{$tenantContract->tenant_penalty_valid_to_date->format('d/m/Y')}}</span></h5>
     </div>
   </div> 
   <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
       <h5 class="details"><b>Penality Amount:  </b><span>{{numberFormat($tenantContract->tenant_penalty_invoice_amt)." OMR"}}</span></h5>
     </div> 
   </div> 
    </div>
  </div>
</div>
</div>
@endif
<!--Document Details ends -->
     <div class="clearfix"></div>
     <!--Agreement Section starts -->
  <!--Penality Details  -->
<div class="card card-box salesSearchBox ">
<div class="dataSearchBox">    
  <div class="card-body row">
      <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
              <h5 class="details"><b>Document :  </b><span>
              @if(!empty($tenantContract->tenantDocument)) 
                @foreach ($tenantContract->tenantDocument  as $doc) 
                  <a href="{{asset('storage/app/'.$doc->tenant_documents_file_name)}}" target="_blank">
                      {{$doc->tenant_documents_name}}  
                  </a>
                  <br/>
                @endforeach    
    
              @endif
              </span></h5>
          </div>
      </div>
  </div>
</div>
</div>
<!--Penality Details ends -->
     <div class="clearfix"></div>
     <!--Agreement Section starts -->   
     
     
     <div class="card card-box salesSearchBox ">
     <div class="dataSearchBox">
      <div class="row">
        <div class="card-body row">
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
             <h5 class="details"><b>Contract Terminated Date :  </b><span>{{$tenantContract->terminationContract->termination_date->format('d/m/Y')?? "NA"}}</span></h5>
           </div>
         </div> 
         <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Last Rent Paid Date :  </b>
            <span>{{isset($tenantContract->tenant_contract_last_paid_date) ? $tenantContract->tenant_contract_last_paid_date->format('d/m/Y') : "NA"}}</span>
          </h5>
         </div>
       </div> 
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Last Rent Paid Amount :  </b><span>{{isset($tenantContract->tenant_contract_last_paid_amt)? number_format($tenantContract->tenant_contract_last_paid_amt, 3,",","")." OMR":"NA"}}</span></h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Deposit Refund Status :  </b><span>  {{ (count($tenantContract->depositRefund) > 0 )?  'Yes':  'No' }}  </span></h5>
         </div>
       </div>
     </div>
     <!-- ends-->
   </div>
 </div>
</div>
 <!--Agreement Section ends -->
  </div>
    </div>

    <!-- Document Show Starts -->
<div class="col-sm-12">
  <div class="card card-box salesLeadBox">
    <div class="card-head">
      <div class="col"><h4>Open For Termination Document List</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
      <div class="row">

        <div class="col leadInformation">

          <table class="table" >
            <thead>
              <tr style="background: #f5f5f5;">
                <th>Sl No</th>
                <th>Document</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($openTerminationDocument as $key=>$document)
            <tr>
                <td>{{ ++$key }}</td>
                <td><a href="{{asset('storage/app/'.$document->termination_doc)}}" target="_blank">{{$document->termination_doc_name}}</a></td>

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
<!-- Document Show Ends -->


  <div class="col-sm-12">
    <div class="card card-box salesLeadBox">
      <div class="card-head">
        <div class="col"><h4>Termination Notes</h4></div>
      </div>
      <div class="card-body">
       <div class="col">
        <div class="row">

          <div class="col leadInformation">
           <div class="table-responsive1">
            <table class="table" >
              <thead>
                <tr style="background: #f5f5f5;">
                  <th>Stage</th>
                  <th>Note</th>
                  <th>Created By</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($terminationNotes as $notes)
              
                  <tr>
                    <td>{{$notes->WorkFlowProcessesCode->work_flow_processes_name}}</td>
                    <td>{{$notes->termination_notes}}</td>
                    <td>{{$notes->createdBy->employee->employee_name??'Admin'}}</td>
                    <td>{{$notes->created_at->format('d/m/Y')}}</td>
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
</div>

</div>       
<div class="modal" id="myModal">

</div>

@endsection
@section('scripts')
 @include('backoffice::Termination.termination_js') 
@endsection
