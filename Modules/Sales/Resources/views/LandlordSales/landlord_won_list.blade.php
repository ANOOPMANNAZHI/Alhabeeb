@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('contractApprovalWon'))
@section('search_reset', route('contractApprovalWon'))

@section('content')
<div class="page-bar">
	<div class="page-title-breadcrumb">
		<div class=" pull-left">
			<div class="page-title">Landlord Won Enquiry</div>
		</div>
		{{ Breadcrumbs::render('landlordContractWon') }}
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
										<th>@sortablelink('salesEnquiry.sales_enquiry_name','Enquiry No',[], ['class' => 'landlord_sort'])</th>

										<th>@sortablelink('salesEnquiry.sales_enquiry_no','Customer Name',[], ['class' => 'landlord_sort'])</th>
										<th>Building Name</th>
										<th>@sortablelink('salesEnquiry.sales_email','Email',[], ['class' => 'landlord_sort'])</th>
										<th>@sortablelink('salesEnquiry.sales_mobile_no','Phone',[], ['class' => 'landlord_sort'])</th>
										<th>Action</th>
									</tr>
									<tr> 
										<td></td>
										<td><input autocomplete="off" type="text" name="sales_enquiry_no" id="sales_enquiry_no" class="search_fields sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
										<td><input autocomplete="off" type="text" name="customer-name" id="sales_enquiry_name" class="search_fields customer-name" value="{{old('sales_enquiry_name')}}" ></td>
										<td><input autocomplete="off" type="text" name="sales_building_name" id="building_name_select" class="search_fields building_name_select" value="{{old('landlordContract__buildingInfo__building_name')}}" ></td>
										<td><input autocomplete="off" type="text" name="email" class="search_fields email"  value="{{old('sales_email')}}" ></td>
										<td><input autocomplete="off" type="text" name="phone" id="sales_mobile_no" class="search_fields phone"  value="{{old('sales_mobile_no')}}" ></td>
										<td></td>

									</tr>
								</thead>
								<tbody id="enquiry-search">	

									@include('sales::LandlordSales.landlord_won_list_ajax') 

								</tbody>
							</table>
							<div id="pagination">
											
						    {{$approveList->appends(\Request::except(['page','_token']))->links()}}
					</div>

				</div>
			</div> 
			<!-- </form>  -->
		</div>
	</div>
</div>
</div>
<!-- The Modal -->

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
	});
</script>
@endsection
