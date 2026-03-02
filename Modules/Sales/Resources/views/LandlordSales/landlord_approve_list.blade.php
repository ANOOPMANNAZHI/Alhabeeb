@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', $quick_url)
@section('search_reset', $quick_url)

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">@if(isset($title)){{$title}} @else Landlord Documentation / Contract Approval @endif</div>
        </div>
        @if(isset($title))
        {{Breadcrumbs::render('contractApprovalPendingList')}}
        @else
        {{Breadcrumbs::render('contractApprovalList')}}
        @endif
        @php
                $current = 'contractApprovalList';
                Session::put('current', $current);  
        @endphp
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
                     @include('includes.pagination_info',['paginator' => $landlordContracts])         
                 </div>
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive1">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                        <tr>
                          <th>@sortablelink('salesEnquiry.sales_enquiry_no','Enquiry No',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('landlord_contract_no','Agrmt No',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('created_at','Agrmt Dt',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('vendorName.vendor_name','Landlord',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('managementTypeInfo.management_types_name','Mgmt Type',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('buildinginfo.building_name','Building',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('landlord_contract_duration','Duration',[],[ 'class' => 'sort_url'])</th>
                          <th>@sortablelink('landlord_contract_valid_to_date','To',[],[ 'class' => 'sort_url'])</th> 
                                   
                          <th width="10%">Action</th>
                        </tr>
                        <tr>
                        <td><input autocomplete="off" id="sales_enquiry_no" type="text" name="sales_enquiry_no" id="sales_enquiry_no" class="contract_search_field sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>

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
                                <option value="2" {{(isset($request->landlord_contract_duration)? (old('landlord_contract_duration')? 'SELECTED':''):'') }} >Perpetual</option>
                            </select>
                        </td>
                        <td><input autocomplete="off" id="landlord_contract_valid_to_date" type="date" name="landlord_contract_valid_to_date" class="contract_search_field landlord_contract_valid_to_date"  value="{{old('landlord_contract_valid_to_date')}}" > </td>
                       
                        <td></td>
                    </tr>
                            </thead>
                            <tbody id="contract-search">	
															
                                @include('sales::LandlordSales.landlord_approve_list_ajax') 
                                
                            </tbody>
                        </table>
                        
                        
                    </div>
                </div> 
                <div class="row "  id="pagination">                                     
                             {{$landlordContracts->appends(\Request::except(['page','_token']))->links()}}
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
@include('backoffice::LandlordContract.contract_search_js')
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

     $(document).on('click', '.reject, .accept',function(e) {        
        
        
        var action_key  = $(this).attr('data-id');
        var enquiryid   = $(this).attr('id');
        var sales_id    = $("#sales_id").val();
        var workflow_id = $("#workflow_id").val();
        if(action_key === 'REJT'){
            if (confirm('Do you want to reject this Approval?')) {
                $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('approvalAccept')}}", // This is the url we gave in the route
                    data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    success: function(response){ // What to do if we succeed
                        $("#myModal").html(response); 
                    },
                });
                return true;
            }
        }
        else{

            $.ajax({
                    method: 'POST', // Type of response and matches what we said in the route
                    url: "{{route('approvalAccept')}}", // This is the url we gave in the route
                    data: {'action_key' : action_key,'enquiryid' : enquiryid,'sales_id' : sales_id,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                    success: function(response){ // What to do if we succeed
                        $("#myModal").html(response); 
                    },
                });
                return true;
        }
    });
});
</script>
@endsection
