@extends('layouts.plms-app')

@section('css')  
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection


@section('search_reset', route('wonList'))

@section('content')
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Won Enquiry</div>
        </div>
        {{ Breadcrumbs::render('wonList') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse  @if(old('fieldName'))  show @endif" id="show"  >
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
                                  <th title="Tenant Name">@sortablelink('tenant_name','Tenant',[],[ 'class' => 'sort_url'])</th>
                                  <th title="Building">@sortablelink('building_name','Building',[],[ 'class' => 'sort_url'])</th>
                                   
                                  <th title="Unit No">@sortablelink('unit_code','Unit No',[],[ 'class' => 'sort_url'])</th>
                                  <th title="Unit Type">@sortablelink('unit_type','Unit Type',[],[ 'class' => 'sort_url'])</th>
                                   
                                  <th title="Duration">@sortablelink('tenant_contract_duration','Duration(Months)',[],[ 'class' => 'sort_url'])</th>
                                  <th title="Unit Usage">@sortablelink('unit_usage','Unit Usage',[],[ 'class' => 'sort_url'])</th>
                                 
                                  <th title="Action">Action</th>
                                </tr>
                                <tr> 
								  <td><input autocomplete="off" type="text" name="tenant_name" class="search_fields mob" id="tenant_name" value="{{old('tenant_name')}}" ></td>
								  <td><input autocomplete="off" type="text" name="building_name_select" id="building_name_select" class="search_fields mob building_name_select" value="{{old('building_name_select')}}" ></td>
								  <td><input autocomplete="off" type="text" name="unit_no_select" id="unit_no_select" class="search_fields unit_no_select mob"  value="{{old('unit_no_select')}}" ></td>
								  <td><input autocomplete="off" type="text" name="unitTypes" id="unitTypes" class="search_fields unit_select mob"  value="{{old('unitTypes')}}" ></td>
								  <td><input autocomplete="off" type="text" name="duration" id="duration" class="search_fields unit_select mob"  value="{{old('unit_select')}}" ></td>
								  <td><input autocomplete="off" type="text" name="unit_usage" id="unit_usage" class="search_fields unit_select mob"  value="{{old('unit_select')}}" ></td>
                                  <td></td>
                                  
								</tr>
                            </thead>
                             <tbody id="enquiry-search">
							   								   
							    @include('sales::TenantSales.tenant_won_list_ajax')	
							    							
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
});
</script>
@endsection
