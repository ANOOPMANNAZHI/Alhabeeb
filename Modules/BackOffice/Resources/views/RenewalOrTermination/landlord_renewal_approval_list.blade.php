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

 @section('search_url', route('landlordContractApproval'))  
 @section('search_reset', route('landlordContractApproval'))  

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Contract Approval</div>
        </div>
          {{ Breadcrumbs::render('landlordContractApproval') }} 
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
                     @include('includes.pagination_info',['paginator' => $contractApprovals])         
                 </div>
                 <div class="clr"></div>
        </h4>
               <div class="table-wrap">
     <div class="table-responsive">   
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>@sortablelink('old_contract','Old Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('new_contract','Contract No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('building_name','Building',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('vendor_name','Landlord',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_contract_valid_from_date','Start Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_contract_valid_to_date','End Date',[],[ 'class' => 'sort_url' ])</th>
                        <th>Contract Amount</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                       <td> <input autocomplete="off" type="text" name="landlord_contract_old_no" class="search_fields mob" id="landlord_contract_old_no" value="{{old('old_landlord_contract_no')}}" ></td> 

                       <td> <input autocomplete="off" type="text" name="landlord_contract_no" class="search_fields mob" id="landlord_contract_no" value="{{old('landlord_contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_name"  value="{{old('building_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_name"  value="{{old('vendor_name')}}" ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_from_date" class="search_fields mob" id="landlord_contract_valid_from_date" value="{{old('landlord_contract_valid_from_date')}}"  ></td>

                        <td><input autocomplete="off" type="date" name="landlord_contract_valid_to_date" class="search_fields mob" id="landlord_contract_valid_to_date" value="{{old('landlord_contract_valid_to_date')}}" ></td>

                        <td><input autocomplete="off" type="text" name="landlord_contract_amt" class="search_fields mob" id="landlord_contract_amt"  value="{{old('landlord_contract_amt')}}" ></td>
                        <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('landlordApprovalSearch')}}" ></td>
                        <td></td>
                    </tr>
                </thead>
                <tbody id="enquiry-search">
                 
                  @include('backoffice::RenewalOrTermination.landlord_renewal_approval_list_ajax')  
                    
                </tbody>
            </table>
              </div>
</div>
            <div class="row "  id="pagination">                           
             {{$contractApprovals->appends(\Request::except(['page','ajax','_token','route']))->links()}} 
            </div> 
   
</div>
</div>
</div>
</div>
<form id="delete-form" action="" method="POST">
    {{ method_field('DELETE') }}  {{csrf_field()}}
    <input value="delete" style="display: none;" type="submit">
</form>
<div class="modal" id="myModal">

    </div>
@endsection
@section('scripts') 
@include('sales::enquiry_search_js')   {{--advance search --}}
@include('backoffice::RenewalOrTermination.landlord_renewal_js') {{--quick search --}}
<script>   
 $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });
/**********************************************************************************/

$(document).ready(function() {

    $(document).on('click', '.accept',function(e) {        

        var action_key =  $(this).attr('datas-id');
        var landlord_contract_id = $(this).attr('data-nid');
        var landlord_old_contract_id = $(this).attr('data-oid');
        var status = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
       /* if (confirm('Do you want to Approval Accept?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalApproveReject')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'landlord_old_contract_id' : landlord_old_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
            },
        });
        return true;
        /*}else {
            return false;
        } */       
         
    });
    $(document).on('click','.terminate', function(e) {        

        var action_key =  $(this).attr('datas-id');
        var landlord_contract_id = $(this).attr('data-nid');
        var landlord_old_contract_id = $(this).attr('data-oid');
        var status = $(this).attr('data-id');
        var process_flow = $(this).attr('data-flow-id');
         /*if (confirm('Do you want to Reject?')) {*/
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('landlordRenewalApproveReject')}}", // This is the url we gave in the route
            data: {'process_flow':process_flow,'action_key' : action_key,'landlord_contract_id' : landlord_contract_id,'landlord_old_contract_id' : landlord_old_contract_id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
               //window.location.href = response;
            },
        });
        return true;
        /*}else {
            return false;
        }  */      
         
    });
});

/**********************************************************************************/
</script>

@endsection
