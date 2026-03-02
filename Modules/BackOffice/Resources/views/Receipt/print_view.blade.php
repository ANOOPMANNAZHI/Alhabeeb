<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>Editable Invoice</title>
<link rel='stylesheet' href="{{asset('public/css/print_style.css')}}">
<link rel='stylesheet' href="{{asset('public/css/print.css')}}" media="print">
</head>
<body>
<div id="page-wrap">

<div id="identity">
<div id="logo" style="margin-top:0px" >
  <img id="image" src="{{asset('public/img/logo-print.png')}}" alt="logo"  style="height:70px;width:250px"/></div>
<div id="address" style="width:500px;margin-bottom:10px">
<b style="text-align:right">الحبيب وشركاه ش . م .م</b></br>
<b>Al Habib & Co. L.L.C</b></br>
<span style="text-align:right">
ص. ب: 2663, روي الرمز البريدي : 112 , مسقط , سلطنة عمان
</span></br>
<span style="text-align:right">
هاتف :96824700247+|فاكس :96824703666+| س.ت. :1131575|المالية 10317401
</span></br>
P.O. Box 2663, Ruwi 112, Muscat, Sultanate of Oman
</br>
Tel:+968 247 00247 | Fax:+968 247 03 666 
</br> C.R. No: 1131575 | Finance code : 10317401 
</div>
</div>
<div style="clear:both"></div>
<div id="header">RECEIPT VOUCHER</div>
<div id="items">
  <div id="head-line">
    <div class="item-no"><label>No. :</label> {{$receiptInfo->receipts_generation_receipt_no}}</div>
    <div class="item-date"><label>Date :</label>
		@if(isset($receiptInfo->pdc->pdc_deposit_date))
		{{ $receiptInfo->pdc->pdc_deposit_date->format('d/m/Y') }}

		@else
		{{ $receiptInfo->receipts_generation_receipt_date->format('d/m/Y') }}

		@endif
	
	</div>
  </div>
  <div class="item-row">
  </br>
    <div class="item-full-width"><label>Received with thanks from: </label> {{$receiptInfo->tenantContractInfo->tenant->tenant_name}}</div>
	</br>
  </div>
  <div class="item-row">
    <div class="item-full-width"><label>By Cash / Cheque No.:</label> 
      {{($receiptInfo->receipts_generation_payment_method ==2 )?'Cash':$receiptInfo->receipts_generation_cheque_no}}</div></br>
  </div>
  <div class="item-row">
    <div class="item-full-width"><label>The sum of Rials Omani :</label> {{ucwords($inWords)}} </div></br>
  </div>
  <div class="item-row">
      <div class="item-full-height"><label>Being: </label>
	  @if(isset($receiptInfo->receipts_generation_remark))
        {{$receiptInfo->receipts_generation_remark}}
        @elseif(isset($receiptInfo->receipts_generation_description))
        {{$receiptInfo->receipts_generation_description}}
        @endif
		</div></br>
  </div>
  @if(isset($pdcInfo->pdc_check_no))
  <div class="item-row">
      <div class="item-full-height"><label>Comment: </label>
    
        {{' Cash/B.T against '.$pdcInfo->bankInfo->bank_code.$pdcInfo->pdc_check_no.' dated '.$pdcInfo->pdc_check_date}}       
    
    </div>
  </div>
  @endif
  <div id="head-line">  
    <div class="item-no"><label>R.O.: </label>
      <span class="item-box">{{numberFormat($receiptInfo->receipts_generation_amt)}} 
                                </span>
    </div>
    <div class="item-date"><label>For Al Habib & Co. L.L.C</label></div>
  </div>
  <div class="item-row">
  </br>
    <div class="item-full-width"><label>Please keep all the receipts for future reference. Receipt is valid subject to realization of cheque.</label></div>
  </div>
</div>

</html>