@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection
@section('search_url', route('finalDocumentationList'))
@section('search_reset', route('finalDocumentationList'))

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Final Documentation</div>
        </div>
        {{ Breadcrumbs::render('finalDocumentationList') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

           @include('sales::enquiry_search')     
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            <!-- <div class="card-head">
                <header>Open Enquiry List</header>
                
            </div> -->
            <div class="card-body ">
            <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $finalDocumentationLists])         
                 </div>
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive1">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                                <tr>
                                    <th title="Enquiry no">@sortablelink('sales_enquiry_no','Enq No',[], ['class' => 'sort_url'])</th>
                                    <th title="Enquiry date">@sortablelink('created_at','Enq Dt',[], ['class' => 'sort_url'])</th>
                                    <th title="Customer Name">@sortablelink('sales_enquiry_name','Customer',[], ['class' => 'sort_url'])</th>
                                    
                                    <th title="Building Name">@sortablelink('building_name','Building',[], ['class' => 'sort_url'])</th>
                                    <th title="Unit">@sortablelink('unit_code','Unit',[], ['class' => 'sort_url'])</th>
                                    <!--<th title="Unit Usage">Unit Usage</th> -->
                                    <!--<th title="Duration period">Duration (Months)</th> -->
                                    <th title="Start date">@sortablelink('tenant_contract_start_date','Start Dt',[], ['class' => 'sort_url'])</th>
                                    <th title="Rent">@sortablelink('agre_rent','Rent',[], ['class' => 'sort_url'])</th>
                                    <th title="Action buttons" width="17%" style="text-align:center">Action</th>
                                </tr>
                                <tr> 
								
								    <td> <input autocomplete="off" type="text" name="sales_enquiry_no" class="search_fields mob" id="sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>
                                    <td><input autocomplete="off" type="date" name="created_at" class="search_fields mob" id="created_at" value="{{old('created_at')}}" ></td>
                                    <td><input autocomplete="off" type="text" name="sales_enquiry_name" class="search_fields" id="sales_enquiry_name" value="{{old('sales_enquiry_name',$request->sales_enquiry_name)}}" ></td>
								    <td><input autocomplete="off" type="text" name="building_name_select" class="search_fields building_name_select" id="building_name_select"  value="{{old('tenantContracts__building__building_name')}}" > </td>
                                    <td><input autocomplete="off" type="text" name="unit_select" class="search_fields unit_select" id="unit_select" value="{{old('tenantContracts__unit__unit_code')}}" ></td>
                                    <!-- <td><input autocomplete="off" type="text" name="duration" class="search_fields duration" id="duration"  value="{{old('duration')}}" > </td>-->
                                    <td><input autocomplete="off" type="date" name="start_dt" class="search_fields start_dt" id="start_dt" value="{{old('tenantContracts__tenant_contract_start_date')}}" > </td>
									<td><input autocomplete="off" type="text" name="rent" class="search_fields rent" id="rent" value="{{old('tenantContracts__tenant_contract_rent')}}" > </td>
                                    <!--<td><input autocomplete="off" type="text" name="assigned_person" class="search_fields assigned_person" value="{{old('assigned_person')}}" > </td> -->
                                    <td></td>
								
								</tr>
                            </thead>
                           <tbody id="enquiry-search">
							   								   
							    @include('sales::TenantSales.tenant_final_document_list_ajax')	
							    							
                            </tbody>
                        </table>
                        
                        
                        
                       
                        
                        
                        
                    </div>
                </div> 

                  <div class="row "  id="pagination">
                         {{$finalDocumentationLists->appends(\Request::except(['page','_token']))->links()}}
                        </div>
            <!-- </form>  -->
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal">
    
</div>
@endsection
@section('scripts')
@include('sales::enquiry_search_js')
@include('sales::sales_search_js')
<script>
$(document).ready(function() {
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
    $(document).on('click', '.closed',function(e) {        

        var action_key = $(this).attr('data-id');
        var workflow_id = $(this).attr('datas-id');
        var enquiryid = $(this).attr('datas-enid');
        /*if (confirm('Do you want to Close this Enquiry?')) {*/
            $.ajax({
                method: 'POST', // Type of response and matches what we said in the route
                url: "{{route('approvalAccepts')}}", // This is the url we gave in the route
                data: {'action_key' : action_key,'enquiryid' : enquiryid,'workflow_id' : workflow_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*}else {
            return false;
        }*/
        
         
    });
    $(document).on('click','.assignLead', function(e) {        

        /*if (confirm("Are you sure you want to Re-assign ?")) {*/
            var vals = $(this).attr('data-id');
            var workflow = $(this).attr('datas-id');
            $('input:hidden[name=enquiryIds]').val(vals);
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../leadAssign/re-assign/'+vals+'/'+workflow+'/list', // This is the url we gave in the route
                //data: {'id' : vals,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
                success: function(response){ // What to do if we succeed
                    $("#myModal").html(response); 
                },
            });
            return true;
        /*} else {
            return false;
        } */ 
         
    });
    
    
});
</script>
@endsection
