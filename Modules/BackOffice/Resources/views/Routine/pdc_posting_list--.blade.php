@extends('layouts.plms-app')



@section('content')
<style>
  input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}




.pad {
    float: right;
    margin:5px 0 0 0;
}
</style>
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">PDC Bulk Posting</div>
    </div> 
    {{ Breadcrumbs::render('pdcPosting') }} 
  </div>
</div>

<div class="row">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
      @include('backoffice::Routine.pdc_search')   
    </div>
  </div>
</div>
 <form autocomplete="off" action="{{route('pdcPost')}}" method="POST" id="tenant_contract_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
{{csrf_field()}}
<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">

    <div class="card-body ">
      <div class="card-head">

      <div class="col-md-5 col-md-offset-5"></div>
      <label for="fieldName">Bank:</label>
      <div class="col-sm-3">
        <div class="form-group">
          <div class="p-relative">
           <select class="form-control" name="bank_id" required style="
           border: #33333329 solid 1px;
           ">
           <option value="">Select </option>
        @foreach($banks as $bank)
        <option  value="{{$bank->id}}">{{$bank->bank_name }} - {{ $bank->bank_code}}</option>
        @endforeach
         </select>
       </div>
     </div>
   </div>
   <label for="fieldValue">Deposit:</label>
   <div class="col-sm-1">
    <div class="form-group">
      <div class="p-relative">
     
       <input type="date" required  name="pdc_deposit_date" id="pdc_deposit_date" placeholder="Enter Value" style="
       border: #33333329 solid 1px;
       ">
     </div>
   </div>
 </div>
 <div class="pad">
   <span>
     <button type="submit" class="btn btn-circle btn-primary pdcPost align-right">Post</button>
   </span>
 </div>
</div>
<div style="overflow-x:auto;">
 

  <table class="table display product-overview mb-30" id="">
    <thead>
      <tr>
       <th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
        <th>Sl No.</th>
        <th>@sortablelink('pdc_check_no','Cheque No') </th>
        <th>Cheque Date</th>
        <th>Receipt Type</th>
        <th>@sortablelink('building_id','Building Name',[],[ 'class' => 'bankInfo' ])</a></th>
        <th>Building Code</th>
        <th>Unit No</th>
        <th>Tenant Name</th>
        <th>Tenant Code</th>
        <th>Agr No</th>
        <th>@sortablelink('bankInfo.bank_name','Bank',[],[ 'class' => 'bankInfo' ]) </th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody>

      @php $count = 1; @endphp
      <input type="hidden" name="search_count" value="{{count($pdc)}}" >
      <input type="hidden" name="row_collection" value="0" id="row_collection">
      @forelse ($pdc as $key=>$pdcData) 
      <tr>
        <td><input type ="checkbox" id ="checkItem" name="pdc_id[]" class ="mdl-switch__input sub_chk" value="{{ $pdcData->id }}">
        <input type="hidden" name="contract_id_{{$pdcData->id}}" value="{{ $pdcData->tenant_contract_id }}" >
        </td>
        <td>{{$pdc->perPage()*($pdc->currentPage()-1)+$count}}</td>
        <td>{{ $pdcData->pdc_check_no}}
        <input type="hidden" name="pdc_check_no_{{$pdcData->id}}" value="{{ $pdcData->pdc_check_no}}" >
        <input type="hidden" name="pdc_type_{{$pdcData->id}}" value="{{ $pdcData->pdc_type}}" >
        </td>
        <td>{{ $pdcData->pdc_check_date->format('d/m/Y') ?? ''}}
        <input type="hidden" name="pdc_check_date_{{$pdcData->id}}" value="{{ $pdcData->pdc_check_date->format('d/m/Y') ?? ''}}" >
        </td>
        <td>{{ $pdcData->pdc_type_name }}</td>
        <td>{{ $pdcData->tenantContractInfo->building->building_name}}</td>
        <td>{{ $pdcData->tenantContractInfo->building->building_code}}</td> 
        <td>{{ $pdcData->tenantContractInfo->Unit->unit_code}}</td>
        <td>{{ $pdcData->tenantContractInfo->tenant->tenant_name}}</td>
        <td>{{ $pdcData->tenantContractInfo->tenant->tenant_code}}</td> 
        <td>{{ $pdcData->tenantContractInfo->tenant_contract_no}}</td>
        <td>{{ $pdcData->bankInfo->bank_name}}</td>
        <td>{{ numberFormat($pdcData->pdc_amt)}}
         <input type="hidden" name="pdc_amt_{{$pdcData->id}}" value="{{$pdcData->pdc_amt}}" ></td>


      </tr>  
	  @php $count++; @endphp
      @empty 
      <tr>
        <td colspan="13" align="center">
          <p>No Record</p>
        </td>
      </tr>
      @endforelse 
      @if(!empty($totalamt))
      <tr>
        <td colspan="12" align="right">Total</td>
        <td  align="center"> <b>{{ numberFormat($totalamt) }}</b></td>
      </tr>
      @endif
    </tbody>
  </table>
	{{$pdc->withPath($route)->appends(\Request::except(['page','_token']))->links()}}
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

   $(function(){
        var dtToday = new Date();

        var month = dtToday.getMonth() + 1;
        var day = dtToday.getDate();
        var year = dtToday.getFullYear();

        if(month < 10)
            month = '0' + month.toString();
        if(day < 10)
            day = '0' + day.toString();

        var maxDate = year + '-' + month + '-' + day;    
        $('#pdc_deposit_date').attr('max', maxDate);
  }); 
   $("#checkAll").click(function () {
     $('input:checkbox').not(this).prop('checked', this.checked);
   });


   $('.pdcPost').on('click', function(e) {  

    var allVals = []; 
    $(".sub_chk:checked").each(function() {  
      allVals.push($(this).attr('value'));
            //alert(allVals);
          });
    if(allVals.length <=0)  
    {  
      alert("Please Select Atleast One PDC..!"); return false;  

    }
     /*
    else {  

     
      $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('pdcPost')}}", // This is the url we gave in the route
                data: {'id' : allVals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                   // $("#myModal").html(response); 
                   location.reload();
                 },
             });
      
    } 
            */
  });
 /*********************************************************************/
 $('.pdc_search').click(function(){
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
    }
  });
 });
</script>  
@endsection
