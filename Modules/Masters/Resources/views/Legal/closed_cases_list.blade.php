@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

 @section('search_url', route('closedLegalCases')) 
 @section('search_reset', route('closedLegalCases')) 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Closed Legal Cases</div>
        </div>
       {{ Breadcrumbs::render('closedLegalCases') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
            @include('masters::Legal.legal_search')   
 
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">  
			<h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $closedLegalCases])         
                 </div>
             <div class="clr"></div>
            </h4>            
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('tenantContract.tenant_contract_no','Agreement No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('tenant.tenant_name','Name',[],[ 'class' => 'sort_url'])</th>
                         <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('unit.unit_code','Unit No',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('tenantContract.tenant_contract_valid_to_date','To',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('tenantContract.tenant_contract_status','Status',[],[ 'class' => 'sort_url'])</th>
                        <th>@sortablelink('tenantContract.tenant_contract_rent','Rent',[],[ 'class' => 'sort_url'])</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td> <input autocomplete="off" type="text" name="tenant_contract_no" class="search_fields mob" id="tenant_contract_no" value="{{old('tenantContract__tenant_contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="tenant_id" class="search_fields mob" id="tenant_id"  value="{{old('tenantContract__tenant__tenant_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit__unit_code')}}" ></td>

                        <td><input autocomplete="off" type="date" name="tenant_contract_valid_to_date" class="search_fields mob" id="tenant_contract_valid_to_date" value="{{old('tenantContract__tenant_contract_valid_to_date')}}" ></td>

                        <td>
                            <select name="tenant_contract_status" class="searchFields search_fields mob" id="tenant_contract_status">
                             <option value="">Show All</option>
                                    <option {{ (old('tenantContract__tenant_contract_status') != '')? ((old('tenantContract__tenant_contract_status') == 0)? 'selected' : '' ) : ''}} value="0">{{'Inactive'}}</option>
                                    <option {{ (old('tenantContract__tenant_contract_status') == 1)? 'selected' : '' }} value="1">{{'Active'}}</option>
                            </select>
                        </td>

                        <td><input autocomplete="off" type="text" name="tenant_contract_rent" class="search_fields mob" id="tenant_contract_rent"  value="{{old('tenantContract__tenant_contract_rent')}}" ></td> 

                        <td><!-- <input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('payment_method_id')}}" > --></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('activeCasesSearch')}}" ></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('masters::Legal.closed_cases_list_ajax')  
                                                            
                </tbody>
                </table>
                        
                 <div class="row "  id="pagination">                                  
                     {{$closedLegalCases->appends(\Request::except(['page','_token']))->links()}} 
                </div> 
                    
            </div>
        </div>
    </div>
</div>
<div class="modal" id="myModal">

    </div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 
@include('masters::Legal.legal_search_js')
@include('masters::Legal.legal_case_search_js')
<script>   
jQuery('.delete_type').click(function (event) {
    var action = $(this).attr("href");
    event.preventDefault();
    if (confirm('Do you want to Delete this Complaint?')) {
        jQuery("#delete-form").attr('action', action);
        jQuery("#delete-form").submit();
    } else {
        return false;
    }
});
/**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/
</script>

@endsection