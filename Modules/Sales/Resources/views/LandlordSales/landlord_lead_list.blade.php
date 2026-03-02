@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('search_url', route('landlordLead.index'))
@section('search_reset', route('landlordLead.index'))

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Enquiry</div>
        </div>
        {{ Breadcrumbs::render('landlordLeadAssign') }}
        @php
        $current = 'landlordLead.index';
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
                     @include('includes.pagination_info',['paginator' => $lead])         
                 </div>
                  
                    <!--<button type="button" class="btn btn-circle btn-primary groupAssign align-right"  data-toggle="modal" data-target="#myModal">Group Assign</button> -->
                    <div class="clr"></div></h4>
                    <div class="table-wrap">
                        <div class="table-responsive1">
                            <table class="table display product-overview mb-30" id="dtBasicExample">
                                <thead>
                                    <tr>
                                  <!--  <th><input type = "checkbox" id = "master" 
                                  class = "mdl-switch__input "> All</th> -->
                                  <th>Sl No</th>
                                  <th>@sortablelink('salesEnquiry.sales_enquiry_no','Enquiry No',[], ['class' => 'landlord_sort'])</th>
                                  <th>@sortablelink('salesEnquiry.sales_enquiry_name','Customer Name',[], ['class' => 'landlord_sort'])</th>
                                  <th>@sortablelink('salesEnquiry.sales_building_name','Building Name',[], ['class' => 'landlord_sort'])</th>
                                  <th>@sortablelink('salesEnquiry.sales_email','Email',[], ['class' => 'landlord_sort'])</th>
                                  <th>@sortablelink('salesEnquiry.sales_mobile_no','Phone',[], ['class' => 'landlord_sort'])</th>
                                  <th>Action</th>
                              </tr>
                              <tr> 
                                  <td></td>
                                  <td><input autocomplete="off" type="text" name="sales_enquiry_no" id="sales_enquiry_no" class="search_fields sales_enquiry_no" value="{{old('sales_enquiry_no')}}" ></td>

                                  <td><input autocomplete="off" type="text" name="customer-name" id="sales_enquiry_name" class="search_fields customer-name" value="{{old('sales_enquiry_name')}}" ></td>
                                  <td><input autocomplete="off" type="text" name="sales_building_name" id="building_name" class="search_fields sales_building_name" value="{{old('sales_building_name')}}" ></td>
                                  <td><input autocomplete="off" type="text" name="email" class="search_fields email"  value="{{old('sales_email')}}" ></td>
                                  <td><input autocomplete="off" type="text" name="phone" class="search_fields phone" id="sales_mobile_no" value="{{old('sales_mobile_no')}}" ></td>
                                  <td></td>

                              </tr>
                          </thead>
                          <tbody id="enquiry-search">                               
                              @include('sales::LandlordSales.landlord_lead_list_ajax') 
                          </tbody>
                      </table>
                      <div  id="pagination">
                      			
                   {{$lead->appends(\Request::except(['page','_token']))->links()}}
                </div
                
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
@include('sales::LandlordSales.search_js')
<script>
    $(document).ready(function() {
       
       $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
       $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
       
       $('#master').on('click', function(e) {
        if($(this).is(':checked',true))  
        {
            $(".sub_chk").prop('checked', true);  
        } else {  
            $(".sub_chk").prop('checked',false);  
        }  
    });
       $(document).on('click','.groupAssign', function(e) {

        var allVals = [];  
        $(".sub_chk:checked").each(function() {  
            allVals.push($(this).attr('value'));
        });  

        if(allVals.length <=0)  
        {  
            alert("Please select atleast one enquiry..!");  
            return false;
        }  else {  


            var check = confirm("Are you sure you want to assign ?"); 
            
            $('input:hidden[name=enquiryIds]').val(allVals);
            return true;  
        }  
    });

       $(document).on('click','.assignLead', function(e) {        

        var check = confirm("Are you sure you want to assign ?"); 
        var vals = $(this).attr('data-id');
        $('input:hidden[name=enquiryIds]').val(vals);
        return true;  
        
    });
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
            /* } */    
            
        });    
       
       
   });
</script>
@endsection
