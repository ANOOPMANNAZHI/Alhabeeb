@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Contract Pdc View</div>
        </div>
   		@if($flag == 0)
            {{ Breadcrumbs::render('LandlordByTenantPdcView',$tenantContract,$termination) }}
	    @else
	       {{ Breadcrumbs::render('LandlordApprovalByTenantPdcView',$tenantContract,$termination) }}
	    @endif
           
         
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
      <div class="card-box salesSearchBox">
        <table class="table display product-overview mb-30" id="dtBasicExample">
          <thead>
            <tr>
              <th>Transaction No</th>
              <th>Transaction Date</th>
              <th>Check No</th>
              <th>Check Date No</th>
              <th>Receive Date</th>
              <th>Amount</th>
              <th>Receipt No</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($remainingPdcs as $remainingPdc)
                <tr>
                    <td>{{$remainingPdc->pdc_transaction_no}}</td>
                    <td>{{$remainingPdc->pdc_transaction_date->format('d/m/Y')}}</td>
                    <td>{{$remainingPdc->pdc_check_no}}</td>
                    <td>{{$remainingPdc->pdc_check_date->format('d/m/Y')}}</td>
                    <td>{{$remainingPdc->pdc_recieve_date->format('d/m/Y')}}</td>
                    <td>{{numberFormat($tenantContract->pdc_amt)}} OMR</td>                    
                    <td>{{$remainingPdc->pdc_receipt_no}}</td>
                </tr>
                @empty 
                <tr>
                   <td colspan="7" align="center">
                    <p>No Record</p>
                  </td>
                </tr>
                @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
@endsection
@section('scripts')
@endsection