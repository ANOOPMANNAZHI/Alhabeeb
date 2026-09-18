@extends('layouts.plms-app')

@section('css')
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css') }}">
<style>
  .tdue-list td.num, .tdue-list th.num { text-align:right; white-space:nowrap; }
  .tdue-list .tdue-sub { display:block; font-size:12px; color:#6b7280; }
  .tdue-list .tdue-overdue { color:#dc2626; }
</style>
@endsection

@section('search_url', route('termination-dues.index'))
@section('search_reset', route('termination-dues.index'))

@section('content')

  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Termination Dues</div>
          </div>
          {{ Breadcrumbs::render('termination-dues.index') }}
      </div>
  </div>

	<a class="align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
			<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
	</a>
	<div class="clearfix"></div>

	<div class="row collapse @if(old('fieldName')) show @endif" id="show">
		<div class="col-md-12 col-sm-12 dashboardtab">
			<div class="card card-box salesSearchBox">
				@include('sales::enquiry_search')
			</div>
		</div>
	</div>

<div class="row">
    <div class="col-md-12 col-sm-12">

<div class="card card-box tdue-list">
    <div class="card-body ">
       <h4>
        <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $dues])
     	</div>
           <div class="clr"></div>
        </h4>

      <div class="table-wrap">
			<div class="table-responsive">
				<table class="table display product-overview mb-30" id="dtBasicExample">
				  <thead>
					  <tr>
						  <th>Contract No</th>
						  <th>Tenant</th>
						  <th>Building</th>
						  <th>Unit</th>
						  <th>@sortablelink('termination_date','Terminated',[],[ 'class' => 'sort_url'])</th>
						  <th class="num">@sortablelink('total_owed','Owed',[],[ 'class' => 'sort_url'])</th>
						  <th class="num">@sortablelink('total_settled','Settled',[],[ 'class' => 'sort_url'])</th>
						  <th class="num">@sortablelink('balance','Balance',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('status','Status',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('last_followup_at','Follow-up',[],[ 'class' => 'sort_url'])</th>
						  <th width="8%">Action</th>
					  </tr>
					  <tr>
						<td><input autocomplete="off" id="tenantContract__tenant_contract_no" type="text" name="tenantContract__tenant_contract_no" class="contract_search_field" value="{{ old('tenantContract__tenant_contract_no') }}"></td>
						<td><input autocomplete="off" id="tenant__tenant_name" type="text" name="tenant__tenant_name" class="contract_search_field" value="{{ old('tenant__tenant_name') }}"></td>
						<td><input autocomplete="off" id="building__building_name" type="text" name="building__building_name" class="contract_search_field" value="{{ old('building__building_name') }}"></td>
						<td><input autocomplete="off" id="unit__unit_no" type="text" name="unit__unit_no" class="contract_search_field" value="{{ old('unit__unit_no') }}"></td>
						<td><input autocomplete="off" id="termination_date" type="date" name="termination_date" class="contract_search_field" value="{{ old('termination_date') }}"></td>
						<td></td>
						<td></td>
						<td></td>
						<td>
							<select name="status" class="contract_search_field" id="status">
								@foreach($statuses as $k => $label)
								<option value="{{ $k }}" {{ old('status', 'outstanding') === $k || ($k === 'all' && old('status') === '') ? 'selected' : '' }}>{{ $label }}</option>
								@endforeach
							</select>
						</td>
						<td></td>
						<td></td>
					</tr>
				  </thead>
				  <tbody id="contract-search">
					  @include('backoffice::TerminationDues.index_ajax')
				  </tbody>
				</table>
			</div>
		</div>
		   <div id="pagination">
				<div class="text-center">
				   {{ $dues->appends(\Request::except(['page','ajax','_token']))->links() }}
			   </div>
		   </div>
    </div>
</div>

    </div>
</div>
@endsection

@section('scripts')
@include('sales::enquiry_search_js')
<script>
$(document).ready(function() {
	$("#show").on("hide.bs.collapse", function(){
		$("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
	});
	$("#show").on("show.bs.collapse", function(){
		$("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
	});

	var timer = 0;
	$(document).on('change keyup paste', '.search_fields,.contract_search_field', function(){
		window.clearTimeout(timer);
		timer = window.setTimeout(refreshList, 400);
	});

	function refreshList(){
		var fieldName = [], operation = [], fieldValue = [], logic = [];
		$(".fieldName").each(function(){ if(this.value != '') fieldName.push(this.value); });
		$(".operation").each(function(){ operation.push(this.value); });
		$(".fieldValue").each(function(){ fieldValue.push(this.value); });
		$(".logic").each(function(){ logic.push(this.value); });

		$.ajax({
			method: "GET",
			url: '{{ $quick_url }}',
			data: {
				'tenantContract__tenant_contract_no': $("#tenantContract__tenant_contract_no").val(),
				'tenant__tenant_name': $("#tenant__tenant_name").val(),
				'building__building_name': $("#building__building_name").val(),
				'unit__unit_no': $("#unit__unit_no").val(),
				'termination_date': $("#termination_date").val(),
				'status': $("#status").val(),
				'route': '{{ $quick_url }}',
				"_token": $('meta[name="csrf-token"]').attr('content'),
				'ajax': true, 'fieldName': fieldName, 'operation': operation, 'fieldValue': fieldValue, 'logic': logic
			},
			beforeSend: function(){
				$('#contract-search').html("<tr><td colspan='11' align='center'><img src='{{ url('/') }}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
			},
			success: function(data){
				if(data != 0){
					var temp = $(data);
					var paginate_info = temp.find('.pagination_info').clone();
					temp.find('.pagination_info').remove();
					var paginate = temp.find('#pagination_ajax').clone();
					temp.find('#pagination_ajax').remove();
					$('#contract-search').html(temp);
					$("#pagination").html(paginate);
					$("#pagination_info").html(paginate_info);
				}
			}
		});
	}
});
</script>
@endsection
