@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Closed Enquiry</div>
        </div>
        {{ Breadcrumbs::render('closedList') }}
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
                      @include('includes.pagination_info',['paginator' => $lists])         
                  </div>
			<div class="clr"></div></h4>
              	<div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table display product-overview mb-30" id="dtBasicExample">
                            <thead>
                                <tr>
                                   <th title="Tenant Name">@sortablelink('cust','Customer/Tenant',[],[ 'class' => 'sort_url'])</th>
								   <th title="GSM">@sortablelink('cust_no','Mobile No',[],[ 'class' => 'sort_url'])</th>
								   <th title="Closing Remarks">@sortablelink('sales_notes','Closing Remarks',[],[ 'class' => 'sort_url'])</th>
								   <th>Action</th>
                                </tr>
                                <tr> 
									<td><input autocomplete="off" type="text" name="cust" class="search_fields" id="cust" value="{{old('cust')}}" ></td>
									<td><input autocomplete="off" type="text" id="cust_no" name="cust_no" class="search_fields sales_mobile_no" value="{{old('sales_mobile_no')}}" ></td>
									<td><input autocomplete="off" type="text" id="sales_note" name="sales_note" class="search_fields sales_note"  value="{{old('sales_notes')}}" ></td>
									<td></td>
								</tr>
                            </thead>
                            <tbody id="enquiry-search">
							    @include('sales::TenantSales.tenant_closed_lists_ajax')
                            </tbody>
                        </table>
                        
                       
                        
                    </div>
                </div> 
                   <div id="pagination">
                             {{$lists->appends(\Request::except(['page','_token']))->links()}}
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
     $(document).on('click','.reopen', function(e) {        

        /*if (confirm("Are you sure you want to Re-open ?")) {*/
            var vals = $(this).attr('data-id');
            var workflow = $(this).attr('datas-id');
            $('input:hidden[name=enquiryIds]').val(vals);
            $.ajax({
                method: 'GET', // Type of response and matches what we said in the route
                url: '../leadAssign/re-open/'+vals+'/'+workflow+'', // This is the url we gave in the route
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
