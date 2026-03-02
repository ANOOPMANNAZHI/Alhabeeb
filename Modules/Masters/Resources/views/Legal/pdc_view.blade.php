@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Pdc Detailed View</div>
    </div>
    {{-- {{ Breadcrumbs::render('legalPdcShow',$pdc) }} --}}
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="card-box">
    <div class="card card-box salesLeadBox">
  <div class="card-head">
    <div class="col"><h4>PDC Details</h4></div>
  </div>
  <div class="card-body">
   <div class="col">
    <div class="row">

      <div class="col leadInformation">
       <div class="table-responsive1">
       <div style="overflow-x:auto;">
        <table class="table" >
          <thead>
            <tr style="background: #f5f5f5;">
              <th>Agreement Details</th>
              <th>Transaction No</th>
              <th>Transaction Date</th>
              <th>Check No</th>
              <th>Check Date</th>
              <th>Reference</th>
              <th>Received Date</th>
              <th>Period</th>
              <th>Amount</th>
              <th>Deposit Date</th>
              <th>Bank Name</th>
              <th>Stage</th>
              <th>Clear Date</th>
              <th>Receipt No</th>
              <th>Cancel Date</th>
              <th>Cancel Reason</th>
              <th>Cancel By</th>
              <th>Remark</th>
              <th>Posted</th>
              <th>Posted By</th>
              <th>Posted Date</th>
              <th>Posted Bank</th>
              <th>Type</th>
              <th>Date Time</th>
            </tr>
          </thead>
          <tbody>

            @forelse ($pdc as $pdc)

            @if($pdc->tenant_contract_id!="") 
            <tr>
              <td> {{$pdc->tenantContractInfo->tenant_contract_no}}</td>
              <td>{{$pdc->pdc_transaction_no}}</td>
              <td>{{$pdc->pdc_transaction_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_check_no}}</td>
              <td>{{$pdc->pdc_check_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_reference}}</td>
              <td>{{$pdc->pdc_recieve_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_period}}</td>
              <td>{{ number_format($pdc->pdc_transaction_no,3) }} OMR</td>
              <td>{{$pdc->pdc_deposit_date->format('d/m/Y')}}</td>
              <td>{{$pdc->bankInfo->bank_name}}</td>
              <td>{{$pdc->pdc_stage}}</td>
              <td>{{$pdc->pdc_clear_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_receipt_no}}</td>
              <td>{{$pdc->pdc_cancel_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_cancel_reason}}</td>
              <td>{{$pdc->pdc_cancel_by}}</td>
              <td>{{$pdc->pdc_remark}}</td>
              <td>{{$pdc->pdc_is_posted}}</td>
              <td>{{$pdc->pdc_posted_by}}</td>
              <td>{{$pdc->pdc_posted_date->format('d/m/Y')}}</td>
              <td>{{$pdc->pdc_posted_bank_id}}</td>
              <td>{{$pdc->pdc_type}}</td>
              <td>{{$pdc->createdBy->username}}</td>
              <td>{{$pdc->created_at->format('d/m/Y h:m A')}}</td>
            </tr> 
            @endif                       
            @empty 
            <tr>
              <td colspan="10" align="center">
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
<!-- ends-->        
</div>
</div>
</div>
@endsection
@section('scripts')

@endsection