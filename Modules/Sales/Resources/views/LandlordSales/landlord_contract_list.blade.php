@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('search_url', route('landlordContract.index'))
@section('search_reset', route('landlordContract.index'))



@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Final Documentation</div>
        </div>
        {{ Breadcrumbs::render('landlordContract') }}
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
                     @include('includes.pagination_info',['paginator' => $contract])         
                 </div>
              
            <!--<button type="button" class="btn btn-circle btn-primary groupAssign align-right"  data-toggle="modal" data-target="#myModal">Group Assign</button> -->
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                                <tr>
                                  <!--  <th><input type = "checkbox" id = "master" 
                                     class = "mdl-switch__input "> All</th> 
                                     <th>Sl No</th>-->
                                    <th>@sortablelink('salesEnquiry.sales_enquiry_no','Enquiry No',[], ['class' => 'landlord_sort'])</th>
                                    <th>@sortablelink('salesEnquiry.sales_enquiry_name','Enquiry Name',[], ['class' => 'landlord_sort'])</th>
                                    <th>@sortablelink('salesEnquiry.sales_building_name','Building Name',[], ['class' => 'landlord_sort'])</th>
                                    <th>@sortablelink('salesEnquiry.sales_email','Email',[], ['class' => 'landlord_sort'])</th>
                                    <th>@sortablelink('salesEnquiry.sales_mobile_no','Phone',[], ['class' => 'landlord_sort'])</th>
                                    <th>Action</th>
                                </tr>
                                <tr> 
                               
								  <td><input autocomplete="off" type="text" name="sales_enquiry_no" id="sales_enquiry_no" class="search_fields sales_enquiry_n" value="{{old('sales_enquiry_n')}}" ></td>
                                   <td><input autocomplete="off" type="text" name="customer-name" id="sales_enquiry_name" class="search_fields customer-name" value="{{old('customer_name')}}" ></td>
								  <td><input autocomplete="off" type="text" id="building_name" name="sales_building_name" class="search_fields sales_building_name" value="{{old('sales_building_name')}}" ></td>
								  <td><input autocomplete="off" type="text" name="email" class="search_fields email"  value="{{old('email')}}" ></td>
								  <td><input autocomplete="off" type="text" name="phone" class="search_fields phone" id="sales_mobile_no"  value="{{old('phone')}}" ></td>
								  <td></td>
								</tr>
                            </thead>
                            <tbody id="enquiry-search">								
                                @include('sales::LandlordSales.landlord_contract_list_ajax') 
                            </tbody>
                        </table>
                        
                        <div id="pagination">
							 {{$contract->appends(\Request::except(['page','_token']))->links()}}
                        </div>
                        
                       
                        
                    </div>
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
@include('sales::LandlordSales.landlord_contract_search_js')
<script>
$(document).ready(function() {

    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });

    $("#leade_search").validate();
   
    $(document).on('click','.closed', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccept')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
       /* }*/
        
         
    });

    // Create contract pop-up

    $(document).on('click','.create_contract', function(e) {        

        var action_key = $(this).attr('data-id');
        var enquiryid = $("#enquiryid").val();
        var sales_id = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
     
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('draftContract')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
     
        
         
    });
});
</script>
@endsection
