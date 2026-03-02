@extends('layouts.plms-app')
@section('css')
<link rel="stylesheet" href="{{ asset('public/css/jquery-ui.css')}} ">
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('public/css/formlayout.css')}}" rel="stylesheet" type="text/css" />
@endsection 
@section('content')
<!-- start widget -->
<div class="page-bar">
  <div class="page-title-breadcrumb">
    <div class=" pull-left">
      <div class="page-title">Generate Maintenance Invoice</div>
    </div>

    {{Breadcrumbs::render('groupInvoiceGeneration')}}

  </div>
</div>
<div class="row">
  <div class="col-md-12 col-sm-12 dashboardtab">
    <div class="panel tab-border card-box">
      <div class="panel-body">	       
        <form autocomplete="off" action="{{route('groupInvoiceGeneration')}}" method="GET" id="search_form" class="form-horizontal" data-toggle="validator">
            <div class="dataSearchBox ">
                <div class="row">
                     <div class="col-sm-5">
                        <div class="form-group">
                            <label for="fromdate">Start Date</label>
                            <div class="p-relative">
                             <i class="fa fa-align-left icn-add" aria-hidden="true"></i>

                            <input type="date" class="form-control" name="fromdate" id="fromdate" placeholder="Enter Value" value="{{isset($request->fromdate)?$request->fromdate:old('fromdate')}}">
                        </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="todate">End Date </label>
                            <div class="p-relative">
                             <i class="fa fa-align-left icn-add" aria-hidden="true"></i>
                            <input type="date" class="form-control" name="todate" id="todate" placeholder="Enter Value" value="{{isset($request->todate)?$request->todate:old('todate')}}">
                        </div>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary margin pdc_search top-align">Search</button>
                        </div>
                    </div>  

                    </div>

                </div>
        </form>
                            
	</div>
   
    </div>
  </div>
</div>
@if(isset($serviceList))
<div class="row">
 <div class="col-md-12 col-sm-12">
  <div class="card  card-box">
  	<form autocomplete="off" action="{{route('maintenanceInvoiceGenerate')}}" method="POST" id="tenant_contract_form"" class="form-horizontal" enctype="multipart/form-data" data-toggle="validator">
			{{csrf_field()}}
  	@if(count($serviceList)>0)
  	<div class="card-head">
  	   <span>
	     <button type="submit" class="btn btn-circle btn-primary pdcPost align-right">Generate</button>
	   </span>
  	</div>
  	@endif
	<div class="card-body ">
		<div style="overflow-x:auto;">
			
			<table class="table display product-overview mb-30" id="">
			<thead>
			  <tr>
				<th><input type ="checkbox" id ="checkAll" class ="mdl-switch__input"></th>
				<th>Sl No.</th>
				<th>Vendor</th>
				<th>Building</th>
				<th>Unit No</th>
				<th>Service Report</th>
				<th>Material Chrge</th>
				<th>Labour Chrge</th>
				<th>Amount</th>
				<th>Tech</th>
				
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
			
			@forelse ($serviceList as $key=>$list) 
			<tr>
				<td><input type ="checkbox" id ="checkItem" name="vendor_id[]" class ="mdl-switch__input sub_chk" value="{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}" value="{{$list->building_id}}">
				{{-- // Specify In-house (technicians) and subcontractors --}}	
				<input type="hidden" name="find_comparison_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->building_id.$list->unit_id}}" >
				<input type="hidden" name="service_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->service_id}}" >
				<input type="hidden" name="building_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->building_id}}" >
				<input type="hidden" name="unit_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->unit_id}}" >
				<input type="hidden" name="mat_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->mat}}" >
				<input type="hidden" name="closeDt_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{isset($list->closeddt)?$list->closeddt:''}}" >
				<input type="hidden" name="lab_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->lab}}" >
				<input type="hidden" name="tot_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->tot}}" >
				
				<input type="hidden" name="code_desc_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->acc_code_desc}}" >

				<input type="hidden" name="code_id_{{empty($list->vendor_name)?'inhouse_'.$list->service_id.$list->building_id.$list->unit_id:$list->service_id.'||'.$list->vendor_id}}[]" value="{{$list->accountcodeid}}" >

				</td>
				<td>{{ $curr_loop + $loop->index }}</td>
				<td>
					@if(isset($list->employee_name))
					{{'General'}} 
					@else
					{{$list->vendor_name}}
					@endif
				</td>
				<td>
					{{$list->building_name}}
				</td>
				
				<td>{{$list->unit_code}}</td> 
				<td>{{$list->service_report_no}}</td>
				<td>{{$list->mat}}</td>
				<td>{{$list->lab}}</td> 
				<td>{{$list->tot}}</td>
				<td>@if(isset($list->employee_name))
					{{$list->employee_name}} 

					@endif		
				</td>
			</tr>  
			
			  @empty 
			  <tr>
				<td colspan="13" align="center">
				  <p>No Record</p>
				</td>
			  </tr>
			  @endforelse 
			  @if(!empty($totalamt))
			  <tr>
				<td colspan="10" align="center"></td>
				<td colspan="4" align="center">Total <b>{{ $totalamt }}</b></td>
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
@endif  
@endsection
@section('scripts')
<script>
  	$(document).ready(function() {
	  	$("#search_form").validate({
	        submitHandler: function(form) {
	          $('.pdc_search').prop('disabled', true);
	          form.submit();
	        }
	    })

	  	$("#checkAll").click(function () {
	     	$('input:checkbox').not(this).prop('checked', this.checked);
	   	});
	   	$(".sub_chk").click(function () {
	   		if($(".sub_chk:checked").length == ''){ 
	   			$("#checkAll").prop('checked', false)
	   		}
	   	});
 	});
 	jQuery.validator.addMethod("greaterThan", 
	  function(value, element, params) {

		  if (!/Invalid|NaN/.test(new Date(value))) {
			  return new Date(value) > new Date($(params).val());
		  }

		  return isNaN(value) && isNaN($(params).val()) 
			  || (Number(value) > Number($(params).val())); 
	},'Must be greater than Start Date.');
	  
	jQuery.validator.addMethod("lessThan", 
		function(value, element, params) {

		  if (!/Invalid|NaN/.test(new Date(value))) {
			  return new Date(value) < new Date($(params).val());
		  }

		  return isNaN(value) && isNaN($(params).val()) 
			  || (Number(value) < Number($(params).val())); 
	},'Must be less than End Date');

	$("#search_form").validate({
	  rules: {
			  todate: { greaterThan: "#fromdate" ,
			 
			 },
			 fromdate: { 
				lessThan: "#todate" ,
				  
			  }
		  },
		 submitHandler: function(form) {
			  $('.pdc_search').prop('disabled', true);
			  form.submit();
		},  
		  
	});
	$('#fromdate, #todate').on('change', function() {
		$('#fromdate').valid(); // <- force re-validation
		$('#todate').valid();
	});
</script>
@endsection

