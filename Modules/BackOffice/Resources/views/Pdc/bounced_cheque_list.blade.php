@extends('layouts.plms-app')



@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }
</style>
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Cheque Bounce</div>
    </div> 
    {{ Breadcrumbs::render('tenantPdcBounce') }} 
  </div>
</div>



<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">

      <div style="overflow-x:auto;">


        <table class="table display product-overview mb-30" id="">
          <thead>
            <tr>

              <!--<th>Sl No.</th> -->
              <th>Building Name</th>
              <th>Unit No</th>
              <th>Tenant Name</th>
              <th>Mobile No</th>
              <th>Contract From</th>
              <th>Contract To</th>
              <th>Rent pm</th>
              <th>Rent Paid Upto</th>
              <th>Last Paid Date</th>
              <th>Bounce Cheque No</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody>
            @php $count = 1; @endphp
            <input type="hidden" name="search_count" value="{{count($pdc)}}" >
            <input type="hidden" name="row_collection" value="0" id="row_collection">
            @forelse ($pdc as $key=>$pdcData) 
            <tr id="tr_{{$pdcData->id}}">

             
              <td>{{ $pdcData->tenantContractInfo->building->building_name}}</td>
              <td>{{ $pdcData->tenantContractInfo->Unit->unit_no}}</td>
              <td>{{ $pdcData->tenantContractInfo->tenant->tenant_name}}</td>
            <td>{{ $pdcData->tenantContractInfo->tenant->tenant_contact_no}}</td>
            <td>{{ $pdcData->tenantContractInfo->tenant_contract_effective_date->format('d/m/Y')}}</td>
            <td>{{ $pdcData->tenantContractInfo->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
            <td>{{ $pdcData->tenantContractInfo->tenant_contract_rent}}</td>
             <td>@if(!empty($pdcData->tenantContractInfo->tenant_contract_last_paid_date)){{ $pdcData->tenantContractInfo->tenant_contract_last_paid_date->format('d/m/Y')}}@endif</td>

              <td>@if(!empty($pdcData->tenantContractInfo->receiptGeneration->receipts_generation_receipt_date)){{ $pdcData->tenantContractInfo->receiptGeneration->receipts_generation_receipt_date->format('d/m/Y')}}@endif</td>
            <td>{{$pdcData->pdc_check_no}}</td>
            <td>{{$pdcData->pdc_cancel_reason_name}}</td>
          </tr>  


          @empty 
          <tr>
            <td colspan="12" align="center">
              <p>No Record</p>
            </td>
          </tr>
          @endforelse 

        </tbody>
      </table>
      @php $count++; @endphp
    </div>

    @php
    /*
    $sort =  app('request')->input('sort') ;
    if(!empty($sort)){
    $direction =  app('request')->input('direction') ;
    $pdc->appends(['sort' => $sort, 'direction' => $direction ]);                   
  }  */                             
  @endphp 
  {{--$pdc->appends(\Request::except(['page','_token']))->links()--}} 

</div>
</div>
</div>
</div>

@endsection
@section('scripts')  

@endsection
