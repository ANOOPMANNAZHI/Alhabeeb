@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/tokenize2.min.css')}}">
@endsection
@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
  }

  input#select_all {
   background: #adc6d8;
}


select#select-meal-type {
   color: #888f94;
   margin: 0px 0 0px 0px;
   padding: 10px 0px 20px 19px;
}
</style>
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Rental Income Posting</div>
    </div> 
    {{ Breadcrumbs::render('rentalIncomePosting') }} 
  </div>
</div>

<div class="row">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
     @include('backoffice::Routine.rental_search') 
   </div>
 </div>
</div>


<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
     <h4>
       <button type="button" class="btn btn-circle btn-primary rentalPost align-right">Post</button>
       <div class="clr"></div>
     </h4>
             <div class="table-wrap">
 <div class="table-responsive">	
      <table class="table display product-overview mb-30" id="">
        <thead>
          <tr>
           <th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
           <th>Sl No.</th>
           <th>Invoice No</th>
           <th>Invoice Date</th>
           <th>Building Name</th>
           <th>Building Code</th>
           <th>Unit No</th>
           <th>Tenant Name</th>
           <th>Tenant Code</th>
           <th>Agr No</th>
           <th>Invoice Amount</th>
         </tr>
       </thead>
       <tbody>
         @php 


         $curr_loop =  1;

         if(\Request::input('curr_url'))
         $curr_url =  \Request::input('curr_url');
         else
         $curr_url =  url()->current();               
         
         @endphp 
         @forelse ($invoices as $invoice) 
         <tr>
           <td><input type ="checkbox" id ="checkItem" class ="mdl-switch__input sub_chk" value="{{ $invoice->id }}"></td>
           <td>{{ $curr_loop + $loop->index }}</td>
           <td>{{ $invoice->tenant_invoice_no}}</td>
           <td>{{ $invoice->tenant_invoice_date->format('d/m/Y')}}</td>
           <td>{{ $invoice->tenantContractInfo->building->building_name}}</td>
           <td>{{ $invoice->tenantContractInfo->building->building_code}}</td>
           <td>{{ $invoice->tenantContractInfo->Unit->unit_code}}</td>
           <td>{{ $invoice->tenantContractInfo->tenant->tenant_name}}</td>
           <td>{{ $invoice->tenantContractInfo->tenant->tenant_code}}</td> 
           <td>{{ $invoice->tenantContractInfo->tenant_contract_no}}</td> 
           <td>{{ numberFormat($invoice->tenant_invoice_amt)}}</td>
         </tr>  
         @empty 
         <tr>
          <td colspan="11" align="center">
            <p>No Record</p>
          </td>
        </tr>
        @endforelse 
        @if(!empty($totalamt)) 
        <tr>
          <td colspan="6" align="center"></td>
          <td colspan="6" align="center">Total <b> {{ $totalamt }} </b></td>
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
<script src="{{ asset('public/js/jquery-ui.js') }} "></script><!-- https://code.jquery.com/ui/1.12.1/jquery-ui.js -->
<script src="{{ asset('public/js/tokenize2.min.js') }}" ></script>
<script>
  $(document).ready(function() {
   $("#checkAll").click(function () {
     $('input:checkbox').not(this).prop('checked', this.checked);
   });

   /*********************************************************************/

   $('.rentalPost').on('click', function(e) {  

    var allVals = []; 
    $(".sub_chk:checked").each(function() {  
      allVals.push($(this).attr('value'));
           // alert(allVals);
         });
    if(allVals.length <=0)  
    {  
      alert("Please Select Atleast One Invoice..!"); return false;  
      
    }else {  
       event.preventDefault();
       //do something
       $(this).prop('disabled', true);
       $(this).css('dsiplay','none');
      $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('rentalPost')}}", // This is the url we gave in the route
                data: {'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                   // $("#myModal").html(response); 
                   location.reload();
                 },
               });
      
    } 

  });

   /*********************************************************************/
   /*********************************************************************/
   
   $('#select_all').click(function() {
    $('#select-meal-type option').prop('selected', true);
  });
   /*********************************************************************/
   $('.rental_search').click(function(){
    var el = $(this);
    var fromdate = $('#fromdate').val();
    var todate = $('#todate').val();
    if(fromdate != '' && todate != ''){
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
     $(".error").hide();
     $(".error1").hide();
   }
   else if(fromdate != '' && todate == ''){
    $(".error").css("display","block").css("color","red");
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);
  }
  else if(fromdate == '' && todate != ''){
    $(".error1").css("display","block").css("color","red");
    el.prop('disabled', true);
    setTimeout(function(){el.prop('disabled', false); }, 3000);


  }else{
   

  }
});
   /*********************************************************************/

 });
   /*************************************************************************/
  $('.tokenize-remote-demo1').on("tokenize:tokens:add", function (event, value, text){

    if(value){

      $("#building_id-error").hide();
    }


  });
  /*************************************************************************/
  $('.tokenize-remote-demo1').on("tokenize:tokens:remove", function (event, value, text){
    $('#building_id').valid();
  });




  /**********************************************************************/
  $('.tokenize-remote-demo1').tokenize2({

    placeholder: " &nbsp;&nbsp; Type The Letter For Building Name",
    dataSource: function(term, object){
      $.ajax('{{route("rentalBuildingAutocomplete")}}', {
        data: { search: term, start: 0 },
        dataType: 'json',
        success: function(data){
          var $items = [];
          $.each(data, function(k, v){
            $items.push(v);
          });
          object.trigger('tokenize:dropdown:fill', [$items]);

        }
      });
    }
  });

  /*************************************************************************/
</script>  
@endsection
