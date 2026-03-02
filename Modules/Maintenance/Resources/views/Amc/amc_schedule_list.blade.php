@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

@section('search_url', route('amcSchedule.index')) 
@section('search_reset', route('amcSchedule.index')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">AMC Schedule</div>
        </div>
        {{ Breadcrumbs::render('amcSchedule.index') }}
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
                     @include('includes.pagination_info',['paginator' => $amcSchedules])         
                 </div>
                @can('amc_schedule_add')

                <a href="{{route('amcSchedule.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
                @endcan
                <div class="clr"></div>
            </h4>
             <div class="table-wrap">
     <div class="table-responsive">
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('amcContract.amc_contract_no','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th> @sortablelink('amc_contract_type','Contractor Type',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_contractor_engineer','Contractor/Engineer',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_schedule_period_from','Start Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_schedule_period_to','End Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('paymentMethod.payment_method_code','Frequency',[],[ 'class' => 'sort_url' ])</th>
                        <th title="Status">@sortablelink('amc_schedule_status','Status',[],[ 'class' => 'sort_url' ])</th> 
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td> <input autocomplete="off" type="text" name="amc_contract_id" class="search_fields mob" id="amc_contract_id" value="{{old('amc_contract_id')}}" ></td>
                        <td><input autocomplete="off" type="text" name="amc_contract_type" class="search_fields mob" id="amc_contract_type" value="{{old('amc_contract_type')}}" ></td>
                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_id"  value="{{old('vendor__vendor_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="date" name="amc_schedule_period_from" class="search_fields mob" id="amc_schedule_period_from" value="{{old('amc_schedule_period_from')}}" ></td>
                        <td><input autocomplete="off" type="date" name="amc_schedule_period_to" class="search_fields mob" id="amc_schedule_period_to" value="{{old('amc_schedule_period_to')}}" ></td>
                        <td><input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('paymentMethod__payment_method_code')}}" ></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('amcScheduleSearch')}}" ></td>
                        <td>
                            <select name="amc_schedule_status" class="searchFields search_fields mob" id="amc_schedule_status">
                             <option value="">Show All</option>
                                    <option {{ (old('amc_schedule_status') != '')? ((old('amc_schedule_status') == 0)? 'selected' : '' ) : ''}} value="0">{{'Open'}}</option>
                                     <option {{ (old('amc_schedule_status') == 1)? 'selected' : '' }} value="1">{{'Closed'}}</option>
                                    <option {{ (old('amc_schedule_status') == 2)? 'selected' : '' }} value="2">{{'Cancel'}}</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                 
                    @include('maintenance::Amc.amc_schedule_list_ajax')  
                    
                </tbody>
            </table>
            </div></div>
            <div  id="pagination">                       
               {{$amcSchedules->appends(\Request::except(['page','_token','ajax']))->links()}} 
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
@include('maintenance::Amc.schedule_search_js')
@include('maintenance::Amc.amc_schedule_search_js')
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
//cancel schedule
$(document).on('click','.cancel_type',function(){

    var amc_schedule_id = $(this).attr('data-id');
    if(confirm('Do You want to Cancel this Schedule?')){
       $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url we gave in the 
            url: "{{route('cancelSchedule')}}",
           //url: '../../complaintStage/'+item_id+'/edit',
          data: {'amc_schedule_id' : amc_schedule_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
              location.reload();
          },
      }); 
   }    
});
/**********************************************************************************/
</script>

@endsection
