@extends('layouts.plms-app')
@section('css')  

<link href="{{asset('public/css/custom.css')}}" rel="stylesheet">
<!-- data tables -->
<link rel="stylesheet" href="{{ asset('public/css/datatables.min.css')}}">
@endsection

  @section('search_url', route('amcTask.index')) 
 @section('search_reset', route('amcTask.index')) 

@section('content')
    <!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">AMC Task</div>
        </div>
         {{ Breadcrumbs::render('amcTask.index') }}
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
                     @include('includes.pagination_info',['paginator' => $amcTasks])         
                 </div>
           {{-- @can('amc_contract_add') --}}

            <!-- <a href="{{route('amcContract.create')}}" class="btn btn-circle btn-primary  align-right"  >Add New</a> -->  
           {{-- @endcan --}}
             <div class="clr"></div>
            </h4>
               <div class="table-wrap">
     <div class="table-responsive">
              <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('amcContract.amc_contract_no','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amcSchedule.amc_contract_type','Contractor Type',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amcSchedule.amc_contractor_engineer','Contractor/Engineer',[],[ 'class' => 'sort_url' ])</th>
                         <th>@sortablelink('building.building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amenities_type_id','Amenity',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_schedule_from_date','Start Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('amc_schedule_to_date','End Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('payment_method_id','Frequency',[],[ 'class' => 'sort_url' ])</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td> <input autocomplete="off" type="text" name="amc_schedule_id" class="search_fields mob" id="amc_schedule_id" value="{{old('amcContract__amc_contract_no')}}" ></td>
                        <td><input autocomplete="off" type="text" name="amc_schedule_id" class="search_fields mob" id="amc_schedule_id" value="{{old('amcContract__amc_contract_no')}}" ></td></td>
                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_id"  value="{{old('vendor__vendor_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('building__building_name')}}" ></td>
                        <td><input autocomplete="off" type="text" name="amenities_types_id" class="search_fields mob" id="amenities_types_id"  value="{{old('amenityType__amentity_types_name')}}" ></td>
                        <td><input autocomplete="off" type="date" name="amc_schedule_from_date" class="search_fields mob" id="amc_schedule_from_date" value="{{old('amc_schedule_from_date')}}" ></td>
                        <td><input autocomplete="off" type="date" name="amc_schedule_to_date" class="search_fields mob" id="amc_schedule_to_date" value="{{old('amc_schedule_to_date')}}" ></td>
                        <td><input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('paymentMethod__payment_method_code')}}" ></td>
                        <td><!-- <input autocomplete="off" type="text" name="payment_method_id" class="search_fields mob" id="payment_method_id"  value="{{old('payment_method_id')}}" > --></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('amcTaskSearch')}}" ></td>
                       <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                                                               
                    @include('maintenance::Amc.amc_task_list_ajax')  
                                                            
                </tbody>
                </table>
                  </div>
</div>      
                 <div id="pagination">                                  
                     {{$amcTasks->appends(\Request::except(['page','_token','ajax']))->links()}} 
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
{{-- @include('maintenance::enquiry_search_js') --}}
@include('maintenance::Amc.task_search_js')
@include('maintenance::Amc.amc_task_search_js')
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
$(document).on('click','.close_type',function(){

    var amc_task_id = $(this).attr('data-id');
	var amc_contract_type = $(this).attr('id'); //i-in-house, s-sub-contractor
      $.ajax({
        method: 'POST', // Type of response and matches what we said in the route
        //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url  we gave in the 
        url: "{{route('checkTask')}}",
        //url: '../../complaintStage/'+item_id+'/edit',
        data: {'amc_task_id' : amc_task_id,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
        success: function(response){ // What to do if we succeed
          //alert(response);
          $("#myModal").on("hidden.bs.modal", function(){
              $("#myModal").html("");
              $(this).removeData('bs.modal');
            });
          if(response == 0){
            $.ajax({
              method: 'POST', // Type of response and matches what we said in the route
              //url: '../../tenantContract/'+tenantContract_id+'/edit', // This is the url  we gave in the 
              url: "{{route('closeTask')}}",
              //url: '../../complaintStage/'+item_id+'/edit',
              data: {'amc_task_id' : amc_task_id,'amc_contract_type':amc_contract_type,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
              success: function(responses){ // What to do if we succeed
                $("#myModal").html(responses); 
              },
            });
          }else{
            
           alert("Close The Previous Task Before Closing This !");
           $("#myModal").modal('hide');
           return false;
         }
         
        },
          
      });
      
      return true;


});
    
</script>

@endsection
