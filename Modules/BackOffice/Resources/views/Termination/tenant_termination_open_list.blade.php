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

@section('search_url', route('tenantTermination.index')) 
@section('search_reset', route('tenantTermination.index'))  
@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Open For Termination</div>
        </div>
          {{ Breadcrumbs::render('tenantTermination.index') }}  
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
                     @include('includes.pagination_info',['paginator' => $tenantTerminations])         
                 </div>
        @can('tenant_early_termination')
        <a href="{{route('tenantTermination.create')}}" class="btn btn-circle btn-primary  align-right"  >Early Termination</a>
        @endcan
            <div class="clr"></div>
            </h4>
            <div class="table-responsive1">
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>    
                        <th>@sortablelink('tenant_contract_no','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('unit_code','Unit No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_name','Tenant',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_start_date','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_valid_to_date','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_rent','Rent Pm',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('tenant_contract_muncipality_agr_no','Municipal Reg No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('os','OS',[],[ 'class' => 'sort_url' ])</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                       <td> <input autocomplete="off" type="text" name="contract_no" class="search_fields mob" id="contract_no" value="{{old('contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="unit_id" class="search_fields mob" id="unit_id"  value="{{old('unit_code')}}" ></td>

                        <td><input autocomplete="off" type="text" name="tenant_id" class="search_fields mob" id="tenant_id"  value="{{old('tenant_name')}}" ></td>

                        <td><input autocomplete="off" type="date" name="tenant_contract_start_date" class="search_fields mob" id="tenant_contract_start_date" value="{{old('tenant_contract_start_date')}}" onkeydown="return false" ></td>

                        <td><input autocomplete="off" type="date" name="tenant_contract_valid_to_date" class="search_fields mob" id="tenant_contract_valid_to_date" value="{{old('tenant_contract_valid_to_date')}}" onkeydown="return false"></td>

                        <td><input autocomplete="off" type="text" name="tenant_contract_rent" class="search_fields mob" id="tenant_contract_rent"  value="{{old('tenant_contract_rent')}}" ></td>

                        <td><input autocomplete="off" type="text" name="tenant_contract_muncipality_agr_no" class="search_fields mob" id="tenant_contract_muncipality_agr_no"  value="{{old('tenant_contract_muncipality_agr_no')}}" >

                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('tenantTerminationRequestSearch')}}" ></td>
                        <td></td>
                        <td><!-- <input autocomplete="off" type="text" name="status" class="search_fields mob" id="status"  value="{{old('status')}}" > --></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                 
                  @include('backoffice::Termination.tenant_termination_open_list_ajax')       
                    
                </tbody>
            </table>
            </div>
             {{csrf_field()}}
            
            <div id="pagination">
             
            
            {{$tenantTerminations->appends(\Request::except(['page','ajax','_token']))->links()}} 
   </div> 
   
</div>
</div>
</div>
</div>
<div class="modal" id="myModal">

</div>
@endsection
@section('scripts')    
 
@include('backoffice::RenewalOrTermination.tenant_search_js') 
@include('backoffice::Termination.tenant_terminate_js') 
<script>
$(document).ready(function() {

  $(document).on('click','.delete_type',function(event) {
          var action = $(this).attr("href");
                event.preventDefault();
                if (confirm('Do you want to Handover?')) {
                   window.location.href = action;
                } else {
                    return false;
                }
  });
      
});
/**********************************************************************************/     
$("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/


  function cancelTermination(id){

    $.ajax
        ({
            type: "POST",
            url: "{{route('cancelTermination')}}",
            data: {"contract_id":id,"_token": "{{ csrf_token() }}"},
            cache: false,
            success: function(data)
            {
               console.log(data);
               location.reload();
            }
        });
  }

</script>

@endsection
