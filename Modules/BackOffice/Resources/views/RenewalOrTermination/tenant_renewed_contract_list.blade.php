@extends('layouts.plms-app')
@section('css')  
<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('tenantRenewedContract'))
@section('search_reset', route('tenantRenewedContract'))

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Renewed Contract</div>
        </div>
         {{ Breadcrumbs::render('renewedContract') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
           @include('backoffice::RenewalOrTermination.tenant_search')  
 
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $tenantRenewals])         
                 </div>
             <div class="clr"></div>
            </h4>
            <div class="table-wrap">
     <div class="table-responsive">  
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('old_contract','Old Contract',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract','New Contract',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('unit_code','Unit No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_muncipality_agr_no','Muncipal Reg No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_start_date','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_valid_to_date','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_rent','Rent',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_name','Tenant',[],[ 'class' => 'sort_url' ])</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td><input type="text" name="tenant_contract_old_no" id="tenant_contract_old_no" class="search_fields Contract mob"  value="{{old('tenant_contract_old_no')}}" >
                        </td>
                        <td><input type="text" name="contract_no" id="contract_no" class="search_fields Contract mob"  value="{{old('contract_no')}}" >
                        </td>
                        <td><input type="text" name="building_id" id="building_id" class="search_fields Building mob"  value="{{old('building_name')}}" >
                        </td> 
                        <td><input type="text" name="unit_id"  id="unit_id" class="search_fields Unit mob"  value="{{old('unit_code')}}" >
                        </td>
                        <td><input type="text" name="tenant_contract_muncipality_agr_no"  id="tenant_contract_muncipality_agr_no" class="search_fields Unit mob"  value="{{old('tenant_contract_muncipality_agr_no')}}" >
                        </td> 
                        <td><input type="date" name="tenant_contract_start_date" id="tenant_contract_start_date" class="search_fields StartDate mob"  value="{{old('tenant_contract_start_date')}}" >
                        </td> 
                        <td><input type="date" name="tenant_contract_valid_to_date" id="tenant_contract_valid_to_date" class="search_fields EndDate mob"  value="{{old('tenant_contract_valid_to_date')}}" >
                        </td>
                        <td><input type="text" name="tenant_contract_rent" id="tenant_contract_rent" class="search_fields Tenant mob"  value="{{old('tenant_contract_rent')}}" >
                        </td>
                        <td><input type="text" name="tenant_id" id="tenant_id" class="search_fields Tenant mob"  value="{{old('tenant_name')}}" >
                        </td>
                        <td><input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('tenantRenewal.tenantRenewalRequestSearch')}}" >
                        </td>
                    </tr>
                </thead>
               
                    <tbody id="enquiry-search">                                                             
                                @include('backoffice::RenewalOrTermination.tenant_renewed_contract_list_ajax')                                 
                    </tbody>
                   
              </table>
               </div>
        </div>
			 <div id="pagination">
                        {{$tenantRenewals->appends(\Request::except('page'))->links()}}               
                    </div>  
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')    
 
@include('backoffice::RenewalOrTermination.tenant_search_js') 
@include('backoffice::RenewalOrTermination.tenant_quick_search_view_js') 

@endsection
