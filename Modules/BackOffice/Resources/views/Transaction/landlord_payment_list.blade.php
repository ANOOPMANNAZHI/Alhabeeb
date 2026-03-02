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

 @section('search_url', route('landlordPayment.index')) 
@section('search_reset', route('landlordPayment.index')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Landlord Payment</div>
        </div>
        {{ Breadcrumbs::render('landlordPayment.index') }}
    </div>
</div>
<a  class=" align-right advSearch" href="#" id="enquiry_div" data-toggle="collapse" data-target="#show">
    <i class="fa fa-search" aria-hidden="true"></i>  <span id="enquirySearch"> Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i></span>
</a>
<div class="clearfix"></div>
<div class="row collapse @if(old('fieldName')) show @endif" id="show"  >
    <div class="col-md-12 col-sm-12 dashboardtab">
        <div class="panel tab-border card-box">

            @include('backoffice::Transaction.transaction_search')   

        </div>
    </div>
</div>
<div class="row">
   <div class="col-md-12 col-sm-12">
    <div class="card  card-box">

        <div class="card-body ">
            <h4>
            <div id="pagination_info">
                     @include('includes.pagination_info',['paginator' => $landlordPayments])         
                 </div>
                @can('landlord_payment_add')

                <a href="{{route('landlordPayment.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
                @endcan
                <div class="clr"></div>
            </h4>
<div class="table-wrap">
 <div class="table-responsive">	
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>@sortablelink('landlord_payment_no','Payment No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_payment_date','Payment Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>Landlord Name</th>
                        <th>Landlord Code</th>
                        <th>@sortablelink('landlordContract.landlord_contract_no','Agreement No',[],[ 'class' => 'sort_url' ])</th>
                        <th>Building</th>
                        <th>@sortablelink('landlordInvoice.landlord_invoice_voucher_no','Invoice No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('landlord_payment_amount','Amount',[],[ 'class' => 'sort_url' ])</th>
                        <th>Status</th>                       
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td> <input autocomplete="off" type="text" name="landlord_payment_no" class="search_fields mob" id="landlord_payment_no" value="{{old('landlord_payment_no')}}" ></td>

                        <td><input autocomplete="off" type="date" name="landlord_payment_date" class="search_fields mob" id="landlord_payment_date" value="{{old('landlord_payment_date')}}"></td>

                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_id"  value="{{old('landlordContract__vendorInfo__vendor_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="vendor_code" class="search_fields mob" id="vendor_code"  value="{{old('landlordContract__vendorInfo__vendor_code')}}" ></td>

                        <td><input autocomplete="off" type="text" name="landlord_contract_id" class="search_fields mob" id="landlord_contract_id"  value="{{old('landlordContract__landlord_contract_no')}}" ></td>

                        <td><input autocomplete="off" type="text" name="building_id" class="search_fields mob" id="building_id"  value="{{old('landlordContract__buildingInfo__building_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="landlord_invoice_id" class="search_fields mob" id="landlord_invoice_id"  value="{{old('landlordInvoice__landlord_invoice_voucher_no')}}" ></td>

                    <td><input autocomplete="off" type="text" name="landlord_payment_amount" class="search_fields mob" id="landlord_payment_amount"  value="{{old('landlord_payment_amount')}}" ></td>
                    <td></td>
                  
                    <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('landlordPaymentSearch')}}" ></td>
                    <td></td>
                </tr>
            </thead>
            <tbody id="enquiry-search">

                @include('backoffice::Transaction.landlord_payment_list_ajax')  

            </tbody>
        </table>
</div>
</div>
        <div class="row "  id="pagination">
                     
  {{$landlordPayments->appends(\Request::except(['page','_token','ajax']))->links()}} 
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
 @include('backoffice::Transaction.landlord_payment_search_js')
@include('backoffice::Transaction.landlord_search_js') 
<script>   
    /**********************************************************************************/

    $("#show").on("hide.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-down" aria-hidden="true"></i>');
    });
    $("#show").on("show.bs.collapse", function(){
        $("#enquirySearch").html('Advance Search <i class="fa fa-caret-up" aria-hidden="true"></i>');
    });

    /*********************************************************************************************/
    jQuery(document).ready(function() {
       
      jQuery('.dataTables_length').addClass('bs-select');
      
      jQuery('.delete_type').click(function (event) {
        var action = $(this).attr("href");
        event.preventDefault();
        if (confirm('Do you want to Delete this Maintenance Payment?')) {
            jQuery("#delete-form").attr('action', action);
            jQuery("#delete-form").submit();
        } else {
            return false;
        }
    });
  });
    /*********************************************************************************************/
    $(document).ready(function() {

    $('.closed').on('click', function(e) {        
        var id = $(this).attr('data-id');
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('cancelLandlordPayment')}}", // This is the url we gave in the route
            data: {'id':id,'status' : status,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
            success: function(response){ // What to do if we succeed
                $("#myModal").html(response);
                //alert(response);
               //window.location.href = response;
            },
        });
        return true;
    });
	 $(document).on('click','.post_type',function(){  

        if (confirm('Do you want to Post this Payment ?')) {
             return true;
        } else {
            return false;
        }
    });

     });
    /*********************************************************************************************/
</script>

@endsection
