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
      <div class="page-title">Landlord Invoice Bulk Posting</div>
    </div> 
    {{ Breadcrumbs::render('costRecognition') }} 
  </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
     @include('backoffice::Routine.cost_search') 
   </div>
 </div>
</div>


<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">
	 <form id="costpostForm" action="{{route('costPost')}}" method="POST">
	  @csrf
    <div class="card-body ">
     <h4>
      <button type="submit" class="btn btn-circle btn-primary costPost align-right">Post</button>
       <div class="clr"></div>
     </h4>
     <div class="table-wrap">
 <div class="table-responsive">	
      <table class="table display product-overview mb-30" id="">
        <thead>
          <tr>
          <th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
            <th>Sl No.</th>
            <th>Voucher No.</th>
            <th>Building Name</th>
            <th>Landlord Name</th>
            <th>Landlord Code</th>
            <th>Agr No</th>
            <th>Period</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          @php $count = 1; @endphp
         @forelse ($invoices as $invoice) 
		    @foreach($invoice->landlordInvoiceDistributionBreakup as $itm )
         @if($itm->credit_amount>0)
         <tr>
         <td><input type ="checkbox" name="checkItem[]" id ="checkItem" class ="mdl-switch__input sub_chk" value="{{$itm->id.','.$invoice->id}}"></td>
         <td>{{ $count }}</td>
          <td>{{ $invoice->landlord_invoice_voucher_no}}</td>
          <td>{{ $invoice->landlordContractInfo->buildingInfo->building_name}}</td>
          <td>{{ $invoice->landlordContractInfo->vendorInfo->vendor_name}}</td>
          <td>{{ $invoice->landlordContractInfo->vendorInfo->vendor_code}}</td> 
          <td>{{ $invoice->landlordContractInfo->landlord_contract_no}}</td> 
          <td>{{\Carbon\Carbon::parse( $itm->date)->format('m')}}</td>
		  <td>
			
				{{ numberFormat(($itm->credit_amount > 0)?$itm->credit_amount:$itm->debit_amount)}}
			
		</td>
        </tr>  
		@php $count++; @endphp 
		@endif
     @endforeach   
        @empty 
        <tr>
          <td colspan="9" align="center">
            <p>No Record</p>
          </td>
        </tr>
        @endforelse 
        @if(!empty($totalamt)) 
        <tr>
          <td colspan="8" align="right">Total :</td>
          <td colspan="1" align="right"><b> {{  numberFormat($totalamt) }} </b></td>
        </tr>
         @endif 
      </tbody>
    </table>
  </div>
  </div>
 </form>
</div>
</div>
</div>
</div>
<form id="delete-form" action="" method="POST">
  {{ method_field('DELETE') }}  {{csrf_field()}}
  <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts')  
<script>
  $(document).ready(function() {
   $("#checkAll").click(function () {
     $('input:checkbox').not(this).prop('checked', this.checked);
   });
/*********************************************************************/

   $('.costPost').on('click', function(e) {  

        var allVals = []; 
        $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
          
        });
         if(allVals.length <=0)  
        {  
            alert("Please Select Atleast One Invoice..!"); return false;  
            
        }else {  
			event.preventDefault();
              //do something
      $(this).prop('disabled', true);
			$(this).css('display','none');
			$( "#costpostForm" ).submit();
           /* $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('costPost')}}", // This is the url we gave in the route
                data: {'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                   // $("#myModal").html(response); 
                   location.reload();
                },
            });
            */ 
        } 

      });

/*********************************************************************/
  $.validator.addMethod("greaterThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) > new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value) > Number($(params).val())); 
    },'Must be greater than End Date.');
    $("#search_form").validate({
            rules: {
                todate: { greaterThan: "#fromdate" ,
                
            }
              

        }
    });
/*********************************************************************/
 });
</script>  
@endsection
