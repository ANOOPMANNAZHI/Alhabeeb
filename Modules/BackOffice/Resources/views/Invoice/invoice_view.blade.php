@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Tenant Invoice</div>
    </div>

    {{ Breadcrumbs::render('tenant-invoice-view',$invoiceDetails->id, $invoiceDetails->tenant_contract_id) }}

  </div>
</div>

<div class="row">
  <div class="col">
    <div class="card card-box salesSearchBox">
      <div class="row">
        <div class="col-sm-12">
         @if($invoiceDetails->tenant_invoice_status !=3)
         <a href="{{route('tenantInvoicePosting',$invoiceDetails->id)}}" title="Post" class="btn btn-circle btn-primary align-right post_type">
           POST
         </a>
         @endif
       </div>
     </div>
     <div class="dataSearchBox">
      <div class="card-body row">
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Invoice No :  </b>
              <span>{{$invoiceDetails->tenant_invoice_no}}
              </span></h5>
            </div>
          </div>
          <div class="col-lg-6 p-t-20"> 
            <div class = "txt-full-width">
              <h5 class="details"><b>Invoice Date  :  </b><span>{{$invoiceDetails->tenant_invoice_date->format('d/m/Y')}}</span></h5>
            </div>
          </div>

        </div>
      </div>
      <div class="sub-head">Contract Details</div>
      <div class="dataSearchBox">
       <div class="card-body row">

        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
           <h5 class="details"><b>Building Name  :  </b><span>{{$invoiceDetails->tenantContractInfo->building->building_name}}</span></h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20"> 
        <div class = "txt-full-width">
         <h5 class="details"><b>Building Code :  </b><span>{{$invoiceDetails->tenantContractInfo->building->building_code}}</span></h5>
       </div>
     </div>
     <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Unit No :  </b><span>{{$invoiceDetails->tenantContractInfo->unit->unit_no}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Unit Code :  </b><span>{{$invoiceDetails->tenantContractInfo->unit->unit_code}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Tenant Name :  </b><span>{{$invoiceDetails->tenantContractInfo->tenant->tenant_name}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">

       <h5 class="details"><b>Tenant Code :  </b><span>{{$invoiceDetails->tenantContractInfo->tenant->tenant_code}}</span></h5></span>
     </div>
   </div>
   <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Agreement No  :  </b><span>{{$invoiceDetails->tenantContractInfo->tenant_contract_no}}</span></h5>
    </div>
  </div>
  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Agreement Date  :  </b><span>{{$invoiceDetails->tenantContractInfo->created_at->format('d/m/Y')}}</span></h5>
    </div>
  </div>
  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Description  :  </b><span>{{$invoiceDetails->tenant_invoice_desc}}</span></h5>
    </div>
  </div>
  <div class="col-lg-6 p-t-20"> 
    <div class = "txt-full-width">
      <h5 class="details"><b>Invoice Rent Amount   :  </b><span>{{numberFormat($invoiceDetails->tenant_invoice_amt)}}</span></h5>
    </div>
  </div>

</div>
</div>
<div class="dataSearchBox">
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>AX-Batch Id:  </b><span>{{$invoiceDetails->ax_batch_id}}</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>AX-Invoice No  :  </b><span>{{$invoiceDetails->ax_invoice_no}}</span></h5>
      </div>
    </div>
  </div>
</div>
<div class="sub-head">Financial Dimension</div>
<div class="dataSearchBox">
  <div class="card-body row">
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Division:  </b><span>
		{{($invoiceDetails->tenantContractInfo->building->ax_division==01)?'HO':'PLM'}}
		</span></h5>
      </div>
    </div>
    <div class="col-lg-6 p-t-20"> 
      <div class = "txt-full-width">
        <h5 class="details"><b>Buildings :  </b><span>{{$invoiceDetails->tenantContractInfo->building->building_name}}</span></h5>
      </div>
    </div>
  </div>
</div>
<div class="sub-head">Distribution Details</div>
<div class="dataSearchBox">
 @php
 $debitSum = 0;
 $creditSum = 0;
 @endphp
 <div class="table-wrap">
  <div class="table-responsive">
    <table class="table display product-overview mb-30" id="dtBasicExample">
      <thead>
        <tr>
          <th>Account Code</th>
          <th>Description</th>
          <th>Type</th>
          <th>Dr Amt</th>
          <th>Cr Amt</th>

        </tr> 
      </thead>
      <tbody>
        @foreach($invoiceDetails->tenantInvoiceDimensionInfo as $distribution)
        <tr>                   
          <td>{{$distribution->acc_code_no}}</td>
          <td>{{$distribution->acc_code_desc}}
          </td>
          <td>{{$distribution->dimension_type}}</td>
          <td>{{numberFormat($distribution->debit_amount)}}
            @php
            $debitSum = $debitSum +$distribution->debit_amount;
            $creditSum = $creditSum +$distribution->credit_amount;
            @endphp    
          </td>

          <td>{{numberFormat($distribution->credit_amount)}}

          </td>
        </tr>
        @endforeach
        <tr>                   
          <td></td>
          <td></td>
          <td><b>Total</b></td>
          <td>{{numberFormat($debitSum)}}</td>

          <td>{{numberFormat($creditSum)}}

          </td>
        </tr>
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
</div>
</div>

<form id="delete-form" action="" method="GET">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')
<script>
   jQuery(document).ready(function() {
     jQuery('.post_type').click(function (event) {
                var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Post this Invoice?')) {
                    jQuery("#delete-form").attr('action', action);
                    jQuery("#delete-form").submit();
                } else {
                    return false;
                }
            });
     });
</script>

@endsection
