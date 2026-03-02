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

 @section('search_url', route('renewalContract')) 
 @section('search_reset', route('renewalContract')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Contract Renewal</div>
        </div>
         {{ Breadcrumbs::render('landlordRenewalContract') }} 
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
                     @include('includes.pagination_info',['paginator' => $contractRenewals])         
                 </div>
                   <div class="clr"></div>
            </h4>
	  <div class="table-wrap">
     <div class="table-responsive">  
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('old_contract_id','Old Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','Landlord',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract_id','Status',[],[ 'class' => 'sort_url' ])</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                       <td> <input autocomplete="off" type="text" name="landlord_contract_old_no" class="search_fields mob" id="landlord_contract_old_no" value="{{old('old_landlord_contract_no')}}" ></td>

                       <td> <input autocomplete="off" type="text" name="landlord_contract_no" class="search_fields mob" id="landlord_contract_no" value="{{old('landlord_contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_name"  value="{{old('building_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_name"  value="{{old('vendor_name')}}" ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_from_date" class="search_fields mob" id="landlord_contract_valid_from_date" value="{{old('landlord_contract_valid_from_date')}}" ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_to_date" class="search_fields mob" id="landlord_contract_valid_to_date" value="{{old('landlord_contract_valid_to_date')}}"></td>

                        

                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('landlordContractSearch')}}" ></td>
                        <td></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                 
                  @include('backoffice::RenewalOrTermination.landlord_renewal_contract_list_ajax')  
                    
                </tbody>
            </table>
            </div>
			</div>
            <div class="row "  id="pagination">                           
       {{$contractRenewals->appends(\Request::except(['page','ajax','_token','route']))->links()}} 
   </div> 
   
</div>
</div>
</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
@endsection
@section('scripts') 
@include('sales::enquiry_search_js') 
@include('backoffice::RenewalOrTermination.landlord_renewal_js') {{--quick search --}}
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
