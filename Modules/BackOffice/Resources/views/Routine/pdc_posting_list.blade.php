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
	<input type="hidden" name="custom_direction" id="custom_direction" value="{{$custom_direction??'asc'}}">
  <input type="hidden" name="field_name" id="field_name" value="{{$field_name??'pdc_check_no'}}">
  <input type="hidden" name="page_count" id="page_count" value="2">
  <!-- 0- List is there , 1 - List End   -->
  <input type="hidden" name="list_end" id="list_end" value="0">
  <table class="table display product-overview mb-30" id="">
    <thead>
      <tr>
       <th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
        <th>Sl No.</th>
        <th>@sortablelink('pdc_check_no','Cheque No') </th>
        <th>Cheque Date</th>
        <th>Receipt Type</th>
        <th>@sortablelink('building_name','Building Name',[],[ 'class' => 'bankInfo' ])</a></th>
        <th>Building Code</th>
        <th>Unit No</th>
        <th>Tenant Name</th>
        <th>Tenant Code</th>
        <th>Agr No</th>
        <th>@sortablelink('bank_name','Bank',[],[ 'class' => 'bankInfo' ]) </th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody  id="fetch-data">
       @include('backoffice::Routine.pdc_posting_list_ajax')
    </tbody>
  </table>
	
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

    }else{
		event.preventDefault();
       //do something
       $(this).prop('disabled', true);
       $(this).css('display','none');
       $( "#tenant_contract_form" ).submit();
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
 /************* Ajax Fetch Data **************************/
  @if(count($pdc)>0)
  var timer;
  $(window).scroll(function() {
      if(timer) {
        window.clearTimeout(timer);
      }
     if($(window).scrollTop() + $(window).height() > $(document).height() - 100)  {
         
      var list_status = parseInt($('#list_end').val());

      if(list_status == 0){ // 1 - End
        
        var endlist     = 1;
        var fromdate    = $('#fromdate').val();
        var todate      = $('#todate').val();
        var pdc_check_no= $('#pdc_check_no').val();
        var page_count  = parseInt($('#page_count').val());
        var total_amt   = 0;
        var sortDir     = $('#custom_direction').val();
        var fieldName   = $('#field_name').val();
        if($('#total_amt').text()!='')
             total_amt   = parseFloat($('#total_amt').text().replace(/\,/g,''));
          
        var totalAmtDiv  = $('#fetch-data tr:last').clone();
        console.log(totalAmtDiv.find('.total_tr').length);
        //$('#fetch-data tr:last').remove();
        if($(".reloader").length == 0) {
          $('#fetch-data').append("<tr class='reloader'><td colspan='13' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");  
        }
        timer = window.setTimeout(function() {
        
        $.ajax({
            url:"{{url('/pdcPosting')}}",
            cache: true,
            data:{'ajax': true,'page':page_count,'fromdate':fromdate,'todate':todate,'pdc_check_no':pdc_check_no,'total_amt':total_amt,'sort':fieldName,'direction':sortDir },
            success:function(data){
              var temp = $(data);
              if(data.length >0){
                $('.reloader').remove();
                if(temp.find('#no_record').length == endlist){
                  //if($('#fetch-data').find('#total_amt').length==0)
                  //   $('#fetch-data').append(totalAmtDiv);
                  $('#fetch-data').find('.total_amt:not(:last)').remove();
                  $('#list_end').val(1);
                  endlist++;
                }
                else{
                  $('#fetch-data tr:last').remove();
                }
                $('#fetch-data').append(temp);   
                $('#page_count').val(page_count+1);
                
                
              }
              
          }
        });  
      },300);     
      }
      
    }

  });
  @endif
  /************* End Ajax Fetch Data **************************/
 });


</script>  
@endsection
