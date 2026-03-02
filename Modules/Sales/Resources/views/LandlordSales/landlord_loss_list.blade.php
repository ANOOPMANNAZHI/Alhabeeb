@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection



@section('search_url', route('contractApprovalLoss'))
@section('search_reset', route('contractApprovalLoss'))



@section('content')
<div class="page-bar">
	<div class="page-title-breadcrumb">
		<div class=" pull-left">
			<div class="page-title">Landlord Loss Enquiry</div>
		</div>
		{{ Breadcrumbs::render('landlordContractLoss') }}
	</div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
	<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show">
	<div class="col-md-12 col-sm-12 dashboardtab">
		<div class="panel tab-border card-box">
			@include('sales::enquiry_search')  

		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12 col-sm-12">
		<div class="card  card-box">

			<div class="card-body ">
				<h4>
				 <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $approveList])         
                 </div>

					<div class="clr"></div></h4>
					<div class="table-wrap">
						<div class="table-responsive">
							<table class="table display product-overview mb-30" id="dtBasicExample">
								<thead>
									<tr>
										<th>Sl No</th>
										<th>@sortablelink('salesEnquiry.sales_enquiry_no','Enquiry No',[], ['class' => 'landlord_sort'])</th>
										<th>@sortablelink('salesEnquiry.sales_enquiry_name','Enquiry Name',[], ['class' => 'landlord_sort'])</th>
										<th><a class="sort_url" href="">Building Name <i class="fa fa-sort"></i></a></th>
										<th>@sortablelink('salesEnquiry.sales_email','Email',[], ['class' => 'landlord_sort'])</th>
										<th>@sortablelink('salesEnquiry.sales_mobile_no','Phone',[], ['class' => 'landlord_sort'])</th>
										<th>Action</th>
									</tr>
									<tr> 
										<td></td>
										<td><input autocomplete="off" type="text" name="sales_enquiry_no" id="sales_enquiry_no" class="search_fields sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
										<td><input autocomplete="off" type="text" id="sales_enquiry_name" name="customer-name" class="search_fields customer-name" value="{{old('sales_enquiry_name')}}" ></td>
										<td><input autocomplete="off" type="text" name="sales_building_name" id="building_name" class="search_fields building_name_select" value="{{old('landlordContract__buildingInfo__building_name')}}" ></td>
										<td><input autocomplete="off" type="text" name="email" class="search_fields email"  value="{{old('sales_email')}}" ></td>
										<td><input autocomplete="off" type="text" id="sales_mobile_no" name="phone" class="search_fields phone"  value="{{old('sales_mobile_no')}}" ></td>
										<td></td>

									</tr>
								</thead>
								<tbody id="enquiry-search">	

									@include('sales::LandlordSales.landlord_loss_list_ajax') 

								</tbody>
							</table>
							


				</div>
			</div> 
			<div id="pagination">
						    {{$approveList->appends(\Request::except(['page','_token']))->links()}}
					        </div>
			<!-- </form>  -->
		</div>
	</div>
</div>
</div>
<!-- The Modal -->
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
@include('sales::LandlordSales.search_js')
<script>
	$(document).ready(function() {

		$("#show").on("hide.bs.collapse", function(){
			$("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
		});
		$("#show").on("show.bs.collapse", function(){
			$("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
		});

		$("#leade_search").validate();

		$(document).on('click', '.reopen',function(e) {        


			var vals = $(this).attr('data-id');
			var workflow = $(this).attr('datas-id');
			$('input:hidden[name=enquiryIds]').val(vals);
			$.ajax({
			method: 'GET', // Type of response and matches what we said in the route{{url('/vendorNameAjax')}}
			url: "{{url('/landLordLeadAssign/re-assign')}}/"+vals+'/'+workflow+'', // This is the url we gave in the route
			//data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
			success: function(response){ // What to do if we succeed
				$("#myModal").html(response); 
			},
		});
			return true;


		});
	});
</script>
@endsection
