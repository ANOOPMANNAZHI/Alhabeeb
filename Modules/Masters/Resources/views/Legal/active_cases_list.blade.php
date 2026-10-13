@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

 @section('search_url', route('activeCases')) 
 @section('search_reset', route('activeCases')) 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Accepted Legal Cases</div>
        </div>
       {{ Breadcrumbs::render('legalActiveCases') }}
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
             @php $LC = \Modules\Masters\Services\LegalCase::class; @endphp
             <style>.lc-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:12px;font-weight:600}.lc-p1{background:#fde8e8;color:#b42318}.lc-p2{background:#fff4e0;color:#a15c07}.lc-p3{background:#e8f3ff;color:#1d5fa8}</style>
             <form method="GET" action="{{ route('activeCases') }}" class="row" style="margin-bottom:10px;">
               <div class="col-sm-2"><select name="lc_priority" class="form-control" aria-label="Priority"><option value="">All priorities</option>
                 @foreach($LC::PRIORITIES as $k => $v)<option value="{{ $k }}" @if((string) request('lc_priority') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
               <div class="col-sm-2"><select name="lc_type" class="form-control" aria-label="Case type"><option value="">All types</option>
                 @foreach($LC::TYPES as $k => $v)<option value="{{ $k }}" @if((string) request('lc_type') === (string) $k) selected @endif>{{ $v }}</option>@endforeach</select></div>
               <div class="col-sm-2"><select name="lc_stage" class="form-control" aria-label="Stage"><option value="">All stages</option>
                 <option value="none" @if(request('lc_stage') === 'none') selected @endif>Stage not set</option>
                 @foreach($lcStages as $s)<option value="{{ $s->id }}" @if((string) request('lc_stage') === (string) $s->id) selected @endif>{{ $s->name }}</option>@endforeach</select></div>
               <div class="col-sm-2"><select name="lc_lawyer" class="form-control" aria-label="Lawyer"><option value="">All lawyers</option>
                 @foreach($lcLawyers as $id => $nm)<option value="{{ $id }}" @if((string) request('lc_lawyer') === (string) $id) selected @endif>{{ $nm }}</option>@endforeach</select></div>
               <div class="col-sm-2"><label style="margin-top:8px;font-weight:400;"><input type="checkbox" name="lc_hearing" value="7" @if(request('lc_hearing')) checked @endif> Hearing in next 7 days</label></div>
               <div class="col-sm-2"><button class="btn btn-primary" type="submit">Filter</button> <a class="btn btn-default" href="{{ route('activeCases') }}">Reset</a></div>
             </form>
             <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $activeCases])         
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
                        <th>@sortablelink('priority','Priority',[],[ 'class' => 'sort_url'])</th>
                        <th>Stage</th>
                        <th>@sortablelink('next_hearing_date','Next hearing',[],[ 'class' => 'sort_url'])</th>
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
                        <td></td><td></td><td></td>

                        <td><!-- <input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('payment_method_id')}}" > --></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('activeCasesSearch')}}" ></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('masters::Legal.active_cases_list_ajax')  
                                                            
                </tbody>
                </table>
                        
                 <div class="row "  id="pagination">                                  
                     {{$activeCases->appends(\Request::except(['page','_token']))->links()}} 
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