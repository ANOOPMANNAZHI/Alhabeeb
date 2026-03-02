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

 @section('search_url', route('amcContract.index')) 
 @section('search_reset', route('amcContract.index')) 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">AMC Contract</div>
        </div>
         {{ Breadcrumbs::render('amcContract.index') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
<i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">
           
            @include('maintenance::Amc.contract_search')   
 
        </div>
    </div>
</div>
 <div class="row">
   <div class="col-md-12 col-sm-12">
        <div class="card  card-box">
            
            <div class="card-body ">
            <h4>
             <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $amcContracts])         
                 </div>
            @can('amc_contract_add')

            <a href="{{route('amcContract.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
            @endcan
             <div class="clr"></div>
            </h4>
              <div class="table-wrap">
     <div class="table-responsive">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('amc_contract_no','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('vendor.vendor_name','Contractor',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_contract_period_from','Start Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_contract_period_to','End Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('payment_method_id','Frequency',[],[ 'class' => 'sort_url' ])</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td> <input autocomplete="off" type="text" name="amc_contract_no" class="search_fields mob" id="amc_contract_no" value="{{old('amc_contract_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_id"  value="{{old('vendor__vendor_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="date" name="amc_contract_period_from" class="search_fields mob" id="amc_contract_period_from" value="{{old('amc_contract_period_from')}}" onkeydown="return false" ></td>
                        <td><input autocomplete="off" type="date" name="amc_contract_period_to" class="search_fields mob" id="amc_contract_period_to" value="{{old('amc_contract_period_to')}}" onkeydown="return false"></td>
                        <td><input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('paymentMethod__payment_method_code')}}" ></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('amcContractSearch')}}" ></td>
                       <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('maintenance::Amc.amc_contract_list_ajax')  
                                                            
                </tbody>
                </table>
                 </div>
    </div>       
                 <div class="row "  id="pagination">                                    
                     {{$amcContracts->appends(\Request::except(['page','_token','ajax']))->links()}} 
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
{{-- @include('maintenance::enquiry_search_js') --}}
@include('maintenance::Amc.contract_search_js')
@include('maintenance::Amc.amc_search_js')
<script>   
/**********************************************************************************/
//cancel contract
function cancelContract(){
return confirm('Contract for this AMC is already scheduled.Cancelling the contract will also cancel the schedule?Do you want to Cancel the Contract?');
}
/**********************************************************************************/
    $("#show").on("hide.bs.collapse", function(){
            $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });

/*********************************************************************************************/

        $(document).on('click','.cancel_type',function(){

        var amc_contract_id = $(this).attr('data-id');
        var amc_schedule_id = $(this).attr('datas-id');
        if(amc_schedule_id!='')
        {
            if(confirm('Contract for this AMC is already scheduled. Cancelling the contract will also cancel the Schedule? Do You want to Cancel the Contract?')){
             $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
           url: "{{route('cancelContract')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'amc_contract_id' : amc_contract_id,'amc_schedule_id' : amc_schedule_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                  location.reload();
            },
        });
         }
        }else{
            if(confirm('Do You want to Cancel this Contract?')){
                  $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
           url: "{{route('cancelContract')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'amc_contract_id' : amc_contract_id,'amc_schedule_id' : amc_schedule_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                  location.reload();
            },
        });
            }
        }
       
       
         
    });
/*********************************************************************************************/
</script>

@endsection
