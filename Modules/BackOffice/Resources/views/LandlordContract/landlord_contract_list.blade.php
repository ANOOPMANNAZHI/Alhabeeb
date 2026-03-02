@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('search_url', route('landlord-contract.index'))
@section('search_reset', route('landlord-contract.index'))


@section('content')


  <div class="page-bar">
      <div class="page-title-breadcrumb">
          <div class=" pull-left">
              <div class="page-title">Landlord Contract </div>
          </div>
          {{ Breadcrumbs::render('landlord-contract.index') }}
      </div>
  </div>
          <!-- start widget -->
        
          <!-- end widget -->
	<a  class="align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
			<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
	</a>
	<div class="clearfix"></div>
	
	<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
		<div class="col-md-12 col-sm-12 dashboardtab"> 
			<div class="card card-box salesSearchBox">
				@include('sales::enquiry_search')     
  
			</div>
		</div>
	</div>


<div class="row">
    <div class="col-md-12 col-sm-12">


<div class="card card-box">

            <!-- <div class="card-head"> 
               <div class="clr"></div>
                  <header>Tenant Contract List</header>   
                <div class="clr"></div>
            </div> -->

    <div class="card-body ">
       <h4>
        <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $landlordContracts])         
     	</div>
     	@can('add_landlord_contract_direct')
     	 
           <a href="{{route('landlord-contract.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>
        @endcan
           <div class="clr"></div>
        </h4>

      <div class="table-wrap">
			<div class="table-responsive">
				<table class="table display product-overview mb-30" id="dtBasicExample">
				  <thead>
					  <tr>
						  
						  <th>@sortablelink('landlord_contract_no','Agrmt No',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('created_at','Agrmt Dt',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('vendorName.vendor_name','Landlord',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('managementTypeInfo.management_types_name','Mgmt Type',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('buildinginfo.building_name','Building',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('landlord_contract_duration','Duration',[],[ 'class' => 'sort_url'])</th>
						  <th>@sortablelink('landlord_contract_valid_to_date','To',[],[ 'class' => 'sort_url'])</th> 
						  <th width="15%">@sortablelink('landlord_contract_status','Status',[],[ 'class' => 'sort_url'])</th>           
						  <th width="10%">Action</th>
					  </tr>
					  <tr>
						<td><input autocomplete="off" id="landlord_contract_no" type="text" name="landlord_contract_no" class="contract_search_field landlord_contract_no" value="{{old('landlord_contract_no')}}" ></td>
						<td><input autocomplete="off" type="date" name="created_at" class="contract_search_field created_at" id="created_at" value="{{old('created_at')}}" ></td>
						<td><input autocomplete="off" type="text" name="vendorName__vendor_name" class="contract_search_field vendorName__vendor_name" id="vendorName__vendor_name" value="{{old('vendorName__vendor_name')}}" ></td>
						<td><input autocomplete="off" id="managementTypeInfo__management_types_name" type="text" name="managementTypeInfo__management_types_name" class="contract_search_field managementTypeInfo__management_types_name" value="{{old('managementTypeInfo__management_types_name')}}" > </td>
						<td><input autocomplete="off" type="text" id="buildingInfo__building_name" name="buildingInfo__building_name" class="contract_search_field buildingInfo__building_name" value="{{old('buildingInfo__building_name')}}" > </td>
						<td>
							<!--<input autocomplete="off" type="text" name="contract_status" class="contract_search_field contract_status" value="{{old('contract_status')}}" >  -->
							<select name="landlord_contract_duration" class="contract_search_field landlord_contract_duration" id="landlord_contract_duration">
								<option value="">Select</option>
								<option value="1" {{(isset($request->landlord_contract_duration)? (old('landlord_contract_duration')? 'SELECTED':''):'') }} >Open</option>
								<option value="2" {{(isset($request->landlord_contract_duration)? (old('landlord_contract_duration')? 'SELECTED':''):'') }} >Close</option>
							</select>
						</td>
						<td><input autocomplete="off" id="landlord_contract_valid_to_date" type="date" name="landlord_contract_valid_to_date" class="contract_search_field landlord_contract_valid_to_date"  value="{{old('landlord_contract_valid_to_date')}}" > </td>
						<td>
							<select name="landlord_contract_status" class="contract_search_field landlord_contract_status" id="landlord_contract_status">
								<option value="">Select</option>
								
								<option value="3" {{(isset($request->landlord_contract_status)? (old('landlord_contract_status')? 'SELECTED':''):'') }} >Pending-Direct</option>
								<option value="2" {{(isset($request->landlord_contract_status)? (old('landlord_contract_status')? 'SELECTED':''):'') }} >Unapproved-Direct</option>
								<option value="1" {{(isset($request->landlord_contract_status)? (old('landlord_contract_status')? 'SELECTED':''):'') }} >Approved</option>
								<option value="0" {{(isset($request->landlord_contract_status)? (old('landlord_contract_status')? 'SELECTED':''):'') }} >Pending</option>
								
							</select>
						</td>
						<td></td>
					</tr>
				  </thead>
				  <tbody id="contract-search">
					  @include('backoffice::LandlordContract.landlord_contract_list_ajax') 						
				  </tbody>
				 
				</table>
			</div>
		</div>
		   <div id="pagination">
				<div class="text-center">                     
				   {{$landlordContracts->appends(\Request::except(['page','ajax','_token']))->links()}}
			   </div>
		   </div>
                 </div>
               </div>

             </div>
           </div>

               
            </div>
        </div>
        <div id="expiredContracts" expiry="{{$expiredContracts}}"> </div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
@include('backoffice::LandlordContract.contract_search_js')

<script>
$(document).ready(function() {
		
		$("#show").on("hide.bs.collapse", function(){
					$("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
			});
			$("#show").on("show.bs.collapse", function(){
				$("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
			});
		var showResultsTimer = 0;
		$(".created_at, .landlord_contract_duration, .landlord_contract_valid_to_date, .landlord_contract_status").change(function(){
				
				$('.contract_search_field').trigger('keyup');
		});
		/*
		$('.contract_search_field').on('keyup paste',function(){ 
			
			 searchnow = this.value;
			 var landlord_contract_no 			= $(".landlord_contract_no").val();
			 var created_at 					= $(".created_at").val();
			 var vendor_name					= $(".vendor_name").val();
			 var management_types_name			= $(".management_types_name").val();
			 var building_name			 		= $(".building_name").val();
			 var landlord_contract_duration		= $(".landlord_contract_duration").val();
			 var landlord_contract_valid_to_date= $(".landlord_contract_valid_to_date").val();
			 var landlord_contract_status		= $(".landlord_contract_status").val();
			 			 
			 var route_href 					= $("#leade_search").attr('action'); 
			
			 window.clearTimeout(showResultsTimer);
			 showResultsTimer = window.setTimeout(function(){	
				 $.ajax({
						  method: "GET",
						  url:route_href,
						  data: {'landlord_contract_no': landlord_contract_no,'created_at':created_at,'vendor_name':vendor_name,'management_types_name':management_types_name,
								 'building_name': building_name, 'landlord_contract_duration':landlord_contract_duration,'landlord_contract_valid_to_date':landlord_contract_valid_to_date, 'landlord_contract_status':landlord_contract_status ,'ajax' : true},			  
						  beforeSend: function(){
							// Show image container
							$("#pagination" ).hide();
							$('#contract-search').html("<tr><td colspan='9' align='center'><img src='{{url('/')}}/public/img/pre-loader.gif' width='75' height='75'></td></tr>");
							
						   },
						  success: function(data){ 
			
							if(data != 0){
								if(data.length >0){
									
									$("#pagination" ).show();
									var temp = $(data);
									var paginate = temp.find('#pagination_ajax').clone();
									temp.find('#pagination_ajax').remove();
									$('#contract-search').html(temp);
									$("#pagination" ).html(paginate);		
									// $('.contract_search_field').trigger('blur');	
								}
							}  
												 
						  }   
						  });        
					},1000);
      
			});  */
			
 });
</script>
@endsection

