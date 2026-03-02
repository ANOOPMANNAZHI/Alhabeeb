@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant Invoice</div>
        </div>
                    {{ Breadcrumbs::render('tenantinvoice',$breadcrumbParent, $params)}}
    </div>
</div>
<div class="row">
	<div class="col">
	 	<div class="card card-box salesSearchBox">
			<div class="card-body row">
               
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Name :  </b>
                            	<span>{{$tenantContract->building->building_name}}
                            </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Building Code  :  </b><span>{{$tenantContract->building->building_code}}</span></h5>
                        </div>
                    </div>
                     <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit No :  </b>
                            	<span>{{$tenantContract->unit->unit_no}}
                            </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Unit Code  :  </b><span>{{$tenantContract->unit->unit_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Name :  </b>
                            	<span>{{$tenantContract->tenant->tenant_name}}
                            </span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Tenant Code  :  </b><span>{{$tenantContract->tenant->tenant_code}}</span></h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
                        </div>
                    </div>
            </div>
	 	</div>
	</div>
</div>

<div class="row">
    <div class="col">
        <div class="card card-box">

    <div class="card-head">
		<div class="col"><h4>Invoice Details</h4></div>
	</div>
	<div class="card-body ">
     <div class="table-wrap">
       <div class="table-responsive">
          <table class="table display product-overview mb-30" id="dtBasicExample">
              <thead>
                  <tr>
                      <th>Sl. No</th>
                      <th>Invoice No</th>
                      <th>Invoice Dt</th>
                      <th>Invoice Amt</th>
                      <!--
                      <th>Bldg Name</th>
                      <th>Bldg Code</th>
                      <th>Unit No</th>
                      <th>Tenant</th>
                      <th>Tenant Code</th>
                      <th>Agre No</th> 
                  		-->
                      <th>Desc</th>
                      <th width="9%" style="text-align:center">Action</th>
                  </tr> 
              </thead>
              <tbody>
              	@foreach ($invoiceList as $invoice)
				  <tr>                   
                    <td>
                      <a class="no-link"title="">{{$loop->iteration}}</a>
                    </td>
                    <td>
                      <a class="no-link" title="">{{$invoice->tenant_invoice_no}}</a>
                     </td>
                    <td>
                      <a class="no-link" title="">{{$invoice->tenant_invoice_date->format('d/m/Y')}}</a>
                    </td>
                    <td>
                      <a class="no-link" title="">{{numberFormat($invoice->tenant_invoice_amt)}}</a>
                    </td>
                    <!-- <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                                              
                    </td>
                     <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                                              
                    </td>
                    <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                                              
                    </td>
                    <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                                              
                    </td>
                    <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                                              
                    </td>
                    <td>
                      <a class="no-link" title="">Swarna Sharma</a>
                    </td> -->
                    <td>
                      <a class="no-link" title="">{{$invoice->tenant_invoice_desc}}</a>
                    </td>
					<td>
                      	<a href="{{route('tenantRentInvoiceDetails',$invoice->id)}}" title="View" class="btn btn-tbl-view btn-xs">
                            <i class="fa fa-eye"></i>
                        </a>
                        @if($invoice->tenant_invoice_status !=3)
                        <a href="{{route('tenantInvoicePosting',$invoice->id)}}" title="Post" class="btn btn-tbl-violet btn-xs post_type">
                            <i class="fa fa-pie-chart"></i>
                        </a>
                        @endif
                       
                    </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
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
