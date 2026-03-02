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

@section('search_url', route('maintenancePayment.index')) 
@section('search_reset', route('maintenancePayment.index')) 

@section('content')
<!-- start widget -->
<div class="page-bar">
    <div class="page-title-breadcrumb">
        <div class=" pull-left">
            <div class="page-title">Maintenance Payment</div>
        </div>
        {{ Breadcrumbs::render('maintenancePayment.index') }}
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
                     @include('includes.pagination_info',['paginator' => $maintenancePayments])         
                 </div>
                @can('maintenance_payment_add')

                <a href="{{route('maintenancePayment.create')}}" class="btn btn-circle btn-primary  align-right"  >Add</a>  
                @endcan
                <div class="clr"></div>
            </h4>
<div class="table-wrap">
 <div class="table-responsive">	
            <table class="table display product-overview mb-30" id="dtBasicExample">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>@sortablelink('maintenance_payment_no','Payment No',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('maintenance_payment_date','Start Dt',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('vendor.vendor_name','Contractor Name',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('vendor.vendor_code','Contractor Code',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('maintenance_payment_method','Payment Method',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('bankInfo.bank_name','Bank',[],[ 'class' => 'sort_url' ])</th>
                        <th>@sortablelink('maintenance_payment_amount','Amount',[],[ 'class' => 'sort_url' ])</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td> <input autocomplete="off" type="text" name="maintenance_payment_no" class="search_fields mob" id="maintenance_payment_no" value="{{old('maintenance_payment_no')}}" ></td>

                        <td><input autocomplete="off" type="date" name="maintenance_payment_date" class="search_fields mob" id="maintenance_payment_date" value="{{old('maintenance_payment_date')}}" ></td>

                        <td><input autocomplete="off" type="text" name="vendor_id" class="search_fields mob" id="vendor_id"  value="{{old('vendor__vendor_name')}}" ></td>

                        <td><input autocomplete="off" type="text" name="vendor_code" class="search_fields mob" id="vendor_code"  value="{{old('vendor__vendor_code')}}" ></td>

                        <td><select name="maintenance_payment_method" class="searchFields search_fields mob" id="maintenance_payment_method" style="width:100px;">
                            <option value="">Show All</option>
                            <option {{ (old('maintenance_payment_method') == 1)? 'selected' : '' }} value="1">{{'Cash'}}</option>
                            <option {{ (old('maintenance_payment_method') == 2)? 'selected' : '' }} value="2">{{'Cheque'}}</option>
                        </select>
                    </td>

                    <td><input autocomplete="off" type="text" name="bank_id" class="search_fields mob" id="bank_id"  value="{{old('bankInfo__bank_name')}}" ></td>

                    <td><input autocomplete="off" type="text" name="maintenance_payment_amount" class="search_fields mob" id="maintenance_payment_amount"  value="{{old('maintenance_payment_amount')}}" ></td>
                    <td></td>
                    <input autocomplete="off" type="hidden" name="url_route" id="url_route"  value="{{route('paymentSearch')}}" ></td>
                    <td></td>
                </tr>
            </thead>
            <tbody id="enquiry-search">

                @include('backoffice::Transaction.maintenance_payment_list_ajax')  

            </tbody>
        </table>
</div>
</div>
        <div class="row "  id="pagination">
                       
        {{$maintenancePayments->appends(\Request::except(['page','_token','ajax']))->links()}} 
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
@include('backoffice::Transaction.payments_search_js')
@include('backoffice::Transaction.maintenance_search_js')
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

    $(document).on('click','.closed', function(e) {        
        var id = $(this).attr('data-id');
        $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('cancelMaintenancePayment')}}", // This is the url we gave in the route
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
