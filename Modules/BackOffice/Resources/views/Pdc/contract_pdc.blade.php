@extends('layouts.plms-app')
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">

<?php 
   $stages = array();

   foreach($tenantPdcInfo as $item){
      // $stages.push($item->pdc_stage);
      array_push($stages,$item->pdc_stage);
   }

   $stages_all= array_unique($stages);

  

?>

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

<div class="row">
 <div class="col-md-6">
   <div class="card card-box salesSearchBox">
     <div class="card-body row">

    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <input type="file" name="file" class="form-control" required>
      <input type="hidden" name="tenant_contract_id" value="{{$tenantContract->id}}">
      <br>

      <button class="btn btn-success">Import User Data</button>
      <a href="{{ route('export') }}">Click Here to Download the Excel</a>
    </form>
  </div>
</div>
</div>

<div class="col-md-6">
  <div class="col">

    <div class="card card-box salesSearchBox">
      <div class="card-header" style=" padding: 0px; background-color: white; ">
        <h4>Print PDC</h4>
      </div>
     <div class="card-body row">
      <div class="form-group "style="margin: 0;">
         <select class=" mr-2 mt-2" id="stage_print" onchange="changeroute(this.value)">
          <option value="0">Choose Stage</option>
          @foreach($stages_all as $stg)
            <option value="{{$stg}}">{{$stg}}</option>
          @endforeach
        </select>
        <a class="btn btn-circle btn-default align-right btnprn" title="Print" href="{{route('PdcprintPreviewstage',['id'=>$tenantContract->id,'stage'=>0]) }}">Print</a>

        <input type="hidden" id="contract_id" value="{{$tenantContract->id}}">

      </div>
        
      
      </div>
    </div>

    
  </div>
</div>
</div>



<form action="{{route('pdc.store')}}" method="POST" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" id="pdc_form">
  <input type="hidden" name="tenantContract" value="{{$tenantContract->id}}">
  @php
  if($tenantContract->tenant_contract_payment_type == 4)
  $payment_term = 6;
  elseif($tenantContract->tenant_contract_payment_type == 5)  
  $payment_term = 12;
  else
  $payment_term = $tenantContract->tenant_contract_payment_type;

  @endphp
  <input type="hidden" name="paymentTerm" id="payment_term" value="{{$payment_term}}">

  <input type="hidden" name="existRow" id="existRow" value="{{count($tenantPdcInfo)}}">
  <input type="hidden" name="deleteRow" id="deleteRow" value="">


  {{csrf_field()}}
  <div class="row">
   <div class="col">
     <div class="card card-box salesSearchBox">
       <div class="card-body row">
        <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>PDC Transaction No :  </b>
             <span>{{$pdcTransacNo}}</span>
           </h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>PDC Transaction Date :  </b><span>{{(count($tenantPdcInfo))?$pdc_transaction_date->format('d/m/Y'):date('d/m/Y') }}</span></h5>
           <input type="hidden" name="pdc_transaction_date" value="{{(count($tenantPdcInfo))?$pdc_transaction_date:date('Y-m-d') }}">
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
           <h5 class="details"><b>Tenant Name :  </b>
            <span>{{$tenantContract->tenant->tenant_name}}
            </span></h5>
          </div>
        </div>
       <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>Agreement No :  </b><span>{{$tenantContract->tenant_contract_no}}</span></h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>Start Date :  </b><span>{{$tenantContract->tenant_contract_start_date->format('d/m/Y')}}</span></h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>End Date :  </b><span>{{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</span></h5>
         </div>
       </div>
       <div class="col-lg-6 p-t-20">
         <div class = "txt-full-width">
           <h5 class="details"><b>Rent PM:  </b><span>{{isset($tenantContract->tenant_contract_rent)?numberFormat($tenantContract->tenant_contract_rent)." OMR":"NA"}}</span></h5>
         </div>
       </div>
        <div class="col-lg-6 p-t-20">
          <div class = "txt-full-width">
            <h5 class="details"><b>Mode of Payment :  </b><span>{{$tenantContract->TenantContractPaymentName}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20">
          <div class = "txt-full-width">
            <h5 class="details"><b>Rent paid Up To :  </b><span>@if(!empty($tenantContract->tenant_contract_last_paid_date))
             {{$tenantContract->tenant_contract_last_paid_date->format('d/m/Y')}}
             @else
             NA
           @endif</span></h5>
         </div>
       </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
          <h5 class="details"><b>Rent :  </b><span>{{isset($tenantContract->tenant_contract_rent)?numberFormat($tenantContract->tenant_contract_rent)." OMR":"NA"}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>Payment Term :  </b><span>{{$tenantContract->TenantContractPaymentName}}</span></h5>
          </div>
        </div>
        <div class="col-lg-6 p-t-20"> 
          <div class = "txt-full-width">
            <h5 class="details"><b>No of Cheques :  </b>
              <span><input placeholder="Enter no of PDC" type="number" name="process_no" id="process_no" min="1" >
                <button type="button" class="btn btn-circle btn-primary re_Assign align-left" disabled  id="process_btn" >Process</button>
              </span></h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="sub-head">
    <h4>PDC Details</h4>
  </div>
  <div class="row">
    <div class="col">
      <div class="card card-box">
       <div class="card-header">

        <button type="submit" class="btn btn-circle btn-primary save_subt align-right"  {{($tenantPdcInfo->isEmpty())?'id=save_subt':''}} >Save</button>

      </div>

      <div class="card-body ">
        <div class="table-wrap">
          <div class="table-responsive">

            <table class="table display product-overview mb-30" id="dtBasicExample">
              <thead>
                <tr>
                  <th>
                    <a class="addDataRow"  id="singleRow" href="#" title="Add">
                      <i class="fa fa-plus" aria-hidden="true"></i> 
                    </a>
                  </th>
                  <th>Sl No.</th>
                  <th>Cheque No <span>*</span></th>
                  <th>Cheque Dt <span>*</span></th>
                  <th>Amt <span>*</span></th>
                  <th>Stage <span>*</span></th>
                   <th>Receipt Type</th>
                  <th>Bank Name <span>*</span></th>
                  <th>Rec Dt <span>*</span></th>
                  <th>From Dt</th>
                  <th>To Dt</th>
                  <th>Dep Dt</th>
                  <th>Clear Dt</th>
                  <th>Cancelled Dt</th>
                  <th>Cancelled</th> 
                  <th>Bounce Reason</th>
                  <th>Receipt Voucher</th>
                  <th>Cheque Acknowledge</th>
                  <th>Remark</th>
                </tr> 
              </thead>
              <tbody>
               @php
               $process_action = 0;
               $rowVal ='';    $loop_no = 1;
               @endphp

               @forelse($tenantPdcInfo as $key=>$item)

               @php
               $process_action = 0;
               $isDisable = 0;
               $rowVal .= $key.',';
               // Posted and Bounce and Exchange Line Item will be disabled
               if($item->pdc_is_posted){
               $isDisable = $item->pdc_is_posted;
             }
             elseif($item->pdc_cancel_reason > 0 || isset($item->pdc_clear_date)){
             $isDisable = 1;
           }
           @endphp


           <tr class="chkRow" id="tr_{{$key}}">   
            
            <td>
              <!--  1 - Saved . To know save or update-->
              <input type="hidden" name="pdc_check_id_{{$key}}" id="pdc_check_id_{{$key}}" value="{{$item->id}}">
              <input type="hidden" name="pdc_is_saved_{{$key}}" value="{{$item->pdc_is_saved}}" >
              @if(empty($isDisable))
              <a  class="dataRow"  id="{{$key}}" href="#" title="Delete">
                <i class="fa fa-minus" aria-hidden="true"></i> 
              </a>
              @endif
            </td> 
             <td><span class="sl_no">{{$loop_no}} </span> @php  $loop_no++;   @endphp </td> 
            <td><input type="text" maxlength="10" {{($isDisable==1)?'readonly':''}} name="pdc_check_no_{{$key}}" id="pdc_check_no_{{$key}}" onkeypress="return isNumber(event)" value="{{$item->pdc_check_no}}" class="pdc_no_cls"></td>
            <td><input type="date" {{($isDisable==1)?'readonly':''}} name="pdc_check_date_{{$key}}" id="pdc_check_date_{{$key}}" value="{{$item->pdc_check_date->format('Y-m-d')}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls" ></td>

            <td><input type="text" {{($isDisable==1)?'readonly':''}} onkeyup="return FormatCurrency(this)" name="pdc_amt_{{$key}}" id="pdc_amt_{{$key}}" value="{{numberFormat($item->pdc_amt)}}"  min="1" class="text-right allownumericwithdecimal pdc_amt_cls" maxlength="10" ></td>

            <td><input type="number" {{($isDisable==1)?'readonly':''}}  name="pdc_stage_{{$key}}" id="pdc_stage_{{$key}}" value="{{$item->pdc_stage}}"   min="1" max="100"  class="pdc_stage_cls"></td>
                      <td>
            @if($isDisable==1)    
            <input type="hidden" name="pdc_type_{{$key}}" id="pdc_type_{{$key}}" value="{{$item->pdc_type}}">
            <select name="temp_pdc_type_{{$key}}" id="temp_pdc_type_{{$key}}" {{($isDisable)?'disabled':''}} id="pdc_bounce_reason_{{$key}}">
              <option value="1" {{($item->pdc_type == 1)?'selected':''}}>Rent</option>
              <option value="2" {{($item->pdc_type == 2)?'selected':''}}>Deposit</option>
              <option value="3" {{($item->pdc_type == 3)?'selected':''}}>Others</option>

            </select>

            @else  
            <select name="pdc_type_{{$key}}" id="pdc_type_{{$key}}" id="pdc_bounce_reason_{{$key}}">
              <option value="1" {{($item->pdc_type == 1)?'selected':''}}>Rent</option>
              <option value="2" {{($item->pdc_type == 2)?'selected':''}}>Deposit</option>
              <option value="3" {{($item->pdc_type == 3)?'selected':''}}>Others</option>

            </select>
            @endif
          </td>
            <td>
              @if($isDisable==1)
              <input type="hidden" name="bank_id_{{$key}}" id="bank_id_{{$key}}" value="{{$item->bank_id}}">

              <select name="temp_bank_id_{{$key}}" id="temp_bank_id_{{$key}}" {{($isDisable)?'disabled':''}}>
                <option value="">Select</option>
                @foreach($bankMaster as $name)
                <option {{($item->bank_id == $name->id)?'selected':''}} value="{{$name->id}}">{{$name->bank_code}}</option>
                @endforeach
              </select>
              @else
              <select name="bank_id_{{$key}}" id="bank_id_{{$key}}" >
                <option value="">Select</option>
                @foreach($bankMaster as $name)
                <option {{($item->bank_id == $name->id)?'selected':''}} value="{{$name->id}}">{{$name->bank_code}}</option>
                @endforeach
              </select>         
              @endif
            </td>
            <td><input type="date" {{($isDisable==1)?'readonly':''}} name="pdc_recieve_date_{{$key}}" id="pdc_recieve_date_{{$key}}" value="{{$item->pdc_recieve_date->format('Y-m-d')}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
            <td><input type="date" {{($isDisable==1)?'readonly':''}} name="pdc_from_date_{{$key}}" id="pdc_from_date_{{$key}}" value="{{isset($item->pdc_from_date)?$item->pdc_from_date:''}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>

            <td><input type="date" {{($isDisable==1)?'readonly':''}} name="pdc_to_date_{{$key}}" id="pdc_to_date_{{$key}}" value="{{isset($item->pdc_to_date)?$item->pdc_to_date:''}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls" ></td>

            <td><input type="date" {{($isDisable==1)?'readonly':''}} name="pdc_deposit_date_{{$key}}" id="pdc_deposit_date_{{$key}}" value="{{isset($item->pdc_deposit_date)?$item->pdc_deposit_date->format('Y-m-d'):''}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls" readonly></td>

            <td><input type="date" readonly {{($isDisable==1)?'readonly':(!empty($item->pdc_deposit_date)?'':'readonly')}} name="pdc_clear_date_{{$key}}" id="pdc_clear_date_{{$key}}" value="{{isset($item->pdc_clear_date)?$item->pdc_clear_date->format('Y-m-d'):''}}" class="pdc_clear_date" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>

            <td><input type="date" {{($isDisable==1)?'readonly':(!empty($item->pdc_clear_date)?'':'readonly')}} name="pdc_cancel_date_{{$key}}" id="pdc_cancel_date_{{$key}}" value="{{isset($item->pdc_cancel_date)?$item->pdc_cancel_date->format('Y-m-d'):''}}" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
            <td>
             @if($isDisable==1) 
             <input type="hidden" name="pdc_cancel_reason_{{$key}}"  id="pdc_cancel_reason_{{$key}}" value="{{$item->pdc_cancel_reason}}">  
             <select name="temp_pdc_cancel_reason_{{$key}}" class="pdc_cancel_reason" {{($isDisable)?'disabled':''}} id="temp_pdc_cancel_reason_{{$key}}">
              <option value="">Select</option>
              <option value="1" {{($item->pdc_cancel_reason == 1)?'selected':''}}>Bounce</option>
              <option value="2" {{($item->pdc_cancel_reason == 2)?'selected':''}}>Exchange</option>
            </select>

            <input type="hidden" name="pdc_reference_{{$key}}" id="pdc_reference_{{$key}}" value="">
            @else
            <select name="pdc_cancel_reason_{{$key}}" {{($isDisable)?'disabled':''}} class="pdc_cancel_reason" id="pdc_cancel_reason_{{$key}}">
              <option value="">Select</option>
              <option value="1" {{($item->pdc_cancel_reason == 1)?'selected':''}}>Bounce</option>
              <option value="2" {{($item->pdc_cancel_reason == 2)?'selected':''}}>Exchange</option>
            </select>
            <input type="hidden" name="pdc_reference_{{$key}}" id="pdc_reference_{{$key}}" value="">
            @endif

          </td>
          <td>
            @if($isDisable==1 || isset($item->pdc_clear_date)) 
            <input type="hidden" name="pdc_bounce_reason_{{$key}}" id="pdc_bounce_reason_{{$key}}" value="{{$item->pdc_bounce_reason}}">               
            <select name="temp_pdc_bounce_reason_{{$key}}" disabled  id="temp_pdc_bounce_reason_{{$key}}">
              <option value="">Select</option>
              <option value="1" {{($item->pdc_bounce_reason == 1)?'selected':''}}>Insufficient Funds</option>
              <option value="2" {{($item->pdc_bounce_reason == 2)?'selected':''}}>Signature Missing</option>
              <option value="3" {{($item->pdc_bounce_reason == 3)?'selected':''}}>Signature Mismatch</option> 
              <option value="4" {{($item->pdc_bounce_reason == 4)?'selected':''}}>Word in amount and figure differ</option>
              <option value="5" {{($item->pdc_bounce_reason == 5)?'selected':''}}>Stop Payment</option>
              <option value="6" {{($item->pdc_bounce_reason == 6)?'selected':''}}>Refer to Drawer</option>
              <option value="7" {{($item->pdc_bounce_reason == 7)?'selected':''}}>Correction</option>  
              <option value="8" {{($item->pdc_bounce_reason == 8)?'selected':''}}>Stale Cheque (Beyond six months)</option>
              <option value="9" {{($item->pdc_bounce_reason == 9)?'selected':''}}>Misc</option>
            </select>
            @else
            <select name="pdc_bounce_reason_{{$key}}" {{!$item->pdc_bounce_reason?'disabled':''}} id="pdc_bounce_reason_{{$key}}">
              <option value="">Select</option>
              <option value="1" {{($item->pdc_bounce_reason == 1)?'selected':''}}>Insufficient Funds</option>
              <option value="2" {{($item->pdc_bounce_reason == 2)?'selected':''}}>Signature Missing</option>
              <option value="3" {{($item->pdc_bounce_reason == 3)?'selected':''}}>Signature Mismatch</option> 
              <option value="4" {{($item->pdc_bounce_reason == 4)?'selected':''}}>Word in amount and figure differ</option>
              <option value="5" {{($item->pdc_bounce_reason == 5)?'selected':''}}>Stop Payment</option>
              <option value="6" {{($item->pdc_bounce_reason == 6)?'selected':''}}>Refer to Drawer</option>
              <option value="7" {{($item->pdc_bounce_reason == 7)?'selected':''}}>Correction</option>  
              <option value="8" {{($item->pdc_bounce_reason == 8)?'selected':''}}>Stale Cheque (Beyond six months)</option>
              <option value="9" {{($item->pdc_bounce_reason == 9)?'selected':''}}>Misc</option>
            </select>   
            @endif
          </td>
          <td>{{$item->pdc_receipt_no}}</td>
          <td><input {{($isDisable==1)?'readonly':''}}  type="text" name="pdc_cheque_acknowledge_{{$key}}" id="pdc_cheque_acknowledge_{{$key}}" value="{{$item->pdc_cheque_acknowledge}}"></td>
          <td><input {{($isDisable==1)?'readonly':''}} type="text" name="pdc_remark_{{$key}}" id="pdc_remark_{{$key}}" value="{{$item->pdc_remark}}"></td>
        </tr>
        @empty
        <tr class="chkRow" id="tr_0">   
           
          <td>
            <input type="hidden" name="pdc_is_saved_0" value="0" >
            <a  class="dataRow" id="0" href="#" title="Delete">
              <i class="fa fa-minus" aria-hidden="true"></i> 
            </a>
          </td>
          <td><span class="sl_no">1</span></td>             
          <td><input type="text" name="pdc_check_no_0" id="pdc_check_no_0" maxlength="10" onkeypress="return isNumber(event)" class="pdc_no_cls"></td>
          <td><input type="date"  name="pdc_check_date_0" id="pdc_check_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
          <td><input type="text" name="pdc_amt_0" id="pdc_amt_0"  min="1" step="0.01" onkeyup="return FormatCurrency(this)" class="allownumericwithdecimal pdc_amt_cls text-right" maxlength="10" ></td>
          <td><input type="number" required name="pdc_stage_0" id="pdc_stage_0"  min="1" max="100" class="pdc_stage_cls"></td>
                  <td>
          <select name="pdc_type_0" id="pdc_type_0">
            <option value="1">Rent</option>
            <option value="2">Deposit</option>
            <option value="3">Others</option>

          </select>
        </td>
          <td>
            <select name="bank_id_0" id="bank_id_0">
              <option value="">Select</option>
              @foreach($bankMaster as $name)
              <option value="{{$name->id}}">{{$name->bank_code}}</option>
              @endforeach
            </select>
          </td>
          <td><input type="date" name="pdc_recieve_date_0" id="pdc_recieve_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
           <td><input type="date" name="pdc_from_date_0" id="pdc_from_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
          <td><input type="date" name="pdc_to_date_0" id="pdc_to_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
          <td><input type="date" name="pdc_deposit_date_0" readonly id="pdc_deposit_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls"></td>
         
          <td><input type="date" name="pdc_clear_date_0" id="pdc_clear_date_0" min="1970-01-01" max="2099-12-31" class="pdc_clear_date" class="pdc_date_cls"></td>
          <td><input type="date" name="pdc_cancel_date_0" id="pdc_cancel_date_0" min="1970-01-01" max="2099-12-31" class="pdc_date_cls" ></td>
          <td>
            <input type="hidden" name="pdc_reference_0" id="pdc_reference_0" value="">
            <select name="pdc_cancel_reason_0" class="pdc_cancel_reason" id="pdc_cancel_reason_0" disabled>
              <option value="">Select</option>
              <option value="1">Bounce</option>
              <option value="2">Exchange</option>
            </select>
          </td>
          <td>
           <select name="pdc_bounce_reason_0" disabled id="pdc_bounce_reason_0">
            <option value="">Select</option>
            <option value="1">Insufficient Funds</option>
            <option value="2">Signature Missing</option>
            <option value="3">Signature Mismatch</option> 
            <option value="4">Word in amount and figure differ</option>
            <option value="5">Stop Payment</option>
            <option value="6">Refer to Drawer</option>
            <option value="7">Correction</option>   <option value="8">Stale Cheque (Beyond six months)</option>
            <option value="9">Misc</option>
          </select>
        </td>
        <td></td>
        <td><input type="text" name="pdc_cheque_acknowledge_0" id="pdc_cheque_acknowledge_0" value=""></td>
        <td><input type="text" name="pdc_remark_0" id="pdc_remark_0" value=""></td>
      </tr>

      @endforelse

    </tbody>
  </table>
</div>

</div>
</div>

</div>
</div>
</form>
</div>
<input type="hidden" name="rowIds" id="rowIds" value="{{$rowVal}}">
<input type="hidden" name="popId" id="popId" value="" > 
<div class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
     <div class="modal-header">
      <h4 class="modal-title">PDC Exchange</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
     <div class="row">
      <div class="col">
        <div class="card card-box salesSearchBox">

          <!-- <div class="sub-head">Building Type Details</div> -->
          <div class="dataSearchBox ">
            <div class="row">
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Cheque No <span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="text" required name="pdc_check_no" id="pdc_check_no" value="" class="form-control" onkeypress="return isNumber(event)" maxlength="10">                    
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Cheque Dt<span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="date" required class="form-control pdc_date_cls"  name="pdc_check_date" id="pdc_check_date" value="" min="1970-01-01" max="2099-12-31" >                    
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Amount<span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="text" required class="form-control text-right allownumericwithdecimal pdc_amt_cls" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="FormatCurrency(this)" name="pdc_amt" id="pdc_amt" value=""  min="1" step="0.01" maxlength="10">                  
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Stage<span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="number"  class="form-control name="pdc_stage id="pdc_stage" value="" min="1">                 
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Bank Name<span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <select name="bank_id" id="bank_id" class="form-control" required>
                      <option value="">Select</option> 
                      @foreach($bankMaster as $name)
                      <option value="{{$name->id}}">{{$name->bank_code}}</option>
                      @endforeach
                    </select>                 
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group">
                  <label for="tenant_contract_deposit_amt">Rec Dt<span>*</span></label>
                  <div class="p-relative">
                    <i class="fa fa-money icn-add" aria-hidden="true"></i>
                    <input type="date" required class="form-control  name="pdc_recieve_date id="pdc_recieve_date" value="" min="1970-01-01" max="2099-12-31" >               
                  </div>
                </div>
              </div>
              <div class="w-100"></div>
              <div class="col">
                <div class="w-100"></div>
                <button type="button" name="submit" value="exchange" class="btn btn-primary" id="exchange">Exchange</button>
                <button type="button" data-dismiss="modal" value="skip" name="skip" id="skip"  class="btn btn-warning">SKIP</button>
              </div>

            </div>
            
          </div>
          <div class="col-sm-12 text-right">
            <!-- <button type="submit" value="skip"  name="skip" class="btn btn-warning">SKIP</button> -->
          </div>
          <div class="clearfix"></div>
        </form>

      </div>
    </div>
  </div> 

</div>
</div>
<div class="modal-footer"></div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('public/js/jquery.printPage.js')}}"></script>

<script>
$(document).ready(function() {
    
     $('.btnprn').printPage();

// EXchange and bounce popup back to select
$('.modal').on('hidden.bs.modal', function () {
  var pdc_check_no = $('#popId').val();
  if(pdc_check_no)
    $('#pdc_cancel_reason_'+pdc_check_no).prop('selectedIndex',0);  

});

 /////<span class="sl_no">
function slno(){
  $('.sl_no').each(function (index, value) {
   $(this).html(index+1);
  });
  
}   



$(document).on("click",'#process_btn, .addDataRow', function(event){

    if($("#pdc_check_no_0").val()=='' || $("#pdc_check_no_0").val()=='' || $("#pdc_check_date_0").val()=='' || $("#bank_id_0").val()=='' || $("#pdc_stage_0").val()=='' || $("#pdc_recieve_date_0").val()==''){

      alert("Please fill following field- Cheque No, Cheque Date, Stage , Bank Name, Received Date");
      return false;
    }
    else{

      // Add One row line item
      if($(this).attr('id')=='singleRow'){
        event.preventDefault();
        var noOfProcess = 1;
      }
      else{
        var noOfProcess = parseInt($("#process_no").val());
      }

      var $htmlRow = '';
      //var countRow = parseInt($("#existRow").val());
      // var lastRow  = parseInt($("#existRow").val());
      var lastRowId  = $(".chkRow:last").attr('id');
      var lastRow  = parseInt(lastRowId.split('_')[1]);
      var countRow = lastRow + 1;

      $('#existRow').val(lastRow+noOfProcess);

      //var noOfRowExist = (parseInt($("#existRow").val()) == 0)? 0 : parseInt($("#existRow").val())+1;

      var pdc_check_no = parseInt($('#pdc_check_no_'+lastRow).val());
      
       //alert(noOfRowExist);




      //var yyyy = nextMonth.getFullYear();
      //alert(pdc_check_date);

      //pdc_check_date = str.split("-");
      //var year = parseInt(pdc_check_date['1'])+1;
      //var month = parseInt(pdc_check_date['2'])+1;

      var pattern_amt = /^\d{1,10}(,\d{4})*(\.\d+)?$/;
      //alert(pattern_amt);
      var pdc_amt = $('#pdc_amt_'+lastRow).val()?$('#pdc_amt_'+lastRow).val():'';
      var pdc_stage = parseInt($('#pdc_stage_'+lastRow).val());
      var bank_id = parseInt($('#bank_id_'+lastRow).val());
      var pdc_recieve_date = $('#pdc_recieve_date_'+lastRow).val();
      var pdc_deposit_date = $('#pdc_deposit_date_'+lastRow).val();
      var pdc_clear_date = $('#pdc_clear_date_'+lastRow).val();

      var pdc_cancel_date = $('#pdc_cancel_date_'+lastRow).val();
      var pdc_cancel_reason = $('#pdc_cancel_reason_'+lastRow).val();
      var pdc_bounce_reason = $('#pdc_bounce_reason_'+lastRow).val();
      var pdc_type= parseInt($('#pdc_type_'+lastRow).val());
      var pdc_remark= parseInt($('#pdc_remark'+lastRow).val());
      var pdc_cheque_acknowledge= parseInt($('#pdc_cheque_acknowledge'+lastRow).val());
      var pdc_from_date= parseInt($('#pdc_from_date'+lastRow).val());
      var pdc_to_date= parseInt($('#pdc_to_date'+lastRow).val());
      var actualDate,day , month,pdc_check_date ='';

     // alert(noOfProcess+'--'+noOfRowExist);
     var payment_term= $('#payment_term').val();
     var k = parseInt(payment_term); 
      //alert(lastRow);
      for (i = countRow; i <= noOfProcess+lastRow; i++) {

        pdc_check_no = pdc_check_no+1;
        
        if(pdc_check_date==''){

         var actualDate = $('#pdc_check_date_'+lastRow).val();  
         
       }else{

         var actualDate = pdc_check_date;

       }

       actualDate = new Date(actualDate);
       var nextMonth  = new Date(actualDate.getFullYear(), actualDate.getMonth() + k , actualDate.getDate());  

       month = (nextMonth.getMonth() + 1) >= 10 ?(nextMonth.getMonth() + 1) : "0"+(nextMonth.getMonth() + 1);

       day = (nextMonth.getDate()) >= 10 ?(nextMonth.getDate()) : "0"+(nextMonth.getDate());

       pdc_check_date = nextMonth.getFullYear()+'-'+month+'-'+day;
       

       $htmlRow +='<tr class="chkRow" id="tr_'+i+'"><td><input type="hidden" name="pdc_is_saved_'+i+'" value="0" ><a  id="'+i+'" class="dataRow"  href="#" title="Delete"><i class="fa fa-minus" aria-hidden="true"></i></a></td><td><span class="sl_no">'+i+'</span></td><td><input type="text" maxlength="10" name="pdc_check_no_'+i+'" id="pdc_check_no_'+i+'" value="'+pdc_check_no+'" onkeypress="return isNumber(event)" class="pdc_no_cls"></td><td><input type="date"  name="pdc_check_date_'+i+'" id="pdc_check_date_'+i+'" value="'+pdc_check_date+'" min="1970-01-01" max="2099-12-31" ></td><td><input type="text" name="pdc_amt_'+i+'" class="pdc_amt_cls text-right" id="pdc_amt_'+i+'" value="'+pdc_amt+'"   min="1" step="0.01" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" onkeyup="return FormatCurrency(this)" class="text-right" maxlength="10"></td><td><input type="number" min="1" max="100" name="pdc_stage_'+i+'" id="pdc_stage_'+i+'" value="'+pdc_stage+'" class="pdc_stage_cls"><td><select name="pdc_type_'+i+'" id="pdc_type_'+i+'"><option value="1" '+(pdc_type==1?'SELECTED':'')+'>Rent</option><option value="2" '+(pdc_type==2?'SELECTED':'')+'>Deposit</option><option value="3" '+(pdc_type==3?'SELECTED':'')+'>Others</option></select></td></td><td><select name="bank_id_'+i+'" id="bank_id_'+i+'"><option value="">Select</option>';

       @foreach($bankMaster as $name)
       $htmlRow +='<option value="'+'{{$name->id}}'+'"';
       if(bank_id=='{{$name->id}}')
        $htmlRow +=' SELECTED';

      $htmlRow +=' >'+'{{$name->bank_code}}'+'</option>';
      @endforeach 

      $htmlRow +='</select></td><td><input type="date" name="pdc_recieve_date_'+i+'" id="pdc_recieve_date_'+i+'" value="'+pdc_recieve_date+'" min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_from_date_'+i+'" id="pdc_from_date_'+i+'" value="'+pdc_from_date+'"  min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_to_date_'+i+'" id="pdc_to_date_'+i+'" value="'+pdc_to_date+'"  min="1970-01-01" max="2099-12-31" ></td><td><input type="date" readonly name="pdc_deposit_date_'+i+'" id="pdc_deposit_date_'+i+'" value="'+pdc_deposit_date+'"  min="1970-01-01" max="2099-12-31" ></td><td><input type="date" class="pdc_clear_date" name="pdc_clear_date_'+i+'" id="pdc_clear_date_'+i+'" value="'+pdc_clear_date+'" min="1970-01-01" max="2099-12-31"></td><td><input type="date" name="pdc_cancel_date_'+i+'" id="pdc_cancel_date_'+i+'" value="'+pdc_cancel_date+'" min="1970-01-01" max="2099-12-31" ></td><td><input type="hidden" name="pdc_reference_'+i+'" id="pdc_reference_'+i+'" value=""><select name="pdc_cancel_reason_'+i+'" class="pdc_cancel_reason" disabled id="pdc_cancel_reason_'+i+'"><option value="">Select</option><option value="1"'+(pdc_cancel_reason==1?'SELECTED':'')+'>Bounce</option><option value="2" '+(pdc_cancel_reason==2?'SELECTED':'')+'>Exchange</option></select></td><td><select name="pdc_bounce_reason_'+i+'" disabled id="pdc_bounce_reason_'+i+'"><option value="">Select</option><option value="1" '+(pdc_bounce_reason==1?'SELECTED':'')+'>Insufficient Funds</option><option value="2" '+(pdc_bounce_reason==2?'SELECTED':'')+'>Signature Missing</option><option value="3" '+(pdc_bounce_reason==3?'SELECTED':'')+'>Signature Mismatch</option> <option value="4" '+(pdc_bounce_reason==4?'SELECTED':'')+'>Word in amount and figure differ</option><option value="5" '+(pdc_bounce_reason==5?'SELECTED':'')+'>Stop Payment</option><option value="6" '+(pdc_bounce_reason==6?'SELECTED':'')+'>Refer to Drawer</option><option value="7" '+(pdc_bounce_reason==7?'SELECTED':'')+'>Correction</option><option value="8" '+(pdc_bounce_reason==8?'SELECTED':'')+'>Stale Cheque (Beyond six months)</option><option value="9" '+(pdc_bounce_reason==9?'SELECTED':'')+'>Misc</option></select></td><td></td><td><input type="text" name="pdc_cheque_acknowledge_'+i+'" value="" ><a  id="'+i+'" class="dataRow"></a></td><td><input type="text" name="pdc_remark_'+i+'" value="" ><a  id="'+i+'" class="dataRow"></a></td></tr>'; 

          //k = parseInt(k) + parseInt(payment_term);

        }
    
      // alert($htmlRow);
      $("#"+lastRowId).after($htmlRow);
      $("#process_no").val('');
      $('#modal').modal('hide');
    }


    slno();

  });


$(document).on("click",'#exchange', function(){

  if($("#pdc_check_no").val()=='' || $("#pdc_check_date").val()=='' || $("#pdc_amt").val()=='' || $("#pdc_stage").val()=='' || $("#pdc_recieve_date").val()==''){

    alert("Please fill following field- Cheque No, Cheque Date, Stage , Bank Name, Received Date");
    return false;
  }
  else{
    $('#exchange').prop("disabled", true);
            // Add One row line item
            var noOfProcess = 1;
            
            var $htmlRow = '';
            var lastRow = parseInt($("#existRow").val());
            $('#existRow').val(lastRow+noOfProcess)

            var pdc_check_no = $('#pdc_check_no').val();
 
            var pdc_check_date = $('#pdc_check_date').val(); 
            var pdc_amt = $('#pdc_amt').val()?parseInt($('#pdc_amt').val()):'';
            var pdc_stage = parseInt($('#pdc_stage').val());
            var bank_id = parseInt($('#bank_id').val());
            var pdc_recieve_date = $('#pdc_recieve_date').val();
            var pdc_reference = $('#popId').val();


            /*
            var pdc_deposit_date = $('#pdc_deposit_date_'+lastRow).val();
            var pdc_clear_date = $('#pdc_clear_date_'+lastRow).val();

            var pdc_cancel_date = $('#pdc_cancel_date_'+lastRow).val();
            var pdc_cancel_reason = $('#pdc_cancel_reason_'+lastRow).val();
            var pdc_bounce_reason = $('#pdc_bounce_reason_'+lastRow).val();
            var pdc_type= parseInt($('#pdc_type_'+lastRow).val());
            */

            for (i = lastRow; i <= lastRow; i++) {

              var sl_no = i+1;
              $htmlRow +='<tr class="chkRow" id="tr_'+i+'"><td><input type="hidden" name="pdc_is_saved_"'+i+'" value="0" ><a  id="'+i+'" class="dataRow"  href="#" title="Delete"><i class="fa fa-minus" aria-hidden="true"></i></a></td><td>'+sl_no+'</td><td><input maxlength="10" type="text" name="pdc_check_no_'+i+'" id="pdc_check_no_'+i+'" value="'+pdc_check_no+'" onkeypress="return isNumber(event)"></td><td><input type="date"  name="pdc_check_date_'+i+'" id="pdc_check_date_'+i+'" value="'+pdc_check_date+'" min="1970-01-01" max="2099-12-31" ></td><td><input type="text" class="text-right" name="pdc_amt_'+i+'" id="pdc_amt_'+i+'" value="'+pdc_amt+'"  min="1" step="0.01" onkeyup="return FormatCurrency(this)" data-rule-pattern="^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:(\.|,)\d+)?$" data-msg-pattern="Allowed only Numeric and Decimal Values" maxlength="10"></td><td><input type="number"  name="pdc_stage_'+i+'" id="pdc_stage_'+i+'" value="'+pdc_stage+'"  min="1" max="100" ></td><td><select name="pdc_type_'+i+'" id="pdc_type_'+i+'"><option value="1" >Rent</option><option value="2" >Deposit</option><option value="3" >Others</option></select></td><td><select name="bank_id_'+i+'" id="bank_id_'+i+'"><option value="">Select</option>';

              @foreach($bankMaster as $name)
              $htmlRow +='<option value="'+'{{$name->id}}'+'"';
              if(bank_id=='{{$name->id}}')
                $htmlRow +=' SELECTED';

              $htmlRow +=' >'+'{{$name->bank_code}}'+'</option>';
              @endforeach 

              $htmlRow +='</select></td><td><input type="date" name="pdc_recieve_date_'+i+'" id="pdc_recieve_date_'+i+'" value="'+pdc_recieve_date+'" min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_from_date_'+i+'" id="pdc_from_date_'+i+'" value="" min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_to_date_'+i+'" id="pdc_to_date_'+i+'" value="" min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_deposit_date_'+i+'" id="pdc_deposit_date_'+i+'" value="" min="1970-01-01" max="2099-12-31" readonly></td><td><input type="date" name="pdc_clear_date_'+i+'" class="pdc_clear_date" id="pdc_clear_date_'+i+'" value="" min="1970-01-01" max="2099-12-31" ></td><td><input type="date" name="pdc_cancel_date_'+i+'" id="pdc_cancel_date_'+i+'" value="" min="1970-01-01" max="2099-12-31" ></td><td><input type="hidden" name="pdc_reference_'+i+'" id="pdc_reference_'+i+'" value="'+pdc_reference+'"><select name="pdc_cancel_reason_'+i+'" class="pdc_cancel_reason" disabled id="pdc_cancel_reason_'+i+'"><option value="">Select</option><option value="1">Bounce</option><option value="2">Exchange</option></select></td><td><select name="pdc_bounce_reason_'+i+'" disabled id="pdc_bounce_reason_'+i+'"><option value="">Select</option><option value="1" >Signature Missing</option><option value="3" >Signature Mismatch</option> <option value="4" >Word in amount and figure differ</option><option value="5" >Stop Payment</option><option value="6">Refer to Drawer</option><option value="7" >Correction</option><option value="8" >Stale Cheque (Beyond six months)</option><option value="9">Misc</option></select></td><td></td><td><input type="text" name="pdc_remark_'+i+'" id="pdc_remark_'+i+'" value=""></td></tr>'; 


              

            }
            // alert("tr_"+lastRow);
            // alert($htmlRow);
            $("#tr_"+(lastRow-1)).after($htmlRow);
            // To avoid  the exchange rowitem to select
            $("#popId").val('');
            $('.modal').modal('toggle');
            return false;
          }

        });

$(document).on("click",'.dataRow', function(e){

  e.preventDefault(); 
  var rowId = 'tr_'+$(this).attr('id');
  var pdcId = $("#pdc_check_id_"+$(this).attr('id')).val();
  var isSave = $("input:hidden[name=pdc_is_saved_"+$(this).attr('id')+']').val();

        // if(pdcId == undefined){ return false;}
        if($(".chkRow").length==1){
          e.preventDefault();
          if(confirm('Do you want to delete this row?')){
            if(pdcId != undefined && isSave ==1){
              var deleteRow = $("#deleteRow").val();
              deleteRow += pdcId+',';
              $("#deleteRow").val(deleteRow.substring(-1)); 
            }
          }

          var elements = document.getElementsByTagName("input");
          for (var ii=0; ii < elements.length; ii++) {
            if(elements[ii].type=='text' || elements[ii].type=='number'|| elements[ii].type=='date' )
              elements[ii].value = "";

          }
          
          $("select").prop('selectedIndex',0);
          $('input:hidden[name=pdc_is_saved_0]').val(''); 
          
          $('#existRow').val(1);
          slno();

          return false;
        }
        if(confirm('Do you want to delete this row?')){
          e.preventDefault();

          if(pdcId && isSave ==1){
            var deleteRow = $("#deleteRow").val();
            deleteRow += pdcId+',';
            $("#deleteRow").val(deleteRow.substring(-1)); 
          }
          $("#"+rowId).remove();
          var countRow = parseInt($("#existRow").val());
          lastRow      = Math.abs(countRow - 1);
          $('#existRow').val(lastRow);
          slno();
          return false;
        }

        return false;


      });

$(document).on("click",'.save_subt', function(){

  var isDelete = $("#deleteRow").val();
  var arr = [];
  $('#dtBasicExample tr').each(function(){
    if(this.id)
      arr.push(this.id.split('tr_').slice(1));
  })
  for($i=0;$i<=arr.length;$i++){
    if(($("#pdc_check_no_"+$i).val()=='' || $("#pdc_check_date_"+$i).val()=='' || $("#bank_id_"+$i).val()=='' || $("#pdc_stage_"+$i).val()=='' || $("#pdc_amt_"+$i).val()=='' || $("#pdc_recieve_date_"+$i).val()=='')){

      alert("Please fill following field- Cheque No, Cheque Date, Stage , Bank Name, Received Date");
      return false;
    }
  }

});

$(document).on("change",'.pdc_clear_date', function(){
    var pdcClearReason = $(this).attr('id');
    var pdcClearReasonVal = $(this).val();
    var lastRow = parseInt($("#existRow").val());
    var idName  = pdcClearReason.split('pdc_clear_date_').slice(1);
    
    if(pdcClearReasonVal){
      $('#pdc_deposit_date_'+idName).prop("readOnly", true);
      $('#pdc_from_date_'+idName).prop("readOnly", true);
      $('#pdc_to_date_'+idName).prop("readOnly", true);
      $('#pdc_cancel_date_'+idName).prop("readOnly", true);
      $('#pdc_cancel_reason_'+idName).prop('selectedIndex',0);
      $('#pdc_cancel_reason_'+idName).prop("disabled", true);
      $('#pdc_bounce_reason_'+idName).prop('selectedIndex',0);
      $('#pdc_bounce_reason_'+idName).prop("disabled", true);
      $('#pdc_cheque_acknowledge_'+idName).prop("readOnly", true);
      $('#pdc_remark_'+idName).prop("readOnly", true);
    }
    else{
      $('#pdc_deposit_date_'+idName).prop("readOnly", false);
      $('#pdc_from_date_'+idName).prop("readOnly", false);
      $('#pdc_to_date_'+idName).prop("readOnly", false);
      $('#pdc_cancel_date_'+idName).prop("readOnly", false);
      $('#pdc_cancel_reason_'+idName).prop("readOnly", true);
      $('#pdc_cancel_reason_'+idName).prop('selectedIndex',0);
      $('#pdc_cancel_reason_'+idName).prop("disabled", false);
      $('#pdc_bounce_reason_'+idName).prop('selectedIndex',0);
      $('#pdc_bounce_reason_'+idName).prop("disabled", false);
      $('#pdc_cheque_acknowledge_'+idName).prop("readOnly", false);
      $('#pdc_remark_'+idName).prop("readOnly", false);

    }

});

$(document).on("change",'.pdc_cancel_reason', function(){

  var activeReasonDesc = $(this).attr('id');
  var pdc_cancel_reason = $(this).val();
  var lastRow = parseInt($("#existRow").val());


  var idName  = activeReasonDesc.split('pdc_cancel_reason_').slice(1);

  var pdcId   = $('#pdc_check_id_'+idName).val();

          if(pdc_cancel_reason == 2){ // Exchange

            $('#pdc_bounce_reason_'+idName).prop('selectedIndex',0);
            $('#pdc_bounce_reason_'+idName).prop("disabled", true);
            if($('#pdc_check_no_'+idName).val()==''){
              alert("Please enter the check No");
              return false;
            }
            else{

              var pdc_check_no = $('#pdc_check_no_'+idName).val();

              $('#pdc_check_no_'+idName).prop("readOnly", true);
              $('#exchange').prop("readOnly", false);
              $('#popId').val(idName);

              // $("#tr_"+idName).append('input type="hidden" name="pdc_reference" id="pdc_reference" value="'+pdc_check_no+'"');

              $('.modal').modal({backdrop: 'static', keyboard: false}).find("input,textarea,select")
              .val('').end();

              return false;

            }

          }
          else if(pdc_cancel_reason == 1){ // Bounce
            $('#pdc_check_no_'+idName).prop("readOnly", false);
            $('#pdc_bounce_reason_'+idName). prop("disabled", false);

          } 
          else{

            $('#pdc_bounce_reason_'+idName).prop('selectedIndex',0);
            $('#pdc_bounce_reason_'+idName). prop("disabled", true);

          }
          //alert(idName+"--"+pdc_cancel_reason);

        });
$(document).on("keyup click",'#process_no', function(){

  if($('#process_no').val().length > 0){

    $('#process_btn').prop('disabled', false);
  } else {
    $('#process_btn').prop('disabled', true);
  }


});
$(document).on("keyup click",'#process_no', function(){

  if($('#process_no').val().length > 0){

    $('#process_btn').prop('disabled', false);
  } else {
    $('#process_btn').prop('disabled', true);
  }


})


});


function changeroute(stg){
  // {{route('PdcprintPreview',$tenantContract->id)}}
  var contract = $("#contract_id").val();
  console.log(contract);
  console.log(stg);
  $(".btnprn").attr('href',"../printPreview/"+contract+"/"+stg);
  
}

</script>
@endsection





