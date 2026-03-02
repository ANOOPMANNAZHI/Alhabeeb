@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Contract Invoices View</div>
        </div>
          @if($flag == 0)
            {{ Breadcrumbs::render('LandlordByTenantInvoiceView',$tenantContract,$termination) }}
          @else
           {{ Breadcrumbs::render('LandlordApprovalByTenantInvoiceView',$tenantContract,$termination) }}
          @endif
         
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
      <div class="card-box salesSearchBox">
        <table class="table display product-overview mb-30" id="dtBasicExample">
          <thead>
            <tr>
              <th>Invoice No</th>
              <th>Invoice Type</th>
              <th>Invoice Date</th>
              <th>Amount</th>
              <th>Posted Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($remainingInvoices as $remainingInvoice)
                <tr>
                    <td>{{$remainingInvoice->tenant_invoice_no}}</td>
                    <td>{{$remainingInvoice->tenant_invoice_type}}</td>
                    <td>{{$remainingInvoice->tenant_invoice_date->format('d/m/Y')}}</td>
                    <td>{{numberFormat($remainingInvoice->tenant_invoice_amt)}}</td>
                    <td>{{isset($remainingInvoice->tenant_invoice_posted_date)?$remainingInvoice->tenant_invoice_posted_date->format('d/m/Y'):""}}
                    </td>
                </tr>
                @empty 
                <tr>
                   <td colspan="5" align="center">
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