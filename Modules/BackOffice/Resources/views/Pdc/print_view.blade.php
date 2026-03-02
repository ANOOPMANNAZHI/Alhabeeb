<?php
// Create a function for converting the amount in words
function numberTowordsas(float $amount)
{
   $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
   // Check if there is any number after decimal
   $amt_hundred = null;
   $count_length = strlen($num);
   $x = 0;
   $string = array();
   $change_words = array(0 => '', 1 => 'One', 2 => 'Two',
     3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
     7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
     10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
     13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
     16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
     19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
     40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
     70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
  $here_digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
  while( $x < $count_length ) {
       $get_divider = ($x == 2) ? 10 : 100;
       $amount = floor($num % $get_divider);
       $num = floor($num / $get_divider);
       $x += $get_divider == 10 ? 1 : 2;
       if ($amount) {
         $add_plural = (($counter = count($string)) && $amount > 9) ? 's' : null;
         $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
         $string [] = ($amount < 21) ? $change_words[$amount].' '. $here_digits[$counter]. $add_plural.' 
         '.$amt_hundred:$change_words[floor($amount / 10) * 10].' '.$change_words[$amount % 10]. ' 
         '.$here_digits[$counter].$add_plural.' '.$amt_hundred;
         }else $string[] = null;
       }
   $implode_to_Rupees = implode('', array_reverse($string));
   $get_paise = ($amount_after_decimal > 0) ? "And " . ($change_words[$amount_after_decimal / 10] . " 
   " . $change_words[$amount_after_decimal % 10]) . '' : '';
   return ($implode_to_Rupees ? $implode_to_Rupees . 'XXX/1000 ' : '') ;
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='UTF-8'>
<title>PDC Print</title>
<link rel='stylesheet' href="{{asset('public/css/print_style.css')}}">
<link rel='stylesheet' href="{{asset('public/css/print.css')}}" media="print">
</head>
<body>
<div id="page-wrap1" style="width:900px;margin:0 auto;">

<div style="clear:both"></div>
<div id="header"><b>Al Habib & Co. L.L.C</b>
</div>
<div id="header">CHEQUE RECEIPT VOUCHER</div>
<div id="header2" style="display: flex;">
<div id="item_left" style="width: 61%;">Date : {{ Date('d/m/Y')}}</div>
<div id="item_right" style="text-align: right;width: 40%;">CRV No: {{$tenantContract->building->building_code}}/{{$tenantContract->unit->unit_no}}</div>
</div>

<style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;

}
#customers th {
  background-color: #dddddd;

}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>
<div id="items_no_side_border" style=" border-top: 1px solid; ">
  @php
  if($tenantContract->tenant_contract_payment_type == 4)
  $payment_term = 6;
  elseif($tenantContract->tenant_contract_payment_type == 5)  
  $payment_term = 12;
  else
  $payment_term = $tenantContract->tenant_contract_payment_type;

  $totalpdc = count($tenantPdcInfo);
  $totalAmt = 0;
  
  $fdate = Date('Y-m-d', strtotime($tenantPdcInfo[0]['pdc_from_date']));
  $e_date_old = date('Y-m-d', strtotime($fdate. ' + 1 year'));
  $e_date = date('Y-m-d', strtotime($e_date_old. ' - 1 day'));

  foreach($tenantPdcInfo as $key=>$item){
    $totalAmt = $totalAmt+ $item->pdc_amt;

    if($item->pdc_from_date != null){
      $fdate =  $item->pdc_from_date;
    }
    
    if($item->pdc_to_date != null){
      $e_date =  $item->pdc_to_date;
    }
    

  }

    

  
  $get_amount= numberTowordsas($totalAmt);
  
  
  $bankName = $tenant_bank[0]->bank_name;
  
  
  
 
  

  @endphp

   

  {{csrf_field()}} <div class="item-row"> <table style="width: 100%;"> <tr>
<td>Name</td> <td>: {{$tenantContract->tenant->tenant_name}}</td> <td
style="width: 100px;"></td> <td> <table style="width: 100%;"> <tr> <td>
</td> <td> </td> </tr> </table> </td> </tr> <tr> <td>Bldg
Code</td> <td>: {{$tenantContract->building->building_code}}</td> <td> <table>
<tr> <td>Bldg Name</td> <td>:
{{$tenantContract->building->building_name}}</td> </tr> </table> </td> <td>
<table style="width: 100%;"> <tr> <td>Unit No</td> <td>: {{$tenantContract->unit->unit_no}}</td> </tr>
</table> </td> </tr> <tr> <td>No Of Cheques</td> <td>: {{$totalpdc}}</td> <td> <table>
<tr> <td>Bank</td> <td>: {{$bankName}}</td> </tr> </table> </td>
        
      </tr>
      <tr>
        <td>Total Amount(R.O)</td>
        <td>: {{number_format($totalAmt, 3)}}</td>
        <td>
          <table style="width: 150%;">
            <tr>
              <td>Rial Omani</td>
              <td>:{{$get_amount}}</td>
            </tr>
          </table>
        </td>
        
      </tr>
      <tr>
        <td>Rent P/M</td>
        <td>: {{isset($tenantContract->tenant_contract_rent)?numberFormat($tenantContract->tenant_contract_rent)." ":"NA"}}</td>
        <td>
          <table>
            <tr>
              <td>Payment Mode</td>
              <td>: {{$tenantContract->TenantContractPaymentName}}</td>
            </tr>
          </table>
        </td>
        
      </tr>
      <tr>
        <td>Rent/Deposit/Others</td>
        <td>:  Rent</td>
      </tr>
      <tr>
        <td>Receipt Period From</td>
        <td>: {{ Date('d/m/Y',strtotime($fdate)) }} &nbsp;&nbsp; &nbsp;  To : {{ Date('d/m/Y',strtotime($e_date)) }}

          
        </td>

        <td style="width: 300px;"></td>
        
      </tr>
      <tr>
        <td>Contract Period From</td>
        <td>: {{ $tenantContract->tenant_contract_start_date->format('d/m/Y')}}   &nbsp;&nbsp; &nbsp;  To :   {{$tenantContract->tenant_contract_valid_to_date->format('d/m/Y')}}</td>
        <td style="width: 300px;"></td>
        
      </tr>
      <br>
      <tr>
        <td>Agreement No</td>
        <td>: {{$tenantContract->tenant_contract_no}}</td>
      </tr>

      <tr>
        <td>Stage</td>
        @if($stage == 0)
          <td>: ALL</td>
        @elseif($stage != 0)
           <td>: {{$stage}}</td>
        @endif
      </tr>

    </table>
   

  </div>

   <table id="customers">
    <thead>
      <tr>
        <td>Cheque No</td>
        <td>Cheque Date</td>
        <td>Amount</td>
        <td>Remarks</td>
      </tr>
    </thead>
    <tbody>
       @foreach($tenantPdcInfo as $key=>$item)
          

        <tr>   
            
            <?php 

               if(isset($item->pdc_remark)){

                   if($item->pdc_remark == "NULL"){
                    $remrk = "";
                   }
                   else{
                    $remrk = $item->pdc_remark;

                   }
                
               }
               else{
                $remrk = "--";
               }

            ?>
            
            <td>{{$item->pdc_check_no}}</td>
            <td>{{$item->pdc_check_date->format('d-m-Y')}}</td>
            <td style="text-align: right;">{{number_format($item->pdc_amt, 3)}}</td>
            
            <td>{{$remrk}}</td>
          
            
        </tr>
        @endforeach
        <tr>
          <td colspan="2" style="text-align: right;"><b>Total</b></td>
          <td style="text-align: right;"><b>{{number_format($totalAmt, 3)}}</b></td>
          <td></td>
        </tr>
    </tbody>
   </table>
<br>
<br>
<br>
<br>
<div class="item-row">
  <table  style="width: 100%;">
    <tr>
      <td>Collected By (ARE/Sales)</td>
      <td></td>
      <td></td>
      <td></td>
      <td style="width: 400px;"></td>
      <td>
        <table  style="width: 100%;">
          <tr>
            <td>Received By (Finance Department)</td>
            <td></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
<div class="item-row">
  <p><i><b>*** Receipt is valid subject to realisation of cheque. ***</b></i></p>
</div>

</div>

</html>