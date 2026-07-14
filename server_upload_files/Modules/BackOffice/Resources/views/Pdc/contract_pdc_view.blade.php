@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">


@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Tenant PDC</div>
        </div>
       
          {{ Breadcrumbs::render('tenant-pdc',$tenantContract->id) }}
         
    </div>
</div>
<div class="row mb-3">
  <div class="col">
    <a class="btn btn-circle btn-default align-right btnprn" title="Print" href="{{route('PdcprintPreview',$tenantContract->id)}}">Print</a>
  </div>
</div>
<div class="row">
	<div class="col">
	 	<div class="card card-box salesSearchBox">
			<div class="card-body row">
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>PDC Transaction No :  </b>
                              <span>{{$pdc_transaction_no?? 'NA'}}</span>
                            </h5>
                        </div>
                    </div>
                    <div class="col-lg-6 p-t-20"> 
                        <div class = "txt-full-width">
                            <h5 class="details"><b>PDC Transaction Date :  </b><span>{{(count($tenantPdcInfo))?$pdc_transaction_date->format('d/m/Y'):'NA' }}</span></h5>
                           
                        </div>
                    </div>
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
			<div class="card-head ">
					PDC Details
			</div>
			<div class="card-body ">
			  <div class="table-wrap">
				<div class="table-responsive">
				 
				  <table class="table display product-overview mb-30" id="dtBasicExample">
					  <thead>
						  <tr>
							  
							  <th>Cheque No</th>
							  <th>Cheque Dt</th>
							  <th>Amt</th>
							  <th>Stage</th>
							  <th>Bank Name</th>
							  <th>Rec Dt</th>
							  <th>Dep Dt</th>
							  <th>Clear Dt</th>
							  <th>Cancelled Dt</th>
							  <th>Cancelled</th> 
							  <th>Bounce Reason</th>
								<th>Receipt Voucher</th>
							  <th>Receipt Type</th>
							  
						  </tr> 
					  </thead>
					<tbody>
                  @forelse($tenantPdcInfo as $key=>$item)
					<tr>      
                     
                      <td>{{$item->pdc_check_no}}</td>
                      <td>{{$item->pdc_check_date->format('d-m-Y')}}</td>
                      <td>{{number_format($item->pdc_amt, 3)}} OMR</td>
                      <td>{{$item->pdc_stage}}</td>
                      <td>
						  @foreach($bankMaster as $name)
								{{($item->bank_id == $name->id)?$name->bank_name:''}}
						  @endforeach
                      
                      </td>
                      <td>{{$item->pdc_recieve_date->format('d-m-Y')}}</td>
                      <td>{{isset($item->pdc_deposit_date)?$item->pdc_deposit_date->format('d-m-Y'):''}}</td>
                      <td>{{isset($item->pdc_clear_date)?$item->pdc_clear_date->format('d-m-Y'):''}}</td>
                      <td>{{isset($item->pdc_cancel_date)?$item->pdc_cancel_date->format('d-m-Y'):''}}</td>
                      <td>
						  
						  {{($item->pdc_cancel_reason == 1)?'Bounce':($item->pdc_cancel_reason == 2)?'Exchange':($item->pdc_cancel_reason == 3)?'Replace':''}}
					  </td>
                      <td>
						  @if($item->pdc_bounce_reason == 1)
								Insufficient Funds
                          @elseif($item->pdc_bounce_reason == 2)
								Signature Missing
                          @elseif($item->pdc_bounce_reason == 3)
								Signature Mismatch
                          @elseif($item->pdc_bounce_reason == 4)
								Word in amount and figure differ
						  @elseif($item->pdc_bounce_reason == 5)
								Stop Payment
                          @elseif($item->pdc_bounce_reason == 6)
								Refer to Drawer
                          @elseif($item->pdc_bounce_reason == 7)
								Correction
                          @elseif($item->pdc_bounce_reason == 8)
								Stale Cheque (Beyond six months)
						  @elseif($item->pdc_bounce_reason == 9)
								Misc
						  @endif
                          </select>   
						  
					  </td>
                      <td>{{$item->pdc_receipt_no}}</td>
                      <td>{{ $item->pdc_type_name }} </td>
                  </tr>
                  @empty
                  <tr>      
                      <td colspan="12">
							No Record
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
 
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('public/js/jquery.printPage.js')}}"></script>
<script>
$(document).ready(function() {
  //Print function
  $('.btnprn').printPage();
})
</script>
@endsection

