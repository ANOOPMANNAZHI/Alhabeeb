@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
<style>
    input[type=date]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}
</style>
@endsection

@section('search_url', route('TerminatedContractLandlord')) 
@section('search_reset', route('TerminatedContractLandlord')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Terminated Landlord Contract</div>
        </div>
        {{ Breadcrumbs::render('TerminatedContractLandlord') }}  
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
        
        <div class="card-body ">
        <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $landlordTerminations])         
                 </div>
                  <div class="clr"></div>
        </h4>
            <div class="table-responsive1">
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('landlordContract.landlord_contract_no','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('vendor_name','Landlord',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_contract_valid_from_date','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_contract_valid_to_date','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('end_date','Terminated Date',[],[ 'class' => 'sort_url' ])</th>

                        <th>Action</th>
                    </tr>
                    <tr>
                       <td> <input autocomplete="off" type="text" name="landlord_contract_no" class="search_fields mob" id="landlord_contract_no" value="{{old('landlord_contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="buildingInfo__building_name"  value="{{old('building_name')}}" ></td>
                        

                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendorInfo__vendor_name"  value="{{old('vendor_name')}}" ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_from_date" class="search_fields mob" id="landlord_contract_valid_from_date" value="{{old('landlord_contract_valid_from_date')}}"  ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_to_date" class="search_fields mob" id="landlord_contract_valid_to_date" value="{{old('landlord_contract_valid_to_date')}}">

                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('TerminatedContractLandlord')}}" ></td>

                        <td><input autocomplete="off" type="date" name="end_date" class="search_fields mob" id="end_date" value="{{old('end_date')}}"  ></td>
                        
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                 
                  @include('backoffice::Termination.landlord_termination_terminated_contracts_list_ajax')  
                    
                </tbody>
            </table>
            </div>
            <div id="pagination">
                         
         {{$landlordTerminations->appends(\Request::except(['page','ajax','_token','route']))->links()}}
   </div> 
   
</div>
</div>
</div>
</div>

@endsection
@section('scripts') 
@include('sales::enquiry_search_js')
 @include('backoffice::Termination.landlord_termination_js')  

 {{--quick search --}}
<script>   
 $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/
</script>

@endsection
