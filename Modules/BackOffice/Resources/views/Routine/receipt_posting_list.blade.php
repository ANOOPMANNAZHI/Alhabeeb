@extends('layouts.plms-app')



@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
#cover-spin {
    position:fixed;
    width:100%;
    left:0;right:0;top:0;bottom:0;
    background-color: rgba(255,255,255,0.7);
    z-index:9999;
    display:none;
}

@-webkit-keyframes spin {
  from {-webkit-transform:rotate(0deg);}
  to {-webkit-transform:rotate(360deg);}
}

@keyframes spin {
  from {transform:rotate(0deg);}
  to {transform:rotate(360deg);}
}

#cover-spin::after {
    content:'';
    display:block;
    position:absolute;
    left:48%;top:40%;
    width:40px;height:40px;
    border-style:solid;
    border-color:black;
    border-top-color:transparent;
    border-width: 4px;
    border-radius:50%;
    -webkit-animation: spin .8s linear infinite;
    animation: spin .8s linear infinite;
}


</style>

<div id="cover-spin"></div>

<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Tenant Receipt Posting</div>
    </div> 
    {{ Breadcrumbs::render('tenantReceiptPosting') }} 
  </div>
</div>

<div class="row">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
     @include('backoffice::Routine.receipt_search') 
   </div>
 </div>
</div>


<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
    <h4><b>Receipt List</b></h4>
     <h4>
       <button type="button" class="btn btn-circle btn-primary receiptPost align-right">Post</button>
       <div class="clr"></div>
     </h4>
         <div class="table-wrap">
 <div class="table-responsive">	
      <table class="table display product-overview mb-30" id="">
        <thead>
          <tr>
           <th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
            <th>Sl No.</th>
            <th>Rec No</th>
            <th>Date</th>
            <th>Bldg.Name</th>
            <th>Bldg.Code</th>
            <th>Unit No</th>
            <th>Tenant Name</th>
            <th>Tenant Code</th>
            <th>Agr No</th>
            <th>Payment Method</th>
            <th>Amt</th>
            <th>Receipt</th>
            <th>Bank</th>
            <th>Dim1</th>
            <th>Dim2</th>
            <th>Dim3</th>
            <th>Dim4</th>
            <th>Dim5</th>
          </tr>
        </thead>
        <tbody>
         @php 


         $curr_loop = 1;

         if(\Request::input('curr_url'))
         $curr_url =  \Request::input('curr_url');
         else
         $curr_url =  url()->current();               
         
         @endphp 
         @forelse ($receipts as $receipt) 
         <tr>
          <td><input type ="checkbox" id ="checkItem" class ="mdl-switch__input sub_chk" value="{{ $receipt->id }}"></td>
         <td>{{ $curr_loop + $loop->index }}</td>
          <td>{{ $receipt->receipts_generation_receipt_no ?? ''}}</td>
          <td>{{ $receipt->receipts_generation_receipt_date->format('d/m/Y') ?? ''}}</td>
          <td>{{ $receipt->tenantContractInfo->building->building_name ?? ''}}</td>
          <td>{{ $receipt->tenantContractInfo->building->building_code ?? ''}}</td>
          <td>{{ $receipt->tenantContractInfo->Unit->unit_code ?? ''}}</td>
          <td>{{ $receipt->tenantContractInfo->tenant->tenant_name ?? ''}}</td>
          <td>{{ $receipt->tenantContractInfo->tenant->tenant_code ?? ''}}</td> 
          <td>{{ $receipt->tenantContractInfo->tenant_contract_no ?? ''}}</td> 
          <td>{{ $receipt->receipts_generation_payment_method_name ?? ''}}</td>
          <td>{{ numberFormat($receipt->receipts_generation_amt) ?? ''}}</td>
          <td>{{ $receipt->receipts_generation_type_name ?? ''}}</td>
          <td>{{ $receipt->bankInfo->bank_name ?? ''}}</td>
          <td>{{ $receipt->dim1}}</td>
          <td>{{ $receipt->dim2}}</td>
          <td>{{ $receipt->dim3}}</td>
          <td>{{ $receipt->dim4}}</td>
          <td>{{ $receipt->dim5}}</td>
        </tr>  

        @empty 
        <tr>
          <td colspan="18" align="center">
            <p>No Record</p>
          </td>
        </tr>
        @endforelse 
        @if(!empty($totalamt)) 
        <tr>
          <td colspan="6" align="center"></td>
          <td colspan="6" align="center">Total :<b> {{ $totalamt }} </b></td>
        </tr>
         @endif 
      </tbody>
    </table>
  </div>
     </div> 
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

   $('.receiptPost').on('click', function(e) {  



        var allVals = []; 
        $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
           // alert(allVals);
        });
         if(allVals.length <=0)  
        {  
            alert("Please Select Atleast One Receipt..!"); return false;  
            
        }else {  
            event.preventDefault();
             $('#cover-spin').show(0);
            //do something
           $(this).prop('disabled', true);
           $(this).css('display', 'none');

            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('receiptPost')}}", // This is the url we gave in the route
                data: {'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed

                   $('#cover-spin').hide(0);

        					if(response =='Error'){
                    alert('Microsoft Dynamics API Service Error');
                    $(this).css('display', 'block');

                  }
        						
        					else{
                          alert(response);
                           // $("#myModal").html(response); 
                            location.reload();
                  }
						
                },
            });
             
        } 

      });
/*********************************************************************/
  $.validator.addMethod("greaterThan", 
    function(value, element, params) {
      
        if (!/Invalid|NaN/.test(new Date(value))) {
            return new Date(value) >= new Date($(params).val());
        }

        return isNaN(value) && isNaN($(params).val()) 
            || (Number(value) >= Number($(params).val())); 
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
